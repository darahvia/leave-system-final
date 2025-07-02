<?php
namespace App; // Namespace confirmed as 'App'

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CtoApplication extends Model
{

    protected $table = 'cto_applications'; // Verify your actual table name if different

    protected $fillable = [
        'customer_id', // CHANGED: From 'customer_id' to 'customer_id'
        'special_order',
        'date_of_activity_start',
        'date_of_activity_end',
        'activity',
        'credits_earned', // For earned activities (hours earned)
        'date_of_absence_start',
        'date_of_absence_end',
        'no_of_days', // For used credits (absences) - stores hours applied
        'balance', // If you have this column for tracking individual record balance
        'current_balance', // ADDED: Field to store the running balance after this record is processed
        'is_activity', // Boolean: true for earned, false for used
        'cto_details', // ADDED: For remarks on CTO usage
        'date_filed', // ADDED: Date the CTO record was filed/applied
    ];

    protected $casts = [
        'date_of_activity_start' => 'date',
        'date_of_activity_end' => 'date',
        'date_of_absence_start' => 'date',
        'date_of_absence_end' => 'date',
        'date_filed' => 'date', // ADDED: Cast to date
        'credits_earned' => 'decimal:2',
        'no_of_days' => 'decimal:2', // Represents hours used/applied
        'balance' => 'decimal:2', // If this column exists
        'current_balance' => 'decimal:2', // ADDED: Cast the running balance to decimal
        'is_activity' => 'boolean',
    ];

    public function customer() // CHANGED: from customer() to customer()
    {
        return $this->belongsTo(Customer::class); // Points to App\Customer
    }

    public function creditUsages()
    {
        return $this->hasMany(CtoCreditUsage::class, 'cto_activity_id'); // Points to App\CtoCreditUsage
    }

    public function consumedActivities()
    {
        return $this->hasMany(CtoCreditUsage::class, 'cto_absence_id'); // Points to App\CtoCreditUsage
    }

    // Accessor for formatted activity date
    public function getFormattedActivityDateAttribute()
    {
        if (!$this->date_of_activity_start) {
            return '';
        }

        $start = Carbon::parse($this->date_of_activity_start)->format('n/j/Y');

        if ($this->date_of_activity_end && $this->date_of_activity_start->toDateString() != $this->date_of_activity_end->toDateString()) {
            $end = Carbon::parse($this->date_of_activity_end)->format('n/j/Y');
            return $start . ' - ' . $end;
        }

        return $start;
    }

    // Accessor for formatted absence date
    public function getFormattedAbsenceDateAttribute()
    {
        if (!$this->date_of_absence_start) {
            return '';
        }

        $start = Carbon::parse($this->date_of_absence_start)->format('n/j/Y');

        if ($this->date_of_absence_end && $this->date_of_absence_start->toDateString() != $this->date_of_absence_end->toDateString()) {
            $end = Carbon::parse($this->date_of_absence_end)->format('n/j/Y');
            return $start . ' - ' . $end;
        }

        return $start;
    }

    /**
     * Get the effective date for chronological sorting (activity start date or absence start date, or date_filed as fallback).
     */
    public function getEffectiveDateAttribute()
    {
        // Prioritize specific dates, then fall back to date_filed
        if ($this->is_activity) {
            return $this->date_of_activity_start ?? $this->date_filed;
        }
        return $this->date_of_absence_start ?? $this->date_filed;
    }

    /**
     * Calculate remaining credits for an activity.
     * This will be used by the FIFO deduction logic.
     */
    public function getRemainingCreditsAttribute()
    {
        if (!$this->is_activity) {
            return 0.0; // Only activities have remaining credits
        }
        // Sum up all usages linked to this activity
        $totalUsed = $this->creditUsages()->sum('days_used'); // 'days_used' in CtoCreditUsage stores hours
        return (float)($this->credits_earned - $totalUsed);
    }

    /**
     * Determine if an activity is expired based on a specific check date.
     * Credits expire 1 year AFTER the end date of the activity.
     * @param Carbon $checkDate The date against which to check expiration.
     */
    public function isExpiredAt(Carbon $checkDate)
    {
        if (!$this->is_activity || !$this->date_of_activity_end) {
            return false; // Only activities with an end date can expire this way
        }
        // Calculate the exact expiration date (1 year after activity end date)
        $expirationDate = $this->date_of_activity_end->copy()->addYear();

        // Return true if the checkDate is on or after the expirationDate
        return $checkDate->greaterThanOrEqualTo($expirationDate);
    }

    /**
     * Convenience method to check if the activity is expired as of the current date/time.
     */
    public function isExpired()
    {
        return $this->isExpiredAt(Carbon::now());
    }
}