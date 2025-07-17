<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable {
	use Notifiable;

	use SoftDeletes;

	protected $table = 'users';

    protected $dates = ['deleted_at'];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name','email','password','role','office_id','position_id','district','employee_id','image','creator_id', 'lastprmtn_date', 'origappnt_date'];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [ 
		'password', 'remember_token',
	];

	public function office()
    {
        return $this->belongsTo(Office::class);
    }
	public function position()
    {
        return $this->belongsTo(Position::class);
    }

}
