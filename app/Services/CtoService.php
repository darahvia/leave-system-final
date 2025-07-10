<?php

namespace App\Services;

use App\Customer;
use App\CtoApplication;
use App\CtoCreditUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CtoService
{
    /**
     * Processes a CTO activity (credits earned).
     * @param \App\Customer $customer
     * @param array $activityData
     * @param \App\CtoApplication|null $existingRecord
     * @return \App\CtoApplication
     * @throws \Exception
     */
    public function processCtoActivity(Customer $customer, array $activityData, $existingRecord = null)
    {
        DB::beginTransaction();
        try {
            if ($existingRecord) {
                $ctoApplication = $existingRecord;
                $ctoApplication->update([
                    'special_order' => $activityData['special_order'],
                    'date_of_activity_start' => $activityData['date_of_activity_start'],
                    'date_of_activity_end' => $activityData['date_of_activity_end'],
                    'activity' => $activityData['activity'],
                    'credits_earned' => (float)$activityData['credits_earned'], // Ensure float
                    'is_activity' => true,
                    'no_of_days' => null,
                    'date_of_absence_start' => null,
                    'date_of_absence_end' => null,
                    'current_balance' => (float)$activityData['credits_earned'], // Ensure float
                    'hours_applied' => 0,
                ]);
                Log::info("Updated CTO Activity ID: {$ctoApplication->id}");
            } else {
                $ctoApplication = $customer->ctoApplications()->create([
                    'special_order' => $activityData['special_order'],
                    'date_of_activity_start' => $activityData['date_of_activity_start'],
                    'date_of_activity_end' => $activityData['date_of_activity_end'],
                    'activity' => $activityData['activity'],
                    'credits_earned' => (float)$activityData['credits_earned'], // Ensure float
                    'is_activity' => true,
                    'no_of_days' => null,
                    'date_of_absence_start' => null,
                    'date_of_absence_end' => null,
                    'current_balance' => (float)$activityData['credits_earned'], // Ensure float
                    'hours_applied' => 0,
                ]);
                Log::info("Created new CTO Activity ID: {$ctoApplication->id}");
            }

            $this->recalculateBalancesForCustomer($customer);

            DB::commit();
            return $ctoApplication;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing CTO activity: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw new \Exception("Failed to process CTO activity: " . $e->getMessage());
        }
    }

    /**
     * Processes a CTO usage (credits deducted for absence).
     * @param \App\Customer $customer
     * @param array $usageData
     * @param \App\CtoApplication|null $existingRecord
     * @return \App\CtoApplication
     * @throws \Exception If insufficient credits are available or other errors occur.
     */
    public function processCtoUsage(Customer $customer, array $usageData, $existingRecord = null)
    {
        DB::beginTransaction();
        try {
            $startDate = Carbon::parse($usageData['date_of_absence_start']);
            $endDate = Carbon::parse($usageData['date_of_absence_end']);
            $hoursToDeduct = (float)$usageData['no_of_days'];

            if ($existingRecord) {
                CtoCreditUsage::where('cto_absence_id', $existingRecord->id)->delete();
                Log::info("Undoing existing CTO usage for absence ID: {$existingRecord->id} before update.");
                
                $ctoApplication = $existingRecord;
                $ctoApplication->update([
                    'date_of_absence_start' => $startDate,
                    'date_of_absence_end' => $endDate,
                    'no_of_days' => $hoursToDeduct,
                    'is_activity' => false,
                    'credits_earned' => 0,
                    'special_order' => null,
                    'activity' => null,
                    'current_balance' => 0,
                ]);
                Log::info("Updated CTO Usage ID: {$ctoApplication->id}");
            } else {
                $ctoApplication = $customer->ctoApplications()->create([
                    'date_of_absence_start' => $startDate,
                    'date_of_absence_end' => $endDate,
                    'no_of_days' => $hoursToDeduct,
                    'is_activity' => false,
                    'credits_earned' => 0,
                    'special_order' => null,
                    'activity' => null,
                    'current_balance' => 0,
                ]);
                Log::info("Created new CTO Usage ID: {$ctoApplication->id}");
            }

            $this->deductCtoCredits($customer, $ctoApplication, $hoursToDeduct);

            $this->recalculateBalancesForCustomer($customer);

            DB::commit();
            return $ctoApplication;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing CTO usage: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw new \Exception("Failed to process CTO usage: " . $e->getMessage());
        }
    }

    /**
     * Deducts CTO credits from available activities using FIFO and expiration logic.
     * @param \App\Customer $customer
     * @param \App\CtoApplication $ctoAbsence The CTO application record for the absence.
     * @param float $hoursToDeduct The total number of hours to deduct.
     * @throws \Exception If insufficient credits are available.
     */
    protected function deductCtoCredits(Customer $customer, CtoApplication $ctoAbsence, $hoursToDeduct)
    {
        $remainingDeduction = $hoursToDeduct;

        $availableActivities = $customer->ctoApplications()
            ->where('is_activity', true)
            ->get()
            ->filter(function($activity) use ($ctoAbsence) {
                return $activity->remaining_credits > 0 && !$activity->isExpiredAt($ctoAbsence->date_of_absence_start);
            })
            ->sortBy(function($activity) {
                $sortDate = $activity->date_of_activity_end ?? $activity->created_at;
                $expirationDate = ($activity->date_of_activity_end instanceof \Carbon\Carbon) ? $activity->date_of_activity_end->copy()->addYear() : (\Carbon\Carbon::parse($activity->date_of_activity_end)->addYear() ?? Carbon::maxValue());

                return $expirationDate->timestamp . '-' . ($sortDate instanceof \Carbon\Carbon ? $sortDate->timestamp : \Carbon\Carbon::parse($sortDate)->timestamp) . '-' . $activity->id;
            })
            ->values();

        Log::info("Absence ID {$ctoAbsence->id} needs {$hoursToDeduct} hours. Available activities before deduction process:", $availableActivities->pluck('id', 'remaining_credits')->toArray());

        // FIX: Iterate over keys, modify the object's property, which persists for Eloquent models
        foreach ($availableActivities as $key => $activity) { // No & needed if modifying model properties
            if ($remainingDeduction <= 0) {
                break;
            }

            $activityRemainingCredits = $activity->remaining_credits; 

            if ($activityRemainingCredits <= 0) {
                Log::debug("Activity ID {$activity->id} (earned {$activity->credits_earned}) has no remaining credits ({$activityRemainingCredits}) left before deduction. Skipping.");
                continue; 
            }

            $deductFromThisActivity = min($remainingDeduction, $activityRemainingCredits);

            if ($deductFromThisActivity > 0) {
                CtoCreditUsage::create([
                    'cto_activity_id' => $activity->id,
                    'cto_absence_id' => $ctoAbsence->id,
                    'days_used' => $deductFromThisActivity,
                ]);
                
                // Directly modify the Eloquent model object's property. This should persist.
                $activity->remaining_credits -= $deductFromThisActivity; 
                $remainingDeduction -= $deductFromThisActivity;
                Log::info("Deducted {$deductFromThisActivity} hours from CTO Activity ID: {$activity->id} for Absence ID: {$ctoAbsence->id}. Remaining deduction for absence: {$remainingDeduction}. Activity remaining: {$activity->remaining_credits}");
            }
        }
        // No unset needed when not using & reference

        if ($remainingDeduction > 0) {
            Log::error("Insufficient CTO credits after deduction process for customer ID: {$customer->id}. Remaining deduction: {$remainingDeduction} for absence ID {$ctoAbsence->id}. This should have been caught by getEligibleCtoBalance.");
            throw new \Exception("Insufficient CTO credits available to cover the full absence after FIFO deduction and expiration.");
        }
    }

    /**
     * Recalculates all CTO balances for a given customer.
     * @param \App\Customer $customer
     * @return void
     */
    public function recalculateBalancesForCustomer(Customer $customer)
    {
        DB::beginTransaction();
        try {
            $ctoRecords = $customer->ctoApplications()
                ->with(['creditUsages', 'consumedActivities'])
                ->get()
                ->sortBy(function($cto) {
                    $sortDate = null;
                    if ($cto->is_activity && ($cto->date_of_activity_start instanceof \Carbon\Carbon || $cto->date_of_activity_start)) {
                        $sortDate = \Carbon\Carbon::parse($cto->date_of_activity_start);
                    } elseif (!$cto->is_activity && ($cto->date_of_absence_start instanceof \Carbon\Carbon || $cto->date_of_absence_start)) {
                        $sortDate = \Carbon\Carbon::parse($cto->date_of_absence_start);
                    }
                    $sortDate = $sortDate ?? $cto->created_at;

                    return $sortDate->timestamp . ($cto->is_activity ? '0' : '1') . $cto->id;
                })
                ->values();

            $ctoApplicationIds = $ctoRecords->pluck('id');
            if ($ctoApplicationIds->isNotEmpty()) {
                CtoCreditUsage::whereIn('cto_activity_id', $ctoApplicationIds)
                              ->orWhereIn('cto_absence_id', $ctoApplicationIds)
                              ->delete();
                Log::info("Deleted CtoCreditUsage records for customer ID: {$customer->id} based on application IDs.");
            } else {
                 Log::info("No CTO applications found for customer ID: {$customer->id}, skipping CtoCreditUsage deletion.");
            }


            Log::info("--- START RECALCULATION FOR EMPLOYEE ID: " . $customer->id . " ---");
            $currentRunningBalance = (float)$customer->balance_forwarded_cto; 
            Log::info("Initial Customer Forwarded Balance: " . $customer->balance_forwarded_cto . " | Running Balance after forwarded: " . $currentRunningBalance);
            Log::info("Total CTO Applications to process: " . $ctoRecords->count());

            $activeCreditPool = collect(); 

            // FIX: Iterate over $ctoRecords by value. Modifications to $record will persist because $ctoRecords
            // holds Eloquent models, and model properties are modified directly.
            foreach ($ctoRecords as $record) { // Removed & here
                $currentEventDateForLog = $record->is_activity ? $record->date_of_activity_start : $record->date_of_absence_start;
                $currentEventDateForLog = Carbon::parse($currentEventDateForLog ?? $record->created_at);

                Log::info("Processing Record ID: " . $record->id . 
                          " | Type: " . ($record->is_activity ? "Activity (Earned " . $record->credits_earned . ")" : "Absence (Used " . $record->no_of_days . ")") .
                          " | Date (Sorted): " . $currentEventDateForLog->toDateString() .
                          " | Running Balance before record & expiry check: " . $currentRunningBalance);
                Log::info("  Active Credits Pool before this transaction:", $activeCreditPool->pluck('remaining', 'record.id')->toArray());

                // FIX: Iterate over keys, get item, modify its property, then put back if it's an array.
                // $activeCreditPool stores arrays like ['record' => model, 'remaining' => float, ...].
                // So, we need to get the array, modify its 'remaining' key, and put the array back.
                foreach ($activeCreditPool->sortBy('expiration_date')->keys() as $poolKey) {
                    $poolEntry = $activeCreditPool->get($poolKey); // This is an array, it's a copy
                    $currentEventDate = $record->is_activity ? $record->date_of_activity_start : $record->date_of_absence_start;
                    $currentEventDate = Carbon::parse($currentEventDate ?? $record->created_at);

                    if ($poolEntry && $currentEventDate->greaterThanOrEqualTo($poolEntry['expiration_date'])) {
                        $expiredAmount = (float)$poolEntry['remaining']; 
                        if ($expiredAmount > 0) {
                            $currentRunningBalance -= $expiredAmount;
                            Log::info("    -> EXPIRED: Activity ID {$poolKey} expired on {$poolEntry['expiration_date']->toDateString()} at transaction date {$currentEventDate->toDateString()}. Deducting {$expiredAmount} from running balance. Balance now: {$currentRunningBalance}");
                        }
                        $activeCreditPool->forget($poolKey); // Remove by key
                    }
                }
                Log::info("  Active Credits Pool after expiry check:", $activeCreditPool->pluck('remaining', 'record.id')->toArray());

                if ($record->is_activity) {
                    $earnedCredits = (float)$record->credits_earned;
                    $currentRunningBalance += $earnedCredits;
                    Log::info("  Processing Activity (ID: " . $record->id . "): Added " . $earnedCredits . ". Running Balance now: " . $currentRunningBalance);

                    $activityStartDate = Carbon::parse($record->date_of_activity_start ?? $record->created_at);

                    if (!$record->isExpiredAt($activityStartDate)) {
                        $activeCreditPool->put($record->id, [
                            'record' => $record,
                            'remaining' => $earnedCredits,
                            'expiration_date' => ($record->date_of_activity_end instanceof \Carbon\Carbon) ? $record->date_of_activity_end->copy()->addYear() : (\Carbon\Carbon::parse($record->date_of_activity_end ?? $record->created_at)->addYear() ?? Carbon::maxValue()),
                            'original_credits' => $earnedCredits,
                        ]);
                        Log::info("  Activity ID " . $record->id . " added to active pool. Pool:", $activeCreditPool->pluck('remaining', 'record.id')->toArray());
                    } else {
                        Log::info("  Activity ID " . $record->id . " earned but immediately expired on its effective date. Not added to active pool.");
                    }

                } else { // This is an absence
                    $hoursToDeduct = (float)$record->no_of_days;
                    $currentRunningBalance -= $hoursToDeduct; 
                    Log::info("  Processing Absence (ID: " . $record->id . "): Deducted " . $hoursToDeduct . ". Running Balance now: " . $currentRunningBalance);

                    $remainingDeductionForThisAbsence = $hoursToDeduct;

                    $activeCreditPool = $activeCreditPool->sort(function($a, $b) {
                        $expA = $a['expiration_date'];
                        $expB = $b['expiration_date'];
                        if ($expA->equalTo($expB)) {
                            $endA = ($a['record']->date_of_activity_end instanceof \Carbon\Carbon) ? $a['record']->date_of_activity_end : \Carbon\Carbon::parse($a['record']->date_of_activity_end ?? $a['record']->created_at);
                            $endB = ($b['record']->date_of_activity_end instanceof \Carbon\Carbon) ? $b['record']->date_of_activity_end : \Carbon\Carbon::parse($b['record']->date_of_activity_end ?? $b['record']->created_at);
                            
                            if ($endA->equalTo($endB)) {
                                return $a['record']->id <=> $b['record']->id;
                            }
                            return $endA <=> $endB;
                        }
                        return $expA <=> $expB;
                    })->values();

                    Log::info("  Absence ID {$record->id}: Needs {$hoursToDeduct} hours. Current sorted pool:", $activeCreditPool->pluck('remaining', 'record.id')->toArray());

                    // FIX: Iterate over keys, get item, modify its property, then put back into the collection
                    foreach ($activeCreditPool->keys() as $poolKey) { // Iterate over keys
                        $poolEntry = $activeCreditPool->get($poolKey); // Get the item array (this is a copy)
                        
                        if ($remainingDeductionForThisAbsence <= 0) {
                            break;
                        }

                        $availableFromActivity = (float)$poolEntry['remaining'];

                        if ($availableFromActivity > 0) {
                            $deductAmount = min($remainingDeductionForThisAbsence, $availableFromActivity);

                            if ($deductAmount > 0) {
                                CtoCreditUsage::create([
                                    'cto_activity_id' => $poolEntry['record']->id,
                                    'cto_absence_id' => $record->id,
                                    'days_used' => $deductAmount,
                                ]);
                                
                                $poolEntry['remaining'] -= $deductAmount; // Modify the copy of the array
                                $activeCreditPool->put($poolKey, $poolEntry); // Put the modified array back into the collection
                                
                                $remainingDeductionForThisAbsence -= $deductAmount;
                                Log::info("    Used {$deductAmount} hours from Activity ID {$poolEntry['record']->id} for Absence ID {$record->id}. Pool remaining for activity: {$activeCreditPool->get($poolKey)['remaining']}. Deduction remaining for absence: {$remainingDeductionForThisAbsence}");
                            }
                        }
                    }
                    
                    if ($remainingDeductionForThisAbsence > 0) {
                        Log::warning("Customer ID: {$customer->id}, Absence ID: {$record->id} could not be fully covered during recalculation by available *active pool credits*. Remaining: {$remainingDeductionForThisAbsence}. This suggests a discrepancy in `getEligibleCtoBalance` or data corruption.");
                    }
                }

                $newRunningBalanceValue = round($currentRunningBalance, 2);
                
                $record->balance = $newRunningBalanceValue; 

                if ($record->is_activity) {
                    $poolDataForThisActivity = $activeCreditPool->firstWhere('record.id', $record->id);
                    $record->current_balance = $poolDataForThisActivity['remaining'] ?? 0;
                } else {
                    $record->current_balance = 0;
                }

                $record->save(); 
                Log::info("  CTO record ID " . $record->id . " running total 'balance' updated to: " . $newRunningBalanceValue . ". 'current_balance' updated to: " . $record->current_balance);
                Log::info("--- END OF RECORD ID: " . $record->id . " PROCESSING ---");
            }
            // No unset needed if $record is not by reference here

            $customer->update(['balance_cto' => round($currentRunningBalance, 2)]);
            Log::info("Final Customer ID: {$customer->id} Overall CTO Balance updated to: " . round($currentRunningBalance, 2));

            DB::commit();
            Log::info("--- END RECALCULATION FOR EMPLOYEE ID: {$customer->id} ---");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error during CTO balance recalculation for Customer ID: {$customer->id}: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw new \Exception("Failed to recalculate CTO balances: " . $e->getMessage());
        }
    }


    /**
     * Deletes a CTO record (activity or absence) and recalculates balances.
     * @param \App\CtoApplication $ctoApplication
     * @return void
     * @throws \Exception
     */
    public function deleteCtoRecord(CtoApplication $ctoApplication)
    {
        DB::beginTransaction();
        try {
            if ($ctoApplication->is_activity) {
                CtoCreditUsage::where('cto_activity_id', $ctoApplication->id)->delete();
                Log::info("Deleted CtoCreditUsage for activity ID: {$ctoApplication->id}");
            } else {
                CtoCreditUsage::where('cto_absence_id', $ctoApplication->id)->delete();
                Log::info("Deleted CtoCreditUsage for absence ID: {$ctoApplication->id}");
            }

            $ctoApplication->delete();
            Log::info("Deleted CTO Application ID: {$ctoApplication->id}");

            $this->recalculateBalancesForCustomer($ctoApplication->customer);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting CTO record ID: {$ctoApplication->id}: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw new \Exception("Failed to delete CTO record: " . $e->getMessage());
        }
    }

    /**
     * Calculates the number of working days (excluding weekends) between two dates.
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return float
     */
    public function calculateWorkingDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->greaterThan($end)) {
            return 0.0;
        }

        $days = 0;
        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $days++;
            }
            $start->addDay();
        }
        return (float)$days;
    }

    /**
     * Get the total eligible CTO balance for a customer as of a specific date.
     * @param \App\Customer $customer
     * @param Carbon $asOfDate The date to check the balance against.
     * @return float
     */
    public function getEligibleCtoBalance(Customer $customer, Carbon $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? Carbon::now();

        Log::debug("Calculating eligible CTO balance for customer ID: {$customer->id} as of {$asOfDate->format('Y-m-d')}");

        $totalEligibleCredits = $customer->ctoApplications()
            ->where('is_activity', true)
            ->where(function($query) use ($asOfDate) {
                $query->whereDate('date_of_activity_start', '<=', $asOfDate);
            })
            ->get()
            ->filter(function($activity) use ($asOfDate) {
                $isExpired = $activity->isExpiredAt($asOfDate);
                $hasRemaining = $activity->remaining_credits > 0;
                
                if ($isExpired) {
                    Log::debug("   Skipping Activity ID {$activity->id} (earned {$activity->credits_earned}) because it expired on {$activity->date_of_activity_end->copy()->addYear()->format('Y-m-d')} as of {$asOfDate->format('Y-m-d')}.");
                } elseif (!$hasRemaining) {
                    Log::debug("   Skipping Activity ID {$activity->id} (earned {$activity->credits_earned}) because it has 0 remaining credits.");
                }
                
                return !$isExpired && $hasRemaining;
            })
            ->sum('remaining_credits');

        $totalEligibleCredits += $customer->balance_forwarded_cto;

        $finalEligibleBalance = (float)round($totalEligibleCredits, 2);
        Log::debug("Final eligible CTO balance for customer ID {$customer->id}: {$finalEligibleBalance}");

        return max(0.0, $finalEligibleBalance);
    }

    /**
     * Get current *total* CTO balance for a customer (sum of all earned - sum of all used).
     * @param \App\Customer $customer
     * @return float
     */
    public function getCurrentCtoBalance(Customer $customer)
    {
        $totalEarned = $customer->ctoApplications()
            ->where('is_activity', true)
            ->sum('credits_earned');

        $totalUsed = $customer->ctoApplications()
            ->where('is_activity', false)
            ->sum('no_of_days');

        return (float)($totalEarned - $totalUsed + $customer->balance_forwarded_cto);
    }
} //for pull request