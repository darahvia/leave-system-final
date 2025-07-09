<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeachingLeaveApplications extends Model
{

    protected $table = 'teaching_leave_applications';

    protected $fillable = [
        'customer_id',
        'leave_start_date',
        'leave_end_date',
        'working_days',
        'is_leavewopay',
    ];


    protected $casts = [
    'leave_start_date' => 'date',
    'leave_end_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
