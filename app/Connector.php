<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    protected $fillable = [
        'name',
        'power',
    ];

    public function charger()
    {
        // TODO limit to single charger a particular instance is linked to

        return $this->belongsToMany('App\Charger');
    }
}
