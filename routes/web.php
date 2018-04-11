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

Route::get('/test/{startingBet}/{ratio}', function($startingBet, $ratio) {

	$bet = $startingBet;
	$total = 0;

	$bets = [100, 150, 225, 350, 525, 800, 1200, 1800, 2700, 4050, 6075, 9150];
	$bets175 = [100, 175, 310, 550, 1000, 1750, 3100, 5500, 10000, 17500, 31000, 55000, 100000, 175000, 310000];
	$bets200 = [100, 200, 400, 800, 1600, 3200, 6400, 12800, 25600, 51200, 102400, 204800];

	for ($i = 0 ; $i < count($bets); $i++) {
		$bet = $bets[$i];
		$total += $bet;
		$win = $bet * 3.05;
		$earn = $win - $total;
		$perday = $earn * 5;
		$permonth = $perday * 30;

		$betstr = number_format($bet, 2, '.', ' ');
		$totalstr = number_format($total, 2, '.', ' ');
		$winstr = number_format($win, 2, '.', ' ');
		$earnstr = number_format($earn, 2, '.', ' ');
		$perdaystr = number_format($perday, 2, '.', ' ');
		$permonthstr = number_format($permonth, 2, '.', ' ');
		echo "${i}: ${betstr} [${totalstr}] - [${winstr} / ${earnstr}] - [${perdaystr} / ${permonthstr}]<br>";
	}

	echo "<hr>";
	$total = 0;

	for ($i = 0 ; $i < count($bets175); $i++) {
		$bet = $bets175[$i];
		$total += $bet;
		$win = $bet * 3.05;
		$earn = $win - $total;
		$perday = $earn * 5;
		$permonth = $perday * 30;

		$betstr = number_format($bet, 2, '.', ' ');
		$totalstr = number_format($total, 2, '.', ' ');
		$winstr = number_format($win, 2, '.', ' ');
		$earnstr = number_format($earn, 2, '.', ' ');
		$perdaystr = number_format($perday, 2, '.', ' ');
		$permonthstr = number_format($permonth, 2, '.', ' ');
		echo "${i}: ${betstr} [${totalstr}] - [${winstr} / ${earnstr}] - [${perdaystr} / ${permonthstr}]<br>";
	}

	echo "<hr>";
	$total = 0;

	for ($i = 0 ; $i < count($bets200); $i++) {
		$bet = $bets200[$i];
		$total += $bet;
		$win = $bet * 3.05;
		$earn = $win - $total;
		$perday = $earn * 5;
		$permonth = $perday * 30;

		$betstr = number_format($bet, 2, '.', ' ');
		$totalstr = number_format($total, 2, '.', ' ');
		$winstr = number_format($win, 2, '.', ' ');
		$earnstr = number_format($earn, 2, '.', ' ');
		$perdaystr = number_format($perday, 2, '.', ' ');
		$permonthstr = number_format($permonth, 2, '.', ' ');
		echo "${i}: ${betstr} [${totalstr}] - [${winstr} / ${earnstr}] - [${perdaystr} / ${permonthstr}]<br>";
	}

});


Route::get('/test/email', function() {
	Mail::to('altybaev@bk.ru')->send(new BetIsRequired('red', 11));
});
	

Route::group(['prefix' => 'wheel'], function() {
	Route::get('/analyze/{date}/{limit}', 'WheelController@analyze');
	Route::get('/collect/{date}/{limit}', 'WheelController@collect');
});


Route::get('/', function () {
	// TODO: when new day starts - clear stats

	$today = Carbon::now()->format('Y-m-d');
    $url = 'https://olimp.betgamestv.eu/ext/game/results/olimp/' . $today . '/7';
	$client = new Client();
	$crawler = $client->request('GET', $url);
	$index = 0;
	$results = [];

	$crawler->filter('table.table-results tr')->filter('td')->each(function($node) use (&$index, &$results) {
		$input = $node->text();
		$input = htmlentities($input, null, 'utf-8');
		$input = str_replace("&nbsp;", '', $input);
		$input = str_replace("\n", '', $input);
		$input = str_replace("\r", '', $input);
		$input = str_replace(' ', '', $input);

		// data
		if ($index == 0) 
		{
			// dd($input);

			$parts = explode("-", $input);
			$results['played_at'] = $parts[0];
		} 
		// game
		else if ($index == 1) 
		{
			// dd($input);
		} 
		// number, color, played_at
		else if ($index == 2) {
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

			// dd($results);
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
		print 'Ошибка: ' . $e->getMessage();
		$doLog = false;
	}

	// count occurencies
	if ($doLog) WheelStats::log($results['color']);

	// get how many times didn't occur a color!
	$stats = WheelStats::first();
	// dd($stats);

	// settings
	$emails = ['altybaev@bk.ru', 'ravonrr@mail.ru'];
	$vitek = '1000-victory@mail.ru';

	$target = '';
	$qntt = 0;
	$sendEmail = false;
	$sendEmailVitek = false;

	// TODO: if [param] times -> notify by email!
	$param = 10;
	$paramVitek = 15;

	// others
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

	// vitek
	if ($stats->red >= $paramVitek) {
		$target = 'red';
		$qntt = $stats->red;
		$sendEmailVitek = true;
	} else if ($stats->grey >= $paramVitek) {
		$target = 'grey';
		$qntt = $stats->grey;
		$sendEmailVitek = true;
	} else if ($stats->black >= $paramVitek) {
		$target = 'black';
		$qntt = $stats->black;
		$sendEmailVitek = true;
	}

	// notify others
	if ($sendEmail && $doLog) { 
		foreach ($emails as $key => $email) {
			Mail::to($email)->send(new BetIsRequired($target, $qntt));
		}
	}

	// notify Vitek
	if ($sendEmailVitek && $doLog) {
		Mail::to($vitek)->send(new BetIsRequired($target, $qntt));
	}

	// TODO: add users

	// TODO: add cabinet, where user can edit email / HMTDOC param 

	return $results;
	
});
