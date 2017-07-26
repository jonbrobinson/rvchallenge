<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'created_at',
        'updated_at',
        'access_token'
    ];

    public $incrementing = false;

    public function cities()
    {
        return $this->belongsToMany('App\City', 'city_user')->withPivot('visited', 'pinned');
    }
}
