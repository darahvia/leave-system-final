<?php
namespace App; // Assuming your models are now directly in the 'app' directory

// Removed 'use Illuminate\Database\Eloquent\Factories\HasFactory;'
use Illuminate\Database\Eloquent\Model;


class CtoCreditUsage extends Model
{
    // Removed 'use HasFactory;' from here as it's not supported in older Laravel versions.


    protected $table = 'cto_credit_usages';


    protected $fillable = [
        'cto_activity_id',
        'cto_absence_id',
        'days_used',
    ];


    protected $casts = [
        'days_used' => 'decimal:2',
    ];


    public function ctoActivity()
    {
        return $this->belongsTo(CtoApplication::class, 'cto_activity_id'); // Ensure this refers to App\CtoApplication
    }


    public function ctoAbsence()
    {
        return $this->belongsTo(CtoApplication::class, 'cto_absence_id'); // Ensure this refers to App\CtoApplication
    }
}
