<?php

namespace App;

use App\WheelLogger;
use App\WheelStats;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Database\Eloquent\Model;

class WheelLogger extends Model
{
	protected $table = "wheel_logs";

    protected $fillable = [
    	'played_at',
    	'logged_at',
    	'match_id',
    	'color',
    	'number'
    ];

    protected $dates = ['logged_at'];
}
