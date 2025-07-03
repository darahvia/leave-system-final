<?php
// app/Services/LeaveService.php
namespace App\Services;

use App\Customer;
use App\LeaveApplication;
use Carbon\Carbon;

class LeaveService
{
    /**
     * Process leave application and calculate balances
     */
    public function processLeaveApplication(Customer $customer, array $leaveData, LeaveApplication $leaveApplication = null)
    {
        $leaveType = strtolower($leaveData['leave_type']);
        $workingDays = $leaveData['working_days'];
        $leaveDate = $leaveData['inclusive_date_start'] ?? $leaveData['date_filed'];

        // For new applications, check if customer has sufficient leave balance
        if (!$leaveApplication && !$this->hasSufficientBalance($customer, $leaveType, $workingDays, $leaveDate)) {
            throw new \Exception("Insufficient {$leaveType} balance. Available: " .
                $this->getAvailableBalanceAtDate($customer, $leaveType, $leaveDate) . " days");
        }

        if ($leaveApplication) {
            // Update existing leave
            $leaveApplication->update([
                'leave_type' => $leaveData['leave_type'],
                'leave_details' => $leaveData['leave_details'] ?? null,
                'working_days' => $workingDays,
                'inclusive_date_start' => $leaveData['inclusive_date_start'] ?? null,
                'inclusive_date_end' => $leaveData['inclusive_date_end'] ?? null,
                'date_filed' => $leaveData['date_filed'],
                'commutation' => $leaveData['commutation'] ?? null,
            ]);
        } else {
            // Create new leave
            $leaveApplication = LeaveApplication::create([
                'customer_id' => $customer->id,
                'leave_type' => $leaveData['leave_type'],
                'leave_details' => $leaveData['leave_details'] ?? null,
                'working_days' => $workingDays,
                'inclusive_date_start' => $leaveData['inclusive_date_start'] ?? null,
                'inclusive_date_end' => $leaveData['inclusive_date_end'] ?? null,
                'date_filed' => $leaveData['date_filed'],
                'commutation' => $leaveData['commutation'] ?? null,
            ]);
        }

        // Handle different leave type deductions
        $this->recalculateBalancesFromDate($customer, $leaveDate);

        $this->processLeaveDeductions($customer, $leaveType, $workingDays, $leaveDate);

        return $leaveApplication;
    }

    /**
     * Process leave deductions based on leave type
     */
    private function processLeaveDeductions(Customer $customer, string $leaveType, int $workingDays, $leaveDate)
    {
        switch ($leaveType) {
            case 'vl':
                // Recalculate VL balances from the affected date onwards
                $this->recalculateBalancesFromDate($customer, $leaveDate);
                break;
                
            case 'sl':
                // Recalculate SL balances from the affected date onwards
                $this->recalculateBalancesFromDate($customer, $leaveDate);
                break;
                
            case 'spl':
                // Deduct from SPL balance
                $customer->deductLeave('spl', $workingDays);
                break;
            case 'fl':
                // Force Leave: Deduct from both FL and VL
                $customer->deductLeave('fl', $workingDays);
                $customer->deductLeave('vl', $workingDays); 
                // Also recalculate VL balances since FL affects VL too
                $this->recalculateBalancesFromDate($customer, $leaveDate);
                break;
                
            case 'solo parent':
                $customer->deductLeave('solo_parent', $workingDays);
                break;
                
            case 'ml':
                $customer->deductLeave('ml', $workingDays);
                break;
                
            case 'pl':
                $customer->deductLeave('pl', $workingDays);
                break;
                
            case 'ra9710':
                $customer->deductLeave('ra9710', $workingDays);
                break;
                
            case 'rl':
                $customer->deductLeave('rl', $workingDays);
                break;
                
            case 'sel':
                $customer->deductLeave('sel', $workingDays);
                break;
                
            case 'study_leave':
                $customer->deductLeave('study_leave', $workingDays);
                break;
            case 'vawc':
                $customer->deductLeave('vawc', $workingDays);
                break;
                
            default:
                // Handle other leave types or throw exception
                throw new \Exception("Unknown leave type: {$leaveType}");
        }
    }

    /**
     * Deduct from current VL balance (for Force Leave)
     */
    private function deductFromCurrentVL(Customer $customer, int $workingDays)
    {
        $latestApplication = $customer->leaveApplications()
            ->orderBy('inclusive_date_start', 'desc')
            ->orderBy('earned_date', 'desc')
            ->orderBy('date_filed', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestApplication) {
            $currentVL = max(0, $latestApplication->current_vl - $workingDays);
            $latestApplication->update(['current_vl' => $currentVL]);
        }
    }

