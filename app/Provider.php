<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = [
        'name',
        'url',
    ];

    public function chargers()
    {
        return $this->hasMany('App\Charger');
    }
}
