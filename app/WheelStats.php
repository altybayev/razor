<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WheelStats extends Model
{
    protected $table = 'wheel_stats';

    protected $fillable = [
    	'red',
    	'black',
    	'grey',
    	'white'
    ];

    public static function log($color) {

    	$stats = self::first();

    	switch ($color) {
    		case 'red':
    			$stats->red = 0;

    			$stats->black += 1;
    			$stats->grey += 1;
    			$stats->white += 1;

    			break;

    		case 'black':
    			$stats->black = 0;

    			$stats->red += 1;
    			$stats->grey += 1;
    			$stats->white += 1;

    			break;

    		case 'grey':
    			$stats->grey = 0;

    			$stats->black += 1;
    			$stats->red += 1;
    			$stats->white += 1;

    			break;

    		case 'white':
    			$stats->white = 0;

    			$stats->black += 1;
    			$stats->grey += 1;
    			$stats->red += 1;

    			break;
    		
    		default:
    			# code...
    			break;
    	}

    	$stats->save();

    }
}
