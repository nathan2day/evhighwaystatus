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


    /**
     * @return \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function connectors()
    {
       return $this->hasMany('App\Connector');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Eloquent\Relations\HasManyThrough | History
     */
    public function history()
    {
        return $this->hasManyThrough('App\History', 'App\Connector', 'charger_id', 'trackable_id')
                    ->where('trackable_type','App\Connector');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Eloquent\Relations\BelongsTo | Provider
     */
    public function provider()
    {
        return $this->belongsTo('App\Provider');
    }
}
