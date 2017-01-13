<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    protected $fillable = [
        'name',
        'lat',
        'lng',
    ];

    public function connectors()
    {
       return $this->hasMany('App\Connector');
    }

    public function history()
    {
        return $this->hasManyThrough('App\History', 'App\Connector', 'charger_id', 'trackable_id')
		    ->where('trackable_type','App\Connector');
    }

    public function provider()
    {
        return $this->belongsTo('App\Provider');
    }
}
