<?php
// app/Services/CtoService.php
namespace App\Services;

// Updated to use 'App\Customer' as per your model's new namespace
use App\Customer; 
use App\CtoApplication; // Updated to use 'App\CtoApplication'
use App\CtoCreditUsage; // Updated to use 'App\CtoCreditUsage'
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CtoService
{
    /**
     * Processes a CTO activity (credits earned).
     *
     * @param \App\Customer $customer // Updated type hint in docblock
     * @param array $activityData
     * @param \App\CtoApplication|null $existingRecord // Updated type hint in docblock
     * @return \App\CtoApplication // Updated type hint in docblock
     * @throws \Exception
     */
 public function processCtoActivity(Customer $customer, array $activityData, $existingRecord = null)
{
    DB::beginTransaction();
    try {
        if ($existingRecord) {
            // Update existing activity record
            $ctoApplication = $existingRecord;
            $ctoApplication->update([
                'special_order' => $activityData['special_order'],
                'date_of_activity_start' => $activityData['date_of_activity_start'],
                'date_of_activity_end' => $activityData['date_of_activity_end'],
                'activity' => $activityData['activity'],
                'credits_earned' => $activityData['credits_earned'],
                'is_activity' => true,
                'no_of_days' => null, // Ensure these are null for activities
                'date_of_absence_start' => null,
                'date_of_absence_end' => null,
            ]);
            Log::info("Updated CTO Activity ID: {$ctoApplication->id}");
        } else {
            // Create new activity record
            $ctoApplication = $customer->ctoApplications()->create([
                'special_order' => $activityData['special_order'],
                'date_of_activity_start' => $activityData['date_of_activity_start'],
                'date_of_activity_end' => $activityData['date_of_activity_end'],
                'activity' => $activityData['activity'],
                'credits_earned' => $activityData['credits_earned'],
                'is_activity' => true,
            ]);
            Log::info("Created new CTO Activity ID: {$ctoApplication->id}");
        }

        // After any activity (new or updated), recalculate balances for the customer
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
     * This uses a FIFO (First-In, First-Out) and expiration logic.
     *
     * @param \App\Customer $customer // Updated type hint in docblock
     * @param array $usageData
     * @param \App\CtoApplication|null $existingRecord // Updated type hint in docblock
     * @return \App\CtoApplication // Updated type hint in docblock
     * @throws \Exception
     */
    public function processCtoUsage(Customer $customer, array $usageData, $existingRecord = null)
    {
        DB::beginTransaction();
        try {
            $startDate = Carbon::parse($usageData['date_of_absence_start']);
            $endDate = Carbon::parse($usageData['date_of_absence_end']);
            $daysToDeduct = $usageData['no_of_days'];

            if ($existingRecord) {
                // For updates, we first "undo" the previous usage
                // This means deleting existing CtoCreditUsage records linked to this absence
                // and then recalculating the balance.
                $originalDaysUsed = $existingRecord->no_of_days;
                CtoCreditUsage::where('cto_absence_id', $existingRecord->id)->delete();
                Log::info("Undoing existing CTO usage for absence ID: {$existingRecord->id}. Original days: {$originalDaysUsed}");
                
                // Temporarily set the absence record's no_of_days to 0 for recalculation
                // or ensure recalculation accounts for this. A full recalculation is safer.
                $existingRecord->no_of_days = 0; // Effectively "releases" the credits
                $existingRecord->save();
                $this->recalculateBalancesForCustomer($customer); // Recalculate after undoing
            }

            // Check eligible balance before proceeding (after undoing for updates)
            $eligibleBalance = $this->getEligibleCtoBalance($customer, $startDate);
            if ($eligibleBalance < $daysToDeduct) {
                DB::rollBack();
                throw new \Exception("Insufficient eligible CTO balance. Available: {$eligibleBalance} days, Required: {$daysToDeduct} days.");
            }

            if ($existingRecord) {
                // Update existing absence record
                $ctoApplication = $existingRecord;
                $ctoApplication->update([
                    'date_of_absence_start' => $startDate,
                    'date_of_absence_end' => $endDate,
                    'no_of_days' => $daysToDeduct,
                    'is_activity' => false,
                    'credits_earned' => null, // Ensure these are null for absences
                    'special_order' => null,
                    'activity' => null,
                ]);
                Log::info("Updated CTO Usage ID: {$ctoApplication->id}");
            } else {
                // Create new absence record
                $ctoApplication = $customer->ctoApplications()->create([
                    'date_of_absence_start' => $startDate,
                    'date_of_absence_end' => $endDate,
                    'no_of_days' => $daysToDeduct,
                    'is_activity' => false,
                ]);
                Log::info("Created new CTO Usage ID: {$ctoApplication->id}");
            }

            // Deduct credits using FIFO and expiration logic
            $this->deductCtoCredits($customer, $ctoApplication, $daysToDeduct);

            // Recalculate balances for the customer after the deduction
            $this->recalculateBalancesForCustomer($customer);

            DB::commit();
            return $ctoApplication;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing CTO usage: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            // Re-throw the exception to be caught by the controller
            throw new \Exception("Failed to process CTO usage: " . $e->getMessage());
        }
    }

    /**
     * Deducts CTO credits from available activities using FIFO and expiration logic.
     *
     * @param \App\Customer $customer // Updated type hint in docblock
     * @param \App\CtoApplication $ctoAbsence The CTO application record for the absence. // Updated type hint in docblock
     * @param float $daysToDeduct The total number of days to deduct.
     * @throws \Exception If insufficient credits are available.
     */
    protected function deductCtoCredits(Customer $customer, CtoApplication $ctoAbsence, $daysToDeduct)
    {
        $remainingDeduction = $daysToDeduct;

        // Get all activity records for the customer, ordered by activity end date (FIFO)
        // and then by creation date to handle activities ending on the same day.
        $availableActivities = $customer->ctoApplications()
            ->where('is_activity', true)
            ->where('credits_earned', '>', 0)
            ->orderBy('date_of_activity_end', 'asc') // FIFO by end date
            ->orderBy('id', 'asc') // Then by ID for consistent tie-breaking
            ->get();

        foreach ($availableActivities as $activity) {
            // Check if the activity is expired as of the absence start date
            if ($activity->isExpiredAt($ctoAbsence->date_of_absence_start)) {
                Log::info("Skipping expired CTO Activity ID: {$activity->id} for absence ID: {$ctoAbsence->id}. Expiration Date: {$activity->date_of_activity_end->copy()->addYear()->format('Y-m-d')}, Absence Date: {$ctoAbsence->date_of_absence_start->format('Y-m-d')}");
                continue;
            }

            $remainingActivityCredits = $activity->remaining_credits;

            if ($remainingActivityCredits <= 0) {
                continue; // This activity has no remaining usable credits
            }

            if ($remainingDeduction <= 0) {
                break; // All required days have been deducted
            }

            $deductFromThisActivity = min($remainingDeduction, $remainingActivityCredits);

            if ($deductFromThisActivity > 0) {
                CtoCreditUsage::create([
                    'cto_activity_id' => $activity->id,
                    'cto_absence_id' => $ctoAbsence->id,
                    'days_used' => $deductFromThisActivity,
                ]);
                $remainingDeduction -= $deductFromThisActivity;
                Log::info("Deducted {$deductFromThisActivity} days from CTO Activity ID: {$activity->id} for Absence ID: {$ctoAbsence->id}. Remaining deduction: {$remainingDeduction}");
            }
        }

        if ($remainingDeduction > 0) {
            // This should ideally not happen if getEligibleCtoBalance check was accurate
            Log::error("Insufficient CTO credits after deduction process for customer ID: {$customer->id}. Remaining deduction: {$remainingDeduction}");
            throw new \Exception("Insufficient CTO credits available to cover the full absence after FIFO deduction and expiration.");
        }
    }

    /**
     * Recalculates all CTO balances for a given customer.
     * This method iterates through all CTO records (activities and absences) chronologically,
     * maintaining a running balance and applying FIFO/expiration rules.
     *
     * @param \App\Customer $customer // Updated type hint in docblock
     * @return void
     */
    public function recalculateBalancesForCustomer(Customer $customer)
    {
        DB::beginTransaction();
        try {
            // Fetch all CTO records for the customer, ordered by effective date and then ID
            // Eager load creditUsages and consumedActivities to ensure remaining_credits is accurate
            $ctoRecords = $customer->ctoApplications()
                ->with(['creditUsages', 'consumedActivities'])
                ->get()
                ->sortBy(function($cto) {
                    // Ensure strict chronological order. Prioritize activities on the same date.
                    return $cto->effective_date->timestamp . ($cto->is_activity ? '0' : '1');
                })
                ->values(); // Re-index the collection after sorting

            $currentRunningBalance = 0.0;
            // This pool represents credits that are currently 'active' and not expired/consumed.
            // Key: cto_activity_id, Value: ['record', 'remaining', 'expiration_date', 'original_credits']
            // 'remaining' in this pool is the amount still available from that specific activity.
            $activeCreditPools = collect(); 

            // Reset existing CtoCreditUsage records for the customer to rebuild them from scratch.
            // This is crucial for accurate recalculation, especially after edits or deletions.
            CtoCreditUsage::whereIn('cto_absence_id', $ctoRecords->where('is_activity', false)->pluck('id'))
                          ->orWhereIn('cto_activity_id', $ctoRecords->where('is_activity', true)->pluck('id'))
                          ->delete();

            Log::info("--- START RECALCULATION FOR EMPLOYEE ID: " . $customer->id . " ---");
            Log::info("Initial State: Current Running Balance = " . $currentRunningBalance);
            Log::info("Total CTO Applications to process: " . $ctoRecords->count());

            foreach ($ctoRecords as $record) {
                Log::info("Processing Record ID: " . $record->id . 
                          " | Type: " . ($record->is_activity ? "Activity (Earned " . $record->credits_earned . ")" : "Absence (Used " . $record->no_of_days . ")") .
                          " | Date: " . $record->effective_date->toDateString() .
                          " | Balance before transaction & expiry check: " . $currentRunningBalance);
                Log::info("  Active Credits Pool before this transaction:", $activeCreditPools->toArray());

                // --- Step 1: Check for and process EXPIRED credits from the active pool ---
                // Iterate over a copy of keys to safely remove items from activeCreditPools during iteration
                foreach ($activeCreditPools->keys() as $activityIdInPool) {
                    if (!$activeCreditPools->has($activityIdInPool)) { 
                        continue; // Already removed
                    }

                    $poolEntry = $activeCreditPools->get($activityIdInPool);
                    $expiryDate = $poolEntry['expiration_date']; // This is Carbon instance

                    Log::info("    Checking Pool Activity ID: " . $activityIdInPool . 
                              " | Expiry Date: " . $expiryDate->toDateString() .
                              " | Remaining in Pool: " . $poolEntry['remaining'] .
                              " | Current Transaction Date: " . $record->effective_date->toDateString() .
                              " | Is Expired At Current Transaction Date: " . ($record->effective_date->greaterThanOrEqualTo($expiryDate) ? 'YES' : 'NO'));

                    // If the activity's expiry date is before or on the effective date of the current transaction, it expires.
                    if ($record->effective_date->greaterThanOrEqualTo($expiryDate)) {
                        $amountToDeduct = $poolEntry['remaining']; // Deduct whatever is left in this pool item
                        if ($amountToDeduct > 0) { // Only deduct if there's something left
                            $currentRunningBalance -= $amountToDeduct;
                            Log::info("      -> EXPIRED: Deducting " . $amountToDeduct . " from running balance. Balance now: " . $currentRunningBalance);
                        } else {
                            Log::info("      -> EXPIRED: Activity had 0 remaining, no deduction from running balance.");
                        }
                        $activeCreditPools->forget($activityIdInPool); // Remove from active pool
                    }
                }
                Log::info("  Active Credits Pool after expiry check:", $activeCreditPools->toArray());


                // --- Step 2: Process the current CTO transaction (Activity or Absence) ---
                if ($record->is_activity) {
                    $earnedCredits = (float)$record->credits_earned;
                    $currentRunningBalance += $earnedCredits;
                    Log::info("  Processing Activity (ID: " . $record->id . "): Added " . $earnedCredits . ". Running Balance now: " . $currentRunningBalance);

                    // Add this new activity to the pool of eligible credits if it's not immediately expired
                    if (!$record->isExpiredAt($record->effective_date)) { 
                        $activeCreditPools->put($record->id, [
                            'record' => $record, // Store the actual model instance
                            'remaining' => $earnedCredits, // Initial remaining amount for this activity in the pool
                            'expiration_date' => $record->date_of_activity_end->copy()->addYear(),
                            'original_credits' => $earnedCredits, // Store original for reference
                        ]);
                        Log::info("  Activity ID " . $record->id . " added to active pool. Pool:", $activeCreditPools->toArray());
                    } else {
                        Log::info("  Activity ID " . $record->id . " earned but immediately expired. Not added to active pool.");
                    }

                } else { // This is an absence
                    $daysToDeduct = (float)$record->no_of_days;
                    $currentRunningBalance -= $daysToDeduct;
                    Log::info("  Processing Absence (ID: " . $record->id . "): Deducted " . $daysToDeduct . ". Running Balance now: " . $currentRunningBalance);

                    $remainingDeductionForThisAbsence = $daysToDeduct;

                    // Sort the pool by expiration date (earliest first), then by activity end date, then ID (FIFO)
                    $activeCreditPools = $activeCreditPools->sort(function($a, $b) {
                        $expA = $a['expiration_date'];
                        $expB = $b['expiration_date'];
                        if ($expA->equalTo($expB)) {
                            // If expiration dates are same, use activity end date
                            $endA = $a['record']->date_of_activity_end;
                            $endB = $b['record']->date_of_activity_end;
                            if ($endA->equalTo($endB)) {
                                return $a['record']->id <=> $b['record']->id; // Finally, by ID
                            }
                            return $endA <=> $endB;
                        }
                        return $expA <=> $expB;
                    });
                    
                    Log::info("  Absence {$record->id}: Needs {$daysToDeduct} days. Current sorted pool:", $activeCreditPools->toArray());

                    foreach ($activeCreditPools as $activityId => &$poolEntry) { // <--- FIX: Added '&' here
                        if ($remainingDeductionForThisAbsence <= 0) {
                            break; // All days deducted for this absence
                        }

                        $availableFromActivity = $poolEntry['remaining']; // Use 'remaining' from pool entry

                        if ($availableFromActivity > 0) {
                            $deductAmount = min($remainingDeductionForThisAbsence, $availableFromActivity);

                            if ($deductAmount > 0) {
                                // Create CtoCreditUsage record for this deduction
                                CtoCreditUsage::create([
                                    'cto_activity_id' => $activityId,
                                    'cto_absence_id' => $record->id,
                                    'days_used' => $deductAmount,
                                ]);
                                // Update the remaining credits in the pool entry directly
                                $poolEntry['remaining'] -= $deductAmount; // Direct modification
                                $remainingDeductionForThisAbsence -= $deductAmount;
                                Log::info("    Used {$deductAmount} from Activity ID {$activityId} for Absence ID {$record->id}. Pool remaining for activity: {$poolEntry['remaining']}. Deduction remaining for absence: {$remainingDeductionForThisAbsence}");
                            }
                        }
                    }
                    unset($poolEntry); // Unset reference after loop to prevent unintended modifications
                    if ($remainingDeductionForThisAbsence > 0) {
                        Log::warning("Customer ID: {$customer->id}, Absence ID: {$record->id} could not be fully covered during recalculation. Remaining: {$remainingDeductionForThisAbsence}. This might indicate a data discrepancy.");
                    }
                }

                // --- Step 3: Update the balance field for the current CTO record and save ---
                $newBalanceValue = round($currentRunningBalance, 2);
                if ($record->balance !== $newBalanceValue) {
                    $record->balance = $newBalanceValue;
                    $record->save(); // Save to database
                    Log::info("  CTO record ID " . $record->id . " balance updated to: " . $newBalanceValue);
                } else {
                    Log::info("  CTO record ID " . $record->id . " balance unchanged: " . $newBalanceValue);
                }
                Log::info("--- END OF RECORD ID: " . $record->id . " PROCESSING ---");
            }

            DB::commit();
            Log::info("Final Recalculation for Customer ID: {$customer->id}. Ending Eligible Balance: " . round($currentRunningBalance, 2));
            Log::info("--- END RECALCULATION FOR EMPLOYEE ID: {$customer->id} ---");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error during CTO balance recalculation for Customer ID: {$customer->id}: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw new \Exception("Failed to recalculate CTO balances: " . $e->getMessage());
        }
    }


    /**
     * Deletes a CTO record (activity or absence) and recalculates balances.
     *
     * @param \App\CtoApplication $ctoApplication // Updated type hint in docblock
     * @return void
     * @throws \Exception
     */
    public function deleteCtoRecord(CtoApplication $ctoApplication)
    {
        DB::beginTransaction();
        try {
            $customer = $ctoApplication->customer;

            // Delete associated CtoCreditUsage records first
            if ($ctoApplication->is_activity) {
                CtoCreditUsage::where('cto_activity_id', $ctoApplication->id)->delete();
                Log::info("Deleted CtoCreditUsage for activity ID: {$ctoApplication->id}");
            } else {
                CtoCreditUsage::where('cto_absence_id', $ctoApplication->id)->delete();
                Log::info("Deleted CtoCreditUsage for absence ID: {$ctoApplication->id}");
            }

            // Delete the CTO application record
            $ctoApplication->delete();
            Log::info("Deleted CTO Application ID: {$ctoApplication->id}");

            // Recalculate balances for the customer
            $this->recalculateBalancesForCustomer($customer);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting CTO record ID: {$ctoApplication->id}: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw new \Exception("Failed to delete CTO record: " . $e->getMessage());
        }
    }

    /**
     * Calculates the number of working days (excluding weekends) between two dates.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return float
     */
    public function calculateWorkingDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->greaterThan($end)) {
            return 0.0; // Return float for consistency
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
     * Get the total eligible CTO balance for an customer as of a specific date.
     * This considers only non-expired credits.
     *
     * @param \App\Customer $customer // Updated type hint in docblock
     * @param Carbon $asOfDate The date to check the balance against.
     * @return float
     */
    public function getEligibleCtoBalance(Customer $customer, Carbon $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? Carbon::now();

        // Debugging logs for getEligibleCtoBalance
        Log::debug("Calculating eligible CTO balance for customer ID: {$customer->id} as of {$asOfDate->format('Y-m-d')}");

        // Fetch all CTO activities for the customer that are not expired as of $asOfDate
        $eligibleActivities = $customer->ctoApplications()
            ->where('is_activity', true)
            ->with('creditUsages') // Eager load usages to correctly calculate remaining_credits
            ->get()
            ->filter(function($activity) use ($asOfDate) {
                // Filter out activities that have expired by the asOfDate
                $isExpired = $activity->isExpiredAt($asOfDate);
                if ($isExpired) {
                    Log::debug("  Activity ID {$activity->id} is expired as of {$asOfDate->format('Y-m-d')}. Expiration Date: {$activity->date_of_activity_end->copy()->addYear()->format('Y-m-d')}. Skipping.");
                }
                return !$isExpired;
            });

        $totalEligibleCredits = 0.0;
        foreach ($eligibleActivities as $activity) {
            $remaining = $activity->remaining_credits;
            if ($remaining > 0) {
                $totalEligibleCredits += $remaining;
                Log::debug("  Adding remaining credits ({$remaining}) from Activity ID {$activity->id}. Current total: {$totalEligibleCredits}");
            } else {
                Log::debug("  Activity ID {$activity->id} has no remaining credits ({$remaining}). Skipping.");
            }
        }

        Log::debug("Final eligible CTO balance for customer ID {$customer->id}: {$totalEligibleCredits}");

        return (float)round($totalEligibleCredits, 2);
    }

    /**
     * Get current *total* CTO balance for an customer (sum of all earned - sum of all used).
     * This does NOT consider expiration or FIFO for the total.
     * Use getEligibleCtoBalance() for checks against new absences.
     */
    public function getCurrentCtoBalance(Customer $customer)
    {
        $totalEarned = $customer->ctoApplications()
            ->where('is_activity', true)
            ->sum('credits_earned');

        $totalUsed = $customer->ctoApplications()
            ->where('is_activity', false)
            ->sum('no_of_days');

        return (float)($totalEarned - $totalUsed);
    }
}