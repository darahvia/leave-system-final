<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['position'];

    protected $hidden = ['created_at', 'updated_at'];
} 
