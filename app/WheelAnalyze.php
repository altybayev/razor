<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WheelAnalyze extends Model
{
    protected $table = "wheel_analyze";

    protected $fillable = [
    	'played_at',
    	'logged_at',
    	'match_id',
    	'color',
    	'number'
    ];

    protected $dates = ['logged_at'];
}
