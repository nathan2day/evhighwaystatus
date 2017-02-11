<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    protected $fillable = [
        'typeid',
        'power',
        'status',
        'position',
        'quantity',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Eloquent\Relations\BelongsTo | Charger
     */
    public function charger()
    {
        return $this->belongsTo('App\Charger');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Eloquent\Relations\MorphMany | History
     */
    public function history()
    {
        return $this->morphMany('App\History','trackable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder | \Illuminate\Database\Eloquent\Relations\BelongsToMany | Type
     */
    public function type()
    {
        return $this->belongsToMany('App\Type');
    }
}
