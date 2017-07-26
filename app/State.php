<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'name',
        'abbreviation',
        'date_added',
        'date_time_added',
        'last_updated'
    ];

    public function cities()
    {
        return $this->hasMany('App\City');
    }
}
