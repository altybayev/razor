<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiceDuelLogger extends Model
{
    protected $table = "dice_duel_logger";

    protected $fillable = [
    	'played_at',
    	'logged_at',
    	'match_id',
    	'dice_1',
    	'dice_2'
    ];

    protected $dates = ['logged_at'];
}
