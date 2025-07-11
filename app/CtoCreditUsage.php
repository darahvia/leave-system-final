<?php
namespace App; // Namespace confirmed as 'App'


use Illuminate\Database\Eloquent\Model;
use App\CtoApplication; // Explicitly import CtoApplication model


class CtoCreditUsage extends Model
{
    protected $table = 'cto_credit_usages'; // Verify your actual table name, e.g., 'cto_credit_usages'


    protected $fillable = [
        'cto_activity_id', // Links to CtoApplication where is_activity = true
        'cto_absence_id',  // Lainks to CtoApplication where is_activity = false
        'days_used',       // Amount of credit used from the activity for this absence (in hours)
    ];


    protected $casts = [
        'days_used' => 'decimal:2',
    ];


    // Relationships
    public function ctoActivity()
    {
        return $this->belongsTo(CtoApplication::class, 'cto_activity_id'); // Points to App\CtoApplication
    }


    public function ctoAbsence()
    {
        return $this->belongsTo(CtoApplication::class, 'cto_absence_id'); // Points to App\CtoApplication
    }


    public function customer()
    {
        return $this->hasOneThrough(Customer::class, CtoApplication::class, 'id', 'id', 'cto_activity_id', 'customer_id');
    }


    // For a usage (is_activity = false): all SOs it was deducted from
public function consumedActivities()
{
    return $this->hasMany(CtoCreditUsage::class, 'cto_absence_id');
}


// For an SO (is_activity = true): all usages that deducted from it
public function creditUsages()
{
    return $this->hasMany(CtoCreditUsage::class, 'cto_activity_id');
}


// Optional: Accessor for remaining credits
public function getRemainingCreditsAttribute()
{
    if (!$this->is_activity) return 0;
    $used = $this->creditUsages()->sum('days_used');
    return (float)($this->credits_earned - $used);
}


}


