<?php

namespace App\Http\Controllers;

use App\DiceDuelLogger;
use App\DiceDuelStats;
use App\Mail\TimeToBet;
use App\WheelLogger;
use App\WheelStats;
use App\WheelStatsNumbers;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DiceDuelController extends Controller
{
	protected $stavkaLabels = [
		'red_equals_blue' => 'КР = СН',
        'red_plus_blue_greater_ten' => 'КР + СН >= 9.5',
        'red_plus_blue_less_four' => 'КР + СН <= 4.5',
        'red_plus_blue_equals_seven' => 'КР + СН = 7',
        'red_greater_blue' => 'КР > СН',
        'blue_greated_red' => 'СН > КР',
        'red_plus_blue_equals_six' => 'КР + СН = 6',
        'red_plus_blue_equals_eight' => 'КР + СН = 8',
	];

	/*
 	 *  7 - wheels
 	 * 10 - dice duel
	 */

    public function round(Request $request)
	{
		$today = Carbon::now()->format('Y-m-d');
	    $url = 'https://olimp.betgamestv.eu/ext/game/results/olimp/' . $today . '/10';
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
			// dice-1, dice-2
			else if ($index == 2) {
				// dd($node->html());

				$dices = [];
				$node->filter('.lottery-item')->each(function($diceNode) use (&$dices) {
					$dices[] = intval(preg_replace('/\D/', '', ($diceNode->attr('class'))));
				});

				$results['dices'] = $dices;

				// dd($results);
			} else if ($index == 3) {
				
			}

			$index += 1;
		});

		// dd($results);

		// log into db
		$doLog = true;

		try {
			DiceDuelLogger::insert([
				'played_at' => $today . " " . $results['played_at'],
				'logged_at' => Carbon::now(),
				'match_id' => 1,
				'dice_1' => $results['dices'][0],
				'dice_2' => $results['dices'][1]
			]);
		} catch (\Exception $e) {
			// print 'Ошибка: ' . $e->getMessage();
			echo 'Already logged<hr>';
			$doLog = false;
		}

		// count occurencies
		if ($doLog) {
			DiceDuelStats::log($results['dices']);

			// check and notify
			$this->notify($results, $doLog);
		}

		// TODO: add users
		// TODO: add cabinet, where user can edit email / HMTDOC param 

		// return $results;
	}

	private function notify($results, $doLog) {
		$stats = DiceDuelStats::first()->toArray();
		// dd($stats);

		// TODO: make for each user own settings!
		$users = [
			[
				'email' => 'altybaev@bk.ru',
				'series' => [
					'red_equals_blue' => 30,
			        'red_plus_blue_greater_ten' => 30,
			        'red_plus_blue_less_four' => 30,
			        'red_plus_blue_equals_seven' => 30,
			        'red_greater_blue' => 10,
			        'blue_greated_red' => 10,
			        'red_plus_blue_equals_six' => 30,
			        'red_plus_blue_equals_eight' => 30,
				],
			],
			[
				'email' => '1000-victory@mail.ru',
				'series' => [
					'red_equals_blue' => 35,
			        'red_plus_blue_greater_ten' => 35,
			        'red_plus_blue_less_four' => 35,
			        'red_plus_blue_equals_seven' => 35,
			        'red_greater_blue' => 15,
			        'blue_greated_red' => 15,
			        'red_plus_blue_equals_six' => 30,
			        'red_plus_blue_equals_eight' => 30,
				],
			],
			// [
			// 	'email' => 'ravonrr@mail.ru',
			// 	'series' => [
			// 		'red_equals_blue' => 30,
			//         'red_plus_blue_greater_ten' => 30,
			//         'red_plus_blue_less_four' => 30,
			//         'red_plus_blue_equals_seven' => 30,
			//         'red_greater_blue' => 10,
			//         'blue_greated_red' => 10,
			//         'red_plus_blue_equals_six' => 30,
			//         'red_plus_blue_equals_eight' => 30,
			// 	],
			// ],
		];


		// for each user check and notify
		foreach ($users as $user) {
			foreach ($user['series'] as $stavka => $requiredQntt) {
				echo 'Checking for ' . $stavka . ' - ' . $stats[$stavka] . '/' . $requiredQntt . '<br>';

				if ($stats[$stavka] >= $requiredQntt) {
					// notify for this stavka
					// echo 'NOTIFY ' . $user['email'] . ' ABOUT - ' . $this->stavkaLabels[$stavka] . '<br>';
					Mail::to($user['email'])->send(new TimeToBet('diceduel', $this->stavkaLabels[$stavka], $stats[$stavka]));
				}
			}

			echo '<br><br>';
		}
	}
}
