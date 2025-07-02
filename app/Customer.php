<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'nama', 'alamat', 'email', 'telepon', 'district', 'office_id', 'position_id',
        'customer_id', 'role', 'lastprmtn_date', 'origappnt_date', 'step_array', 'loyalty_array',         
        'vl', 'sl', 'spl', 'fl', 'solo_parent', 'ml', 'pl',
        'ra9710', 'rl', 'sel', 'study_leave', 'vawc', 'adopt',
        'balance_forwarded_vl', 'balance_forwarded_sl',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'step_array' => 'array',
        'loyalty_array' => 'array',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
    

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }
   
    public function getCurrentLeaveBalance($leaveType)
    {
        $lastApplication = $this->leaveApplications()->latest()->first();

        switch (strtolower($leaveType)) {
            case 'vl':
                return $lastApplication ? $lastApplication->current_vl : $this->balance_forwarded_vl;
            case 'sl':
                return $lastApplication ? $lastApplication->current_sl : $this->balance_forwarded_sl;
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
            case 'vawc':
                return $this->vawc ?? 0;
            case 'adopt':
                return $this->adopt;
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
            case 'vawc':
                $this->vawc = max(0, ($this->vawc ?? 0) - $days);
                break;
            case 'adopt':
                $this->adopt = max(0, $this->adopt - $days);
                break;
        }
       
        $this->save();
    }

    /**
     * Add leave credits to the appropriate leave type
     */
    public function addLeaveCredits($leaveType, $days)
    {
        switch (strtolower($leaveType)) {
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
            case 'vawc':
                $this->vawc = ($this->vawc ?? 0) + $days;
                break;
            case 'adopt':
                $this->adopt = ($this->adopt ?? 0) + $days;
                break;
        }
       
        $this->save();
    }

}