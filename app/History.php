<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    $fillable = [
    	'old',
    	'new',
    ];
}
