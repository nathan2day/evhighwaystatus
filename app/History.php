<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
    	'old',
    	'new',
    ];

    public function trackable()
    {
    	return $this->morphTo();
    }

}
