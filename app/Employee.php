<?php
namespace App; // Changed namespace from App\Models to App

// Removed 'use Illuminate\Database\Eloquent\Factories\HasFactory;'
use Illuminate\Database\Eloquent\Model;
use App\LeaveApplication; // Assuming LeaveApplication is also in namespace App
use App\CtoApplication;   // Assuming CtoApplication is also in namespace App


class Employee extends Model
{
    // Removed 'use HasFactory;' as it's not supported in older Laravel versions.


    protected $fillable = [
        'surname', 'given_name', 'middle_name', 'division', 'designation', 'original_appointment',
        'vl', 'sl', 'spl', 'fl', 'solo_parent', 'ml', 'pl',
        'ra9710', 'rl', 'sel', 'study_leave', 'vawc', 'adopt', // Added 'vawc'
        'balance_forwarded_vl', 'balance_forwarded_sl',
        'salary'
    ];


    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }


    public function getCurrentLeaveBalance($leaveType)
    {
        $lastApplication = $this->leaveApplications()->latest()->first();

        switch (strtolower($leaveType)) {
            case 'vl':
                return $lastApplication ? $lastApplication->current_vl : ($this->balance_forwarded_vl ?? 0);
            case 'sl':
                return $lastApplication ? $lastApplication->current_sl : ($this->balance_forwarded_sl ?? 0);
            case 'spl':
                return $this->spl ?? 0;
            case 'fl':
                return $this->fl ?? 0;
            case 'solo_parent':
                return $this->solo_parent ?? 0;
            case 'ml':
                return $this->ml ?? 0;
            case 'pl':
                return $this->pl ?? 0;
            case 'ra9710':
                return $this->ra9710 ?? 0;
            case 'rl':
                return $this->rl ?? 0;
            case 'sel':
                return $this->sel ?? 0;
            case 'study_leave':
                return $this->study_leave ?? 0;
            case 'vawc': // Added vawc case
                return $this->vawc ?? 0;
            case 'adopt':
                return $this->adopt ?? 0; // Ensure null coalescing for adopt as well
            default:
                return 0;
        }
    }


    /**
     * Deduct leave days from the appropriate leave type
     */
    public function deductLeave($leaveType, $days)
    {
        switch (strtolower($leaveType)) {
            case 'vl':
                $this->vl = max(0, ($this->vl ?? 0) - $days);
                break;
            case 'sl':
                $this->sl = max(0, ($this->sl ?? 0) - $days);
                break;
            case 'spl':
                $this->spl = max(0, ($this->spl ?? 0) - $days);
                break;
            case 'fl':
                $this->fl = max(0, ($this->fl ?? 0) - $days);
                break;
            case 'solo_parent':
                $this->solo_parent = max(0, ($this->solo_parent ?? 0) - $days);
                break;
            case 'ml':
                $this->ml = max(0, ($this->ml ?? 0) - $days);
                break;
            case 'pl':
                $this->pl = max(0, ($this->pl ?? 0) - $days);
                break;
            case 'ra9710':
                $this->ra9710 = max(0, ($this->ra9710 ?? 0) - $days);
                break;
            case 'rl':
                $this->rl = max(0, ($this->rl ?? 0) - $days);
                break;
            case 'sel':
                $this->sel = max(0, ($this->sel ?? 0) - $days);
                break;
            case 'study_leave':
                $this->study_leave = max(0, ($this->study_leave ?? 0) - $days);
                break;
            case 'vawc': // Added vawc case
                $this->vawc = max(0, ($this->vawc ?? 0) - $days);
                break;
            case 'adopt':
                $this->adopt = max(0, ($this->adopt ?? 0) - $days);
                break;
            default:
                throw new \Exception("Invalid leave type: {$leaveType}");
        }
        
        $this->save();
    }


    /**
     * Add leave credits to the appropriate leave type
     */
    public function addLeaveCredits($leaveType, $days)
    {
        switch (strtolower($leaveType)) {
            case 'vl':
                $this->vl = ($this->vl ?? 0) + $days;
                break;
            case 'sl':
                $this->sl = ($this->sl ?? 0) + $days;
                break;
            case 'spl':
                $this->spl = ($this->spl ?? 0) + $days;
                break;
            case 'fl':
                $this->fl = ($this->fl ?? 0) + $days;
                break;
            case 'solo_parent':
                $this->solo_parent = ($this->solo_parent ?? 0) + $days;
                break;
            case 'ml':
                $this->ml = ($this->ml ?? 0) + $days;
                break;
            case 'pl':
                $this->pl = ($this->pl ?? 0) + $days;
                break;
            case 'ra9710':
                $this->ra9710 = ($this->ra9710 ?? 0) + $days;
                break;
            case 'rl':
                $this->rl = ($this->rl ?? 0) + $days;
                break;
            case 'sel':
                $this->sel = ($this->sel ?? 0) + $days;
                break;
            case 'study_leave':
                $this->study_leave = ($this->study_leave ?? 0) + $days;
                break;
            case 'vawc': // Added vawc case
                $this->vawc = ($this->vawc ?? 0) + $days;
                break;
            case 'adopt':
                $this->adopt = ($this->adopt ?? 0) + $days;
                break;
            default:
                throw new \Exception("Invalid leave type: {$leaveType}");
        }
        
        $this->save();
    }


    /**
     * Relationship with CTO Applications
     */
    public function ctoApplications()
    {
        return $this->hasMany(CtoApplication::class);
    }


    /**
     * Get current CTO balance
     */
    public function getCurrentCtoBalance()
    {
        $latestRecord = $this->ctoApplications()
            ->orderBy('date_of_activity_start', 'desc')
            ->orderBy('date_of_absence_start', 'desc')
            ->orderBy('id', 'desc')
            ->first();


        return $latestRecord ? $latestRecord->balance : 0;
    }
}
