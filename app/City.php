<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'state_id',
        'status',
        'latitude',
        'longitude',
        'created_at',
        'updated_at'
    ];

    public function state()
    {
        return $this->belongsTo('App\State');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'city_user')->withPivot('visited', 'pinned');
    }
}
