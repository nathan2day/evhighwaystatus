<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    protected $fillable = [
        'lat',
        'lng',
    ];

    public function connectors()
    {
       return $this->belongsToMany('App\Connector')->withPivot('id','status')->withTimestamps();
    }

    public function provider()
    {
        return $this->belongsTo('App\Provider');
    }
}
