<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    protected $fillable = [
        'name',
        'power',
        'status',
        'position',
    ];

    public function charger()
    {
        return $this->belongsTo('App\Charger');
    }

    public function history()
    {
        return $this->morphMany('App\History','trackable');
    }
}
