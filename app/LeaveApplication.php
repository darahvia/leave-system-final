<?php
namespace App; // Assuming your models are now directly in the 'app' directory

// Removed 'use Illuminate\Database\Eloquent\Factories\HasFactory;'
use Illuminate\Database\Eloquent\Model;


class LeaveApplication extends Model
{
    // Removed 'use HasFactory;' from here as it's not supported in older Laravel versions.

    protected $fillable = [
        'employee_id', 'leave_type', 'leave_details', 'working_days',
        'inclusive_date_start', 'inclusive_date_end', 'date_filed',
        'date_incurred', 'commutation', 'current_vl', 'current_sl',
        'is_credit_earned', 'earned_date', 'is_cancellation'
    ];

    protected $casts = [
        'inclusive_date_start' => 'date',
        'inclusive_date_end' => 'date',
        'date_filed' => 'date',
        'date_incurred' => 'date',
        'earned_date' => 'date',
        'is_credit_earned' => 'boolean',
        'is_cancellation' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class); // Ensure this refers to App\Employee
    }
}
