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
        $leaveDate = $leaveData['date_filed']; 
        $isLeaveWithoutPay = $leaveData['is_leavewopay'] ?? false;

        // For new applications, check if customer has sufficient leave balance
        if(!$isLeaveWithoutPay){
            if (!$leaveApplication && !$this->hasSufficientBalance($customer, $leaveType, $workingDays)) {
                throw new \Exception("Insufficient {$leaveType} balance. Available: " .
                    $this->getAvailableBalance($customer, $leaveType) . " days");
            }
        }


        // Get current balances before processing
        $currentBalances = $this->getCurrentBalances($customer);

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
                'is_leavewopay' => $isLeaveWithoutPay,
                'current_vl' => $currentBalances['vl'],
                'current_sl' => $currentBalances['sl'],
            ]);
        } else {
            $newVlBalance = $currentBalances['vl'];
            $newSlBalance = $currentBalances['sl'];
            if (!$isLeaveWithoutPay){
                // Process leave deductions first (for non-VL/SL types)
                $this->processLeaveDeductions($customer, $leaveType, $workingDays);
                
                // Calculate new VL/SL balances manually
                $currentBalances = $this->getCurrentBalances($customer);
                $newVlBalance = $currentBalances['vl'];
                $newSlBalance = $currentBalances['sl'];
                
                // Apply VL/SL deductions with proper balance checking
                if ($leaveType === 'vl') {
                    if ($newVlBalance < $workingDays) {
                        throw new \Exception("Insufficient VL balance. Available: {$newVlBalance} days, Required: {$workingDays} days");
                    }
                    $newVlBalance = $newVlBalance - $workingDays;
                } elseif ($leaveType === 'sl') {
                    if ($newSlBalance < $workingDays) {
                        throw new \Exception("Insufficient SL balance. Available: {$newSlBalance} days, Required: {$workingDays} days");
                    }
                    $newSlBalance = $newSlBalance - $workingDays;
                } elseif ($leaveType === 'fl') {
                    // Force Leave also deducts from VL - balance already checked in hasSufficientBalance
                    if ($newVlBalance < $workingDays) {
                        throw new \Exception("Insufficient VL balance for Force Leave. Available: {$newVlBalance} days, Required: {$workingDays} days");
                    }
                    $newVlBalance = $newVlBalance - $workingDays;
                }
            }
            
            // Create new leave with calculated balances
            $leaveApplication = LeaveApplication::create([
                'customer_id' => $customer->id,
                'leave_type' => $leaveData['leave_type'],
                'leave_details' => $leaveData['leave_details'] ?? null,
                'working_days' => $workingDays,
                'inclusive_date_start' => $leaveData['inclusive_date_start'] ?? null,
                'inclusive_date_end' => $leaveData['inclusive_date_end'] ?? null,
                'date_filed' => $leaveData['date_filed'],
                'commutation' => $leaveData['commutation'] ?? null,
                'is_leavewopay' => $isLeaveWithoutPay,
                'current_vl' => $newVlBalance,
                'current_sl' => $newSlBalance,
            ]);
        }

        return $leaveApplication;
    }

    /**
     * Process leave deductions based on leave type
     */
    private function processLeaveDeductions(Customer $customer, string $leaveType, float $workingDays)
    {
        switch ($leaveType) {
            case 'vl':
                break;
            case 'sl':
                break;
            case 'spl':
                $customer->deductLeave('spl', $workingDays);
                break;
                
            case 'fl':
                // Force Leave: Deduct from FL only (VL is handled through running balance)
                $customer->deductLeave('fl', $workingDays);
                break;
                
            case 'solo_parent':
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
                
            case 'adopt':
                $customer->deductLeave('adopt', $workingDays);
                break;
                
            default:
                throw new \Exception("Unknown leave type: {$leaveType}");
        }
    }

    /**
     * Check if customer has sufficient balance for the leave type
     */
    private function hasSufficientBalance(Customer $customer, string $leaveType, float $workingDays)
    {
        $availableBalance = $this->getAvailableBalance($customer, $leaveType);
        
        // Special case for Force Leave - check both FL and VL balances
        if ($leaveType === 'fl') {
            $flBalance = $customer->getCurrentLeaveBalance('fl');
            $vlBalance = $customer->getCurrentLeaveBalance('vl');
            
            if ($flBalance < $workingDays) {
                throw new \Exception("Insufficient Force Leave balance. Available FL: {$flBalance}");
            }
            
            if ($vlBalance < $workingDays) {
                throw new \Exception("Insufficient Vacation Leave balance. Available VL: {$vlBalance}");
            }
            
            return true;
        }
        
        return $availableBalance >= $workingDays;
    }

    /**
     * Get current balances for all leave types
     */
    public function getCurrentBalances(Customer $customer)
    {
        // Get the latest leave application ordered by date_filed
        $lastApplication = $customer->leaveApplications()
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
            'adopt' => $customer->adopt,
        ];
    }

    /**
     * Get available balance for a specific leave type
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
        // Get current balances
        $currentBalances = $this->getCurrentBalances($customer);
        
        // Calculate new balances after adding credits
        $newVlBalance = $currentBalances['vl'] + $vlCredits;
        $newSlBalance = $currentBalances['sl'] + $slCredits;
        
        // Create leave application record for the credits
        $leaveApplication = LeaveApplication::create([
            'customer_id' => $customer->id,
            'is_credit_earned' => true,
            'earned_date' => $earnedDate,
            'date_filed' => $earnedDate, // Use earned_date as date_filed for ordering
            'earned_vl' => $vlCredits,
            'earned_sl' => $slCredits,
            'current_vl' => $newVlBalance,
            'current_sl' => $newSlBalance,
        ]);

        return $leaveApplication;
    }

    /**
     * Delete a leave application and restore balances
     */
    public function deleteLeaveApplication(LeaveApplication $leaveApplication)
    {
        $customer = $leaveApplication->customer;
        $leaveType = strtolower($leaveApplication->leave_type ?? '');
        $workingDays = $leaveApplication->working_days ?? 0;
        
        // If it's a credit earned entry, we don't need to restore anything
        // since VL/SL are managed through leave applications
        if (!$leaveApplication->is_credit_earned) {
            // Only restore balances for non-VL/SL leave types
            if (!in_array($leaveType, ['vl', 'sl'])) {
                $this->restoreLeaveBalance($customer, $leaveType, $workingDays);
            }
        }
       
        // Delete the leave application
        $leaveApplication->delete();
    }

    /**
     * Restore leave balance when deleting a leave application
     */
    private function restoreLeaveBalance(Customer $customer, string $leaveType, float $workingDays)
    {
        switch ($leaveType) {
            case 'vl':
                // VL is managed through leave applications, not customer balance
                break;
            case 'sl':
                // SL is managed through leave applications, not customer balance
                break;
            case 'spl':
                $customer->addLeaveCredits('spl', $workingDays);
                break;
            case 'fl':
                // Force Leave: Only restore FL (VL is managed through leave applications)
                $customer->addLeaveCredits('fl', $workingDays);
                break;
            case 'solo_parent':
                $customer->addLeaveCredits('solo_parent', $workingDays);
                break;
            case 'ml':
                $customer->addLeaveCredits('ml', $workingDays);
                break;
            case 'pl':
                $customer->addLeaveCredits('pl', $workingDays);
                break;
            case 'ra9710':
                $customer->addLeaveCredits('ra9710', $workingDays);
                break;
            case 'rl':
                $customer->addLeaveCredits('rl', $workingDays);
                break;
            case 'sel':
                $customer->addLeaveCredits('sel', $workingDays);
                break;
            case 'study_leave':
                $customer->addLeaveCredits('study_leave', $workingDays);
                break;
            case 'vawc':
                $customer->addLeaveCredits('vawc', $workingDays);
                break;
            case 'adopt':
                $customer->addLeaveCredits('adopt', $workingDays);
                break;
        }
        
        $customer->save();
    }

    /**
     * Process cancellation - restore credits
     */
    public function processCancellation(Customer $customer, array $cancellationData)
    {
        $leaveType = strtolower($cancellationData['leave_type']);
        $workingDays = $cancellationData['working_days']; // Credits to restore
        $cancellationDate = $cancellationData['date_filed']; // Date cancellation was filed

        // Get current balances
        $currentBalances = $this->getCurrentBalances($customer);
        
        // Calculate new balances after restoration
        $newVlBalance = $currentBalances['vl'];
        $newSlBalance = $currentBalances['sl'];
        
        // Apply credit restoration
        if ($leaveType === 'vl') {
            $newVlBalance = $newVlBalance + $workingDays;
        } elseif ($leaveType === 'sl') {
            $newSlBalance = $newSlBalance + $workingDays;
        } elseif ($leaveType === 'fl') {
            // Force Leave also restores VL
            $newVlBalance = $newVlBalance + $workingDays;
        }

        // For non-VL/SL leave types, restore the customer balance
        if (!in_array($leaveType, ['vl', 'sl'])) {
            $this->restoreLeaveBalance($customer, $leaveType, $workingDays);
        }

        // Create a new leave application record for the cancellation
        $cancellationApplication = LeaveApplication::create([
            'customer_id' => $customer->id,
            'leave_type' => $cancellationData['leave_type'],
            'leave_details' => 'CANCELLED - Credits Restored',
            'working_days' => -$workingDays, // Negative value to indicate credit restoration
            'inclusive_date_start' => $cancellationData['inclusive_date_start'],
            'inclusive_date_end' => $cancellationData['inclusive_date_end'],
            'date_filed' => $cancellationDate,
            'commutation' => null,
            'is_cancellation' => true, // Mark as cancellation entry
            'current_vl' => $newVlBalance,
            'current_sl' => $newSlBalance,
        ]);

        return $cancellationApplication;
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
}