    /**
     * Enhanced balance checking with proper validations
     */
    private function hasSufficientBalance(Customer $customer, string $leaveType, int $workingDays, $atDate = null)
    {
        $availableBalance = $atDate
            ? $this->getAvailableBalanceAtDate($customer, $leaveType, $atDate)
            : $this->getAvailableBalance($customer, $leaveType);
        
        // Special case for Force Leave - check both FL and VL balances
        if ($leaveType === 'fl') {
            $flBalance = $customer->getCurrentLeaveBalance('fl');
            $vlBalance = $atDate 
                ? $this->getAvailableBalanceAtDate($customer, 'vl', $atDate)
                : $this->getAvailableBalance($customer, 'vl');
            
            return ($flBalance >= $workingDays) && ($vlBalance >= $workingDays);
        }
        
        return $availableBalance >= $workingDays;
    }

    /**
     * Get available balance for a specific leave type at a specific date
     */
    private function getAvailableBalanceAtDate(Customer $customer, string $leaveType, $atDate)
    {
        if (in_array($leaveType, ['vl', 'sl'])) {
            $balances = $this->getBalancesBeforeDate($customer, $atDate);
            return $balances[$leaveType] ?? 0;
        }

        // For other leave types, use current balance from customer model
        return $customer->getCurrentLeaveBalance($leaveType);
    }

    /**
     * Recalculate all VL/SL balances from a specific date onwards
     */
    private function recalculateBalancesFromDate(Customer $customer, $fromDate)
    {
        // Get all leave applications (including credits) from the specified date onwards
        $leaves = LeaveApplication::where('customer_id', $customer->id)
            ->where(function($query) use ($fromDate) {
                $query->whereDate('inclusive_date_start', '>=', $fromDate)
                      ->orWhereDate('earned_date', '>=', $fromDate);
            })
            ->get()
            ->sortBy(function($leave) {
                $sortDate = $leave->date_filed ?? $leave->earned_date ?? $leave->inclusive_date_start;
                
                return $sortDate . '-' . str_pad($leave->id, 10, '0', STR_PAD_LEFT);
            });

        // Get the balance just before this date
        $balances = $this->getBalancesBeforeDate($customer, $fromDate);

        // Recalculate each leave application's current_vl and current_sl
    foreach ($leaves as $leave) {
        if ($leave->is_credit_earned) {
            // Add earned credits
            $balances['vl'] += $leave->earned_vl ?? 1.25;
            $balances['sl'] += $leave->earned_sl ?? 1.25;
        } else {
            // Handle leave deduction or cancellation credit restoration
            $leaveType = strtolower($leave->leave_type);
            $workingDays = $leave->working_days;
            
            if ($leaveType === 'vl') {
                if ($leave->is_cancellation ?? false) {
                    // Cancellation: add credits back (working_days is negative for cancellations)
                    $balances['vl'] += abs($workingDays);
                } else {
                    // Regular leave: deduct credits
                    $balances['vl'] = max(0, $balances['vl'] - $workingDays);
                }
            } elseif ($leaveType === 'sl') {
                if ($leave->is_cancellation ?? false) {
                    // Cancellation: add credits back (working_days is negative for cancellations)
                    $balances['sl'] += abs($workingDays);
                } else {
                    // Regular leave: deduct credits
                    $balances['sl'] = max(0, $balances['sl'] - $workingDays);
                }
            } elseif ($leaveType === 'fl') {
                // Force Leave also deducts from VL
                $balances['vl'] = max(0, $balances['vl'] - $workingDays);
            }
        }

            // Update the leave application with new balances
            $leave->update([
                'current_vl' => $balances['vl'],
                'current_sl' => $balances['sl'],
            ]);
        }
    }

