<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiceDuelStats extends Model
{
    protected $table = 'dice_duel_stats';

    protected $fillable = [
    	'red_equals_blue',
        'red_plus_blue_greater_ten',
        'red_plus_blue_less_four',
        'red_plus_blue_equals_seven',
        'red_greater_blue',
        'blue_greated_red',
        'red_plus_blue_equals_six',
        'red_plus_blue_equals_eight',
    ];

    public static function log($dices) {
    	// dd([$dices[0], $dices[1]]);

    	$stats = self::first();
    	// dd($stats);

    	// red_equals_blue
    	if ($dices[0] == $dices[1]) {
    		$stats->red_equals_blue = 0;
    	} else {
    		$stats->red_equals_blue += 1;
    	}

    	// red_plus_blue_less_four
    	if ($dices[0] + $dices[1] <= 4) {
    		$stats->red_plus_blue_less_four = 0;
    	} else {
    		$stats->red_plus_blue_less_four += 1;
    	}

        // red_plus_blue_greater_ten
    	if ($dices[0] + $dices[1] >= 10) {
    		$stats->red_plus_blue_greater_ten = 0;
    	} else {
    		$stats->red_plus_blue_greater_ten += 1;
    	}

        // red_plus_blue_equals_seven
    	if ($dices[0] + $dices[1] == 7) {
    		$stats->red_plus_blue_equals_seven = 0;
    	} else {
    		$stats->red_plus_blue_equals_seven += 1;
    	}

        // red_plus_blue_equals_six
        if ($dices[0] + $dices[1] == 6) { 
        	$stats->red_plus_blue_equals_six = 0;
        } else {
        	$stats->red_plus_blue_equals_six += 1;
        }

        // red_plus_blue_equals_eight
        if ($dices[0] + $dices[1] == 8) {
        	$stats->red_plus_blue_equals_eight = 0;
        } else {
        	$stats->red_plus_blue_equals_eight += 1;
        }

        // red_greater_blue
        if ($dices[0] > $dices[1]) {
        	$stats->red_greater_blue = 0;
        } else {
        	$stats->red_greater_blue += 1;
        }

        // TODO: error in column name ;)
        // blue_greated_red
        if ($dices[0] < $dices[1]) {
        	$stats->blue_greated_red = 0;
        } else {
        	$stats->blue_greated_red += 1;
        }

        $stats->save();
    }

    private static function updateStats($case, $stats) 
    {
    	$stats->red_equals_blue = ($case == 'red_equals_blue') ? 0 : $stats->red_equals_blue += 1;
		$stats->red_plus_blue_greater_ten = ($case == 'red_plus_blue_greater_ten') ? 0 : $stats->red_plus_blue_greater_ten += 1;
		$stats->red_plus_blue_less_four = ($case == 'red_plus_blue_less_four') ? 0 : $stats->red_plus_blue_less_four += 1;
		$stats->red_plus_blue_equals_seven = ($case == 'red_plus_blue_equals_seven') ? 0 : $stats->red_plus_blue_equals_seven += 1;
		$stats->red_greater_blue = ($case == 'red_greater_blue') ? 0 : $stats->red_greater_blue += 1;
		$stats->blue_greated_red = ($case == 'blue_greated_red') ? 0 : $stats->blue_greated_red += 1;
		$stats->red_plus_blue_equals_six = ($case == 'red_plus_blue_equals_six') ? 0 : $stats->red_plus_blue_equals_six += 1;
		$stats->red_plus_blue_equals_eight = ($case == 'red_plus_blue_equals_eight') ? 0 : $stats->red_plus_blue_equals_eight += 1;

    	$stats->save();
    }
}
