<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WheelStatsNumbers extends Model
{
    protected $table = 'wheel_stats_numbers';

    protected $fillable = [
    	'one_six',
    	'seven_twelve',
    	'thirteen_eighteen',
    ];

    public static function log($number) {

    	$stats = self::first();

    	switch ($number) {
    		case 1:
    		case 2:
    		case 3:
    		case 4:
    		case 5:
    		case 6:
    			$stats->one_six = 0;

    			$stats->seven_twelve += 1;
    			$stats->thirteen_eighteen += 1;

    			break;

    		case 7:
    		case 8:
    		case 9:
    		case 10:
    		case 11:
    		case 12:
    			$stats->seven_twelve = 0;

    			$stats->one_six += 1;
    			$stats->thirteen_eighteen += 1;

    			break;

    		case 13:
    		case 14:
    		case 15:
    		case 16:
    		case 17:
    		case 18:
    			$stats->thirteen_eighteen = 0;

    			$stats->one_six += 1;
    			$stats->seven_twelve += 1;

    			break;

    		default:
    			# code...
    			break;
    	}

    	$stats->save();

    }
}
