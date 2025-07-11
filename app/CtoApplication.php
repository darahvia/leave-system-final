<?php


namespace App;


use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class CtoApplication extends Model
{
    protected $table = 'cto_applications';


    protected $fillable = [
        'customer_id',
        'special_order',
        'date_of_activity_start',
        'date_of_activity_end',
        'activity',
        'credits_earned',
        'date_of_absence_start',
        'date_of_absence_end',
        'no_of_days',
        'balance',
        'current_balance',
        'is_activity',
        'cto_details',
        'date_filed',
    ];


    protected $casts = [
        'date_of_activity_start' => 'date',
        'date_of_activity_end' => 'date',
        'date_of_absence_start' => 'date',
        'date_of_absence_end' => 'date',
        'date_filed' => 'date',
        'credits_earned' => 'decimal:2',
        'no_of_days' => 'decimal:2',
        'balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_activity' => 'boolean',
    ];


    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public function creditUsages()
    {
        return $this->hasMany(CtoCreditUsage::class, 'cto_activity_id');
    }


    public function consumedActivities()
    {
        return $this->hasMany(CtoCreditUsage::class, 'cto_absence_id');
    }


    // Accessor for formatted activity date
    public function getFormattedActivityDateAttribute()
    {
        if (!$this->date_of_activity_start) return '';
        $start = Carbon::parse($this->date_of_activity_start)->format('n/j/Y');


        if (
            $this->date_of_activity_end &&
            $this->date_of_activity_start->toDateString() !== $this->date_of_activity_end->toDateString()
        ) {
            $end = Carbon::parse($this->date_of_activity_end)->format('n/j/Y');
            return $start . ' - ' . $end;
        }


        return $start;
    }


    // Accessor for formatted absence date
    public function getFormattedAbsenceDateAttribute()
    {
        if (!$this->date_of_absence_start) return '';
        $start = Carbon::parse($this->date_of_absence_start)->format('n/j/Y');


        if (
            $this->date_of_absence_end &&
            $this->date_of_absence_start->toDateString() !== $this->date_of_absence_end->toDateString()
        ) {
            $end = Carbon::parse($this->date_of_absence_end)->format('n/j/Y');
            return $start . ' - ' . $end;
        }


        return $start;
    }


    // Used for sorting earned and used records together
    public function getEffectiveDateAttribute()
    {
        if ($this->is_activity) {
            return $this->date_of_activity_start ?? $this->date_filed;
        }
        return $this->date_of_absence_start ?? $this->date_filed;
    }


    // ✅ Remaining credits accessor (used for SO deduction logic)
    public function getRemainingCreditsAttribute()
    {
        if (!$this->is_activity) {
            return 0.0; // Only activities have remaining credits
        }


        $totalUsed = $this->creditUsages()->sum('days_used');
        return (float)($this->credits_earned - $totalUsed);
    }


    // Check if activity is expired at a given date
    public function isExpiredAt(Carbon $checkDate)
    {
        if (!$this->is_activity || !$this->date_of_activity_end) {
            return false;
        }


        $expirationDate = $this->date_of_activity_end->copy()->addYear();
        return $checkDate->greaterThanOrEqualTo($expirationDate);
    }


    // Check if activity is expired now
    public function isExpired()
    {
        return $this->isExpiredAt(Carbon::now());
    }
}


