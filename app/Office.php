<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Office extends Model

{
        protected $table = 'offices';

    protected $fillable = ['district','office'];

    protected $hidden = ['created_at', 'updated_at'];
} 
