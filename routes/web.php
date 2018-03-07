<?php

use App\Mail\BetIsRequired;
use App\WheelLogger;
use App\WheelStats;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', function() {

	$email = 'altybaev@bk.ru';
	Mail::to($email)->send(new BetIsRequired('red', 10));

});
	


Route::get('/', function () {
	// TODO: when new day starts - clear stats

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
	$doLog = true;

	try {
		WheelLogger::insert([
			'played_at' => $today . " " . $results['played_at'],
			'logged_at' => Carbon::now(),
			'match_id' => 1,
			'number' => $results['number'],
			'color' => $results['color']
		]);
	} catch (\Exception $e) {
		print $e->getMessage() . "<br>";
		$doLog = false;
	}

	// count occurencies
	if ($doLog) WheelStats::log($results['color']);

	// get how many times didn't occur a color!
	$stats = WheelStats::first();
	// dd($stats);

	// TODO: if [param] times -> notify by email!
	$email = 'altybaev@bk.ru';
	$param = 10;
	$target = '';
	$qntt = 0;
	$sendEmail = false;

	// red
	if ($stats->red >= $param) {
		$target = 'red';
		$qntt = $stats->red;
		$sendEmail = true;
	} else if ($stats->grey >= $param) {
		$target = 'grey';
		$qntt = $stats->grey;
		$sendEmail = true;
	} else if ($stats->black >= $param) {
		$target = 'black';
		$qntt = $stats->black;
		$sendEmail = true;
	}

	if ($sendEmail) { 
		Mail::to($email)->send(new BetIsRequired($target, $qntt));
	}


	// TODO: add users

	// TODO: add cabinet, where user can edit email / HMTDOC param 

	return $results;
	
});
