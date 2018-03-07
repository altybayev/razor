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


    public static function logEveryMinute() {
    	$today = Carbon::now()->format('Y-m-d');
	    $url = 'https://olimp.betgamestv.eu/ext/game/results/olimp/' . $today . '/7';
		$client = new Client();
		$crawler = $client->request('GET', $url);
		$index = 0;
		$results = [];

		$crawler->filter('table.table-results tr.lottery-items-cell')->first()->filter('td')->each(function($node) use (&$index, &$results) {
			$input = $node->text();
			$input = htmlentities($input, null, 'utf-8');
			$input = str_replace("&nbsp;", '', $input);
			$input = str_replace("\n", '', $input);
			$input = str_replace(' ', '', $input);

			// color
			if ($index == 0) {
				// TODO: get match id
				$parts = explode("-", $input);
				$results['played_at'] = $parts[0];
			} else if ($index == 1) {

			} else if ($index == 2) {
				$color = '';
				$class = $node->filter('span')->attr('class');

				if (mb_strpos($class, 'black')) {
					$color = 'black';
				} else if (mb_strpos($class, 'red')) {
					$color = 'red';
				} else if (mb_strpos($class, 'grey')) {
					$color = 'grey';
				} else if (mb_strpos($class, 'white')) {
					$color = 'white';
				} 

				$results['number'] = $input;
				$results['color'] = $color;
			} else if ($index == 3) {

			}

			$index += 1;
		});

		// log into db
		try {
			WheelLogger::insert([
				'played_at' => $today . " " . $results['played_at'],
				'logged_at' => Carbon::now(),
				'match_id' => 1,
				'number' => $results['number'],
				'color' => $results['color']
			]);
		} catch (\Exception $e) {
			//print "---";
			return null;
		}

		// count occurencies
		WheelStats::log($results['color']);

		// get how many times didn't occur a color!
		$stats = WheelStats::first();
		// dd($stats);

		// TODO: if [param] times -> notify by email!

		// TODO: add users

		// TODO: add cabinet, where user can edit email / HMTDOC param 

		// TODO: cronjob each 3 min

		return $results;
    }
}