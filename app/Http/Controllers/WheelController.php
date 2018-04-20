<?php

namespace App\Http\Controllers;

use App\Mail\BetIsRequired;
use App\WheelAnalyze;
use App\WheelLogger;
use App\WheelStats;
use App\WheelStatsNumbers;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class WheelController extends Controller
{
	public function round(Request $request)
	{
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
		if ($doLog) {
			WheelStats::log($results['color']);
			WheelStatsNumbers::log(intval($results['number']));
		}

		// check for color
		$this->notifyByColor($results, $doLog);

		// check for numbers
		$this->notifyByNumbers($results, $doLog);


		// TODO: add users
		// TODO: add cabinet, where user can edit email / HMTDOC param 

		return $results;
	}

	private function notifyByColor($results, $doLog) {
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
	}

	private function notifyByNumbers($results, $doLog) {
		// get how many times didn't occur a color!
		$stats = WheelStatsNumbers::first();
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
		if ($stats->one_six >= $param) {
			$target = '1-6';
			$qntt = $stats->one_six;
			$sendEmail = true;
		} else if ($stats->seven_twelve >= $param) {
			$target = '7-12';
			$qntt = $stats->seven_twelve;
			$sendEmail = true;
		} else if ($stats->thirteen_eighteen >= $param) {
			$target = '13-18';
			$qntt = $stats->thirteen_eighteen;
			$sendEmail = true;
		}

		// vitek
		if ($stats->one_six >= $paramVitek) {
			$target = '1-6';
			$qntt = $stats->one_six;
			$sendEmailVitek = true;
		} else if ($stats->seven_twelve >= $paramVitek) {
			$target = '7-12';
			$qntt = $stats->seven_twelve;
			$sendEmailVitek = true;
		} else if ($stats->thirteen_eighteen >= $paramVitek) {
			$target = '13-18';
			$qntt = $stats->thirteen_eighteen;
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
	}


	public function analyze(Request $request, $date, $limit) {
		$results = WheelAnalyze::where('played_at', 'like', $date . '%')->get();
		
		$occurencies = [
			'red' => 0, 'black' => 0, 'grey' => 0, 'white' => 0
		];

		$stats = [];
		// $games = [];

		for ($i = 1; $i < 100; $i++) { 
			$stats[$i] = 0;
		}

		$prevColor = '';
		$qntt = 0;
		$index = 0;

		$games = count($results);
		$currentGame = 0;

		// while ($currentGame < $games) {

		// }

		// while ($prevColor !== 'red' && isset($results[$index])) {
		// 	$qntt += 1;
		// 	$prevColor = $results[$index]['color'];
		// 	$index += 1;
		// }

		// dd($qntt);

		foreach ($results as $key => $result) {
			// while ($result['color'] === $prevColor) {

			// }



			if ($result['color'] == 'red') {
				$occurencies['red'] = 0;
				
				$occurencies['black'] += 1;
				$occurencies['grey'] += 1;
				$occurencies['white'] += 1;
			} else if ($result['color'] == 'black') {
				$occurencies['black'] = 0;
				
				$occurencies['red'] += 1;
				$occurencies['grey'] += 1;
				$occurencies['white'] += 1;
			} else if ($result['color'] == 'grey') {
				$occurencies['grey'] = 0;
				
				$occurencies['black'] += 1;
				$occurencies['red'] += 1;
				$occurencies['white'] += 1;
			} else if ($result['color'] == 'white') {
				$occurencies['white'] = 0;
				
				$occurencies['black'] += 1;
				$occurencies['grey'] += 1;
				$occurencies['red'] += 1;
			} 

			// $games[] = $occurencies;

			// collect stats
			if ($occurencies['red'] == 28 || $occurencies['black'] == 28 || $occurencies['grey'] == 28) {
				$stats[28] += 1;
			}

			$prevColor = $result['color'];
		}

		$statsForColors = $this->countByColors($results);

		return [$games, $stats, $statsForColors];
	}

	private function countByColors($results) {
		$colors = [
			'red' => 0, 'black' => 0, 'grey' => 0, 'white' => 0
		];

		foreach ($results as $result) {
			switch ($result['color']) {
				case 'red':
					$colors['red'] += 1;
					break;
				case 'grey':
					$colors['grey'] += 1;
					break;
				case 'black':
					$colors['black'] += 1;
					break;
				case 'white':
					$colors['white'] += 1;
					break;
				
				default:
					# code...
					break;
			}
		}

		return $colors;
	}

    public function collect(Request $request, $date, $limit) {
    	$urls = [];

    	// $limit = 2;
    	for ($i = 1; $i <= $limit; $i++) { 
    		$urls[] = 'https://olimp.betgamestv.eu/ext/game/results/olimp/' . $date . '/7/' . $i;
    	}

    	$data = [];
    	foreach ($urls as $key => $url) {
    		$data[] = $this->collectDataFor($url);
    	}

    	$totalData = [];
    	foreach ($data as $key => $trs) {
    		foreach ($trs as $tr) {
    			$results = [];
    			$index = 0;

    			$tr->filter('td')->each(function($node) use (&$results, &$index) {
	    			$input = $node->text();
	    			$input = htmlentities($input, null, 'utf-8');
					$input = str_replace("&nbsp;", '', $input);
					$input = str_replace("\n", '', $input);
					$input = str_replace(' ', '', $input);
					// dd($input);

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

	    		$totalData[] = $results;
    		}
    	}

    	foreach ($totalData as $result) {
    		WheelAnalyze::insert([
				'played_at' => $date . " " . $result['played_at'],
				'logged_at' => Carbon::now(),
				'match_id' => 1,
				'number' => $result['number'],
				'color' => $result['color']
			]);
    	}

    	return "DONE";
    }

    public function collectDataFor($url) {
    	$client = new Client();
		$crawler = $client->request('GET', $url);
		$index = 0;
		$count = 0;
		$results = [];
		$rawData = [];

		$crawler->filter('table.table-results tr.lottery-items-cell')->each(function($node) use (&$rawData) {
			$rawData[] = $node;
		});

		return $rawData;
    }
}