    /**
     * Get VL/SL balances just before a specific date
     */
    private function getBalancesBeforeDate(Customer $customer, $beforeDate)
    {
        // Get all leave applications before the specified date
        $leaves = LeaveApplication::where('customer_id', $customer->id)
            ->where(function($query) use ($beforeDate) {
                $query->whereDate('inclusive_date_start', '<', $beforeDate)
                    ->orWhereDate('earned_date', '<', $beforeDate);
            })
            ->get()
            ->sortBy(function($leave) {
                $sortDate = $leave->date_filed ?? $leave->earned_date ?? $leave->inclusive_date_start;
                return $sortDate . '-' . str_pad($leave->id, 10, '0', STR_PAD_LEFT);
            });

        // Start with forwarded balances
        $balances = [
            'vl' => $customer->balance_forwarded_vl ?? 0,
            'sl' => $customer->balance_forwarded_sl ?? 0,
        ];

        // Apply all leave applications chronologically
        foreach ($leaves as $leave) {
            if ($leave->is_credit_earned) {
                $balances['vl'] += $leave->earned_vl ?? 1.25;
                $balances['sl'] += $leave->earned_sl ?? 1.25;
            } else {
                $leaveType = strtolower($leave->leave_type);
                if ($leaveType === 'vl') {
                    $balances['vl'] = max(0, $balances['vl'] - $leave->working_days);
                } elseif ($leaveType === 'sl') {
                    $balances['sl'] = max(0, $balances['sl'] - $leave->working_days);
                } elseif ($leaveType === 'fl') {
                    // Force Leave also deducts from VL
                    $balances['vl'] = max(0, $balances['vl'] - $leave->working_days);
                }
            }
        }

        return $balances;
    }

    /**
     * Get balance before a specific leave (for editing purposes)
     */
    public function getBalanceBeforeLeave(Customer $customer, LeaveApplication $leaveToEdit, $type = 'vl')
    {
        $leaveDate = $leaveToEdit->inclusive_date_start ?? $leaveToEdit->date_filed;
       
        // Get all leave applications before this one (by date, not ID)
        $leaves = LeaveApplication::where('customer_id', $customer->id)
            ->where('id', '!=', $leaveToEdit->id) // Exclude the leave being edited
            ->where(function($query) use ($leaveDate) {
                $query->where('inclusive_date_start', '<', $leaveDate)
                      ->orWhere('earned_date', '<', $leaveDate);
            })
            ->where(function($query) use ($type) {
                $query->where('leave_type', $type)
                      ->orWhere('is_credit_earned', true);
            })
            ->orderBy('inclusive_date_start')
            ->orderBy('earned_date')
            ->orderBy('date_filed')
            ->orderBy('id')
            ->get();

        // Start with forwarded balance
        $balance = $type === 'vl' ? $customer->balance_forwarded_vl : $customer->balance_forwarded_sl;

        foreach ($leaves as $leave) {
            if ($leave->is_credit_earned) {
                $balance += $type === 'vl' ? ($leave->earned_vl ?? 1.25) : ($leave->earned_sl ?? 1.25);
            } else {
                $leaveType = strtolower($leave->leave_type);
                if ($leaveType === $type) {
                    $balance -= $leave->working_days ?? 0;
                } elseif ($leaveType === 'fl' && $type === 'vl') {
                    // Force Leave also deducts from VL
                    $balance -= $leave->working_days ?? 0;
                }
            }
        }

        return max(0, $balance);
    }

