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


Route::get('/', 'WheelController@round');
