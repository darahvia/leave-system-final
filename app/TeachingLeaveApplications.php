<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeachingLeaveApplications extends Model
{

    protected $table = 'teaching_leave_applications';

    protected $fillable = [
        'customer_id',
        'leave_incurred_date',
        'leave_incurred_days',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