    /**
     * Get current balances for all leave types
     */
    public function getCurrentBalances(Customer $customer)
    {
        $lastApplication = $customer->leaveApplications()
            ->orderBy('inclusive_date_start', 'desc')
            ->orderBy('earned_date', 'desc')
            ->orderBy('date_filed', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return [
            'vl' => $lastApplication ? $lastApplication->current_vl : $customer->balance_forwarded_vl,
            'sl' => $lastApplication ? $lastApplication->current_sl : $customer->balance_forwarded_sl,
            'spl' => $customer->spl,
            'fl' => $customer->fl,
            'solo_parent' => $customer->solo_parent,
            'ml' => $customer->ml,
            'pl' => $customer->pl,
            'ra9710' => $customer->ra9710,
            'rl' => $customer->rl,
            'sel' => $customer->sel,
            'study_leave' => $customer->study_leave,
            'vawc' => $customer->vawc,

        ];
    }

    /**
     * Get available balance for a specific leave type (current balance)
     */
    private function getAvailableBalance(Customer $customer, string $leaveType)
    {
        return $customer->getCurrentLeaveBalance($leaveType);
    }

    /**
     * Add credits earned (monthly leave credits)
     */
    public function addCreditsEarned(Customer $customer, $earnedDate, $vlCredits = 1.25, $slCredits = 1.25)
    {
        $leaveApplication = LeaveApplication::create([
            'customer_id' => $customer->id,
            'is_credit_earned' => true,
            'earned_date' => $earnedDate,
            'earned_vl' => $vlCredits,
            'earned_sl' => $slCredits,
        ]);

        // Recalculate balances from this date onwards
        $this->recalculateBalancesFromDate($customer, $earnedDate);

        return $leaveApplication;
    }

    /**
     * Delete a leave application and recalculate balances
     */
    public function deleteLeaveApplication(LeaveApplication $leaveApplication)
    {
        $customer = $leaveApplication->customer;
        $leaveDate = $leaveApplication->inclusive_date_start ?? $leaveApplication->earned_date ?? $leaveApplication->date_filed;
        $leaveType = strtolower($leaveApplication->leave_type ?? '');
        $workingDays = $leaveApplication->working_days ?? 0;
        
        // If it's a non-VL/SL leave type, restore the balance back to customer model
        if (!in_array($leaveType, ['vl', 'sl']) && !$leaveApplication->is_credit_earned) {
            $this->restoreLeaveBalance($customer, $leaveType, $workingDays);
        }
       
        // Delete the leave application
        $leaveApplication->delete();
       
        // Recalculate VL/SL balances from this date onwards
        if (in_array($leaveType, ['vl', 'sl', 'fl']) || $leaveApplication->is_credit_earned) {
            $this->recalculateBalancesFromDate($customer, $leaveDate);
        }
    }

    /**
     * Restore leave balance when deleting a leave application
     */
    private function restoreLeaveBalance(Customer $customer, string $leaveType, int $workingDays)
    {
        switch ($leaveType) {
            case 'spl':
                $customer->spl = ($customer->spl ?? 0) + $workingDays;
                break;
            case 'fl':
                $customer->fl = ($customer->fl ?? 0) + $workingDays;
                break;
            case 'solo_parent':
                $customer->solo_parent = ($customer->solo_parent ?? 0) + $workingDays;
                break;
            case 'ml':
                $customer->ml = ($customer->ml ?? 0) + $workingDays;
                break;
            case 'pl':
                $customer->pl = ($customer->pl ?? 0) + $workingDays;
                break;
            case 'ra9710':
                $customer->ra9710 = ($customer->ra9710 ?? 0) + $workingDays;
                break;
            case 'rl':
                $customer->rl = ($customer->rl ?? 0) + $workingDays;
                break;
            case 'sel':
                $customer->sel = ($customer->sel ?? 0) + $workingDays;
                break;
            case 'study_leave':
                $customer->study_leave = ($customer->study_leave ?? 0) + $workingDays;
                break;
            case 'vawc':
                $customer->vawc = ($customer->vawc ?? 0) + $workingDays;
                break;
        }
        
        $customer->save();
    }

    /**
     * Get leave type display names
     */
    public static function getLeaveTypes()
    {
        return [
            'VL' => 'Vacation Leave',
            'FL' => 'Mandatory/Forced Leave',
            'SL' => 'Sick Leave',
            'ML' => 'Maternity Leave',
            'PL' => 'Paternity Leave',
            'SPL' => 'Special Privilege Leave',
            'SOLO_PARENT' => 'Solo Parent Leave',
            'STUDY_LEAVE' => 'Study Leave',
            'VAWC' => '10-Day VAWC Leave',
            'RL' => 'Rehabilitation Privilege',
            'RA9710' => 'Special Leave Benefits for Women',
            'SEL' => 'Special Emergency Leave',
            'ADOPT' => 'Adoption Leave',
        ];
    }
    public function processCancellation(Customer $customer, array $cancellationData)
    {
        $leaveType = strtolower($cancellationData['leave_type']);
        $workingDays = $cancellationData['working_days']; // Credits to restore
        $cancellationDate = $cancellationData['date_filed']; // Date cancellation was filed
        $effectiveDate = $cancellationData['inclusive_date_start']; // When cancellation takes effect

        // Create a new leave application record for the cancellation
        // This will appear as a new row in the table
        $cancellationApplication = LeaveApplication::create([
            'customer_id' => $customer->id,
            'leave_type' => $cancellationData['leave_type'],
            'leave_details' => 'CANCELLED - Credits Restored',
            'working_days' => -$workingDays, // Negative value to indicate credit restoration
            'inclusive_date_start' => $effectiveDate,
            'inclusive_date_end' => $cancellationData['inclusive_date_end'],
            'date_filed' => $cancellationDate,
            'commutation' => null,
            'is_cancellation' => true, // Mark as cancellation entry
        ]);

        // Recalculate all balances from the effective date onwards
        $this->recalculateBalancesFromDate($customer, $effectiveDate);

        // For non-VL/SL leave types, add credits back to customer balance
        if (!in_array($leaveType, ['vl', 'sl'])) {
            $customer->addLeaveCredits($leaveType, $workingDays); // Add credits back
        }

        return $cancellationApplication;
    }
}
