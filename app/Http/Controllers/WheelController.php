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
	private $_money = [
		// 11 => 205.00,
		// 12 => 258.75,
		// 13 => 360.50,
		// 14 => 542.50,
		// 15 => 915.00,
		16 => 205.00,
		17 => 258.75,
		18 => 360.50,
		19 => 542.50,
		20 => 915.00,
		21 => 1452.50,
		22 => 2470.00,
		23 => 4290.00,
		24 => 8015.00,
		25 => 13390.00,
		26 => 39985.00,
	];


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
		$emails = ['ravonrr@mail.ru'];
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
		$emails = ['ravonrr@mail.ru'];
		$vitek = '1000-victory@mail.ru';

		$target = '';
		$qntt = 0;
		$sendEmail = false;
		$sendEmailVitek = false;

		// TODO: if [param] times -> notify by email!
		$param = 10;
		$paramVitek = 20;

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


	public function analyze(Request $request, $date) {
		$results = WheelAnalyze::where('played_at', 'like', $date . '%')->get();
		$results = WheelAnalyze::get();
		// dd($results->count());

		$prevColor = '';

		$occurencies = [
			'red' => 0, 'black' => 0, 'grey' => 0, 'white' => 0,
			'1-6' => 0, '7-12' => 0, '13-18' => 0, '0' => 0
		];

		$stats = [
			'red' => [], 'black' => [], 'grey' => [], 'white' => [],
			'1-6' => [], '7-12' => [], '13-18' => [], '0' => []
		];
		// $games = [];


		// play round
		foreach ($results as $key => $result) 
		{
			$color = $result->color;
			$number = $result->number;
			// dd([$color, $number]);

			// save color
			if (isset($stats[$color][$occurencies[$color] + 1]))
				$stats[$color][$occurencies[$color] + 1] += 1;
			else
				$stats[$color][$occurencies[$color] + 1] = 1;

			// save number
			$numString = '0';

			if ($number >= 1 && $number <= 6)
				$numString = '1-6';
			else if ($number >= 7 && $number <= 12)
				$numString = '7-12';
			else if ($number >= 13 && $number <= 18)
				$numString = '13-18';

			if (isset($stats[$numString][$occurencies[$numString] + 1]))
				$stats[$numString][$occurencies[$numString] + 1] += 1;
			else
				$stats[$numString][$occurencies[$numString] + 1] = 1;

			// count by color
			if ($color == 'red') 
			{	

				$occurencies['red'] = 0;
				$occurencies['black'] += 1;
				$occurencies['grey'] += 1;
				$occurencies['white'] += 1;
			} 
			else if ($color == 'black') 
			{

				$occurencies['black'] = 0;
				$occurencies['red'] += 1;
				$occurencies['grey'] += 1;
				$occurencies['white'] += 1;
			}
			else if ($color == 'grey') 
			{

				$occurencies['grey'] = 0;
				$occurencies['red'] += 1;
				$occurencies['black'] += 1;
				$occurencies['white'] += 1;
			}
			else if ($color == 'white') 
			{

				$occurencies['white'] = 0;
				$occurencies['red'] += 1;
				$occurencies['grey'] += 1;
				$occurencies['black'] += 1;
			}

			// count by numbers
			if ($number >= 1 && $number <= 6)
			{
				$occurencies['1-6'] = 0;
				$occurencies['7-12'] += 1;
				$occurencies['13-18'] += 1;
				$occurencies['0'] += 1;
			}
			else if ($number >= 7 && $number <= 12)
			{
				$occurencies['7-12'] = 0;
				$occurencies['1-6'] += 1;
				$occurencies['13-18'] += 1;
				$occurencies['0'] += 1;
			}
			else if ($number >= 13 && $number <= 18)
			{
				$occurencies['13-18'] = 0;
				$occurencies['1-6'] += 1;
				$occurencies['7-12'] += 1;
				$occurencies['0'] += 1;
			}
			else
			{
				$occurencies['0'] = 0;
				$occurencies['1-6'] += 1;
				$occurencies['7-12'] += 1;
				$occurencies['13-18'] += 1;
			}
			
			// save color for next round
			$prevColor = $color;
		}

		$more10 = 0;
		$more20 = 0;
		$win = 0;
		$loss = 0;
		$earn = 0;

		foreach ($stats as $key => $results)
		{
			if ($key == 'white') continue;

			foreach ($results as $max => $res)
			{
				if (!isset($stats[$max])) $stats[$max] = 0;
				$stats[$max] += $res;

				if ($max >= 10 && $max <= 12) $win += $res;
				if ($max >= 13) $loss += $res;

				if ($max >= 10)
				{
					$more10 += $res;
				}

				if ($max >= 21)
				{
					$more20 += $res;
				} 
				

				if ($max >= 16 && $max <= 25)
				{
					// echo $key . ' - ' . $max . ' ' . $res . ' times --- ';
					// echo '<span style="color: green">+' . ($res * $this->_money[$max]) . 'tg</span><br>';

					// $win += $res * $this->_money[$max];
				}

				if ($max >= 26)
				{
					// echo $key . ' - ' . $max . ' ' . $res . ' times --- ';
					// echo '<span style="color:red">-' . ($this->_money[26] * $res) . 'tg</span>, but ';
					// echo '<span style="color: green">+' . ($this->_money[$max - 25 + 15] * $res) . 'tg</span><br>';

					// $win += $this->_money[$max - 25 + 15] * $res;
					// $loss += $res * $this->_money[26];
				}
			}
		}

		ksort($stats);
		dd($stats);

		// echo '<br><br>Win: ' . $win . ' times<br>';
		// echo 'Loss: ' . $loss . ' times<br>';
		// echo '<br><br>More 20: ' . $more20 . ' times<br>';
		// echo 'Win: ' . $win . ' tg<br>';
		// echo 'Loss: ' . $loss . ' tg<br>';
		// echo 'Earn: ' . ($win - $loss) . ' tg';
	}

	private function finishRound($color, $occurencies)
	{
		if (isset($stats[$ccc][$occurencies[$ccc]]))
		{
			$stats[$ccc][$occurencies[$ccc]] += 1;
		}
		else
		{
			$stats[$ccc][$occurencies[$ccc]] = 1;
		}
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

    public function collect(Request $request, $date, $limit = 16) {
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
    		foreach ($trs as $key => $tr) {
    			if ($key == 0) continue;

    			$results = [];
    			$index = 0;

    			// dd($tr->html());

    			$tr->filter('td')->each(function($node) use (&$results, &$index) {
	    			$input = $node->text();
	    			// dd($input);

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

						$results['number'] = intval($input);
						$results['color'] = $color;
					} else if ($index == 3) {

					}

					$index += 1;
	    		});

				// dd($results);

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

		$crawler->filter('table.table-results tr')->each(function($node) use (&$rawData) {
			// dd($node);
			$rawData[] = $node;
		});

		return $rawData;
    }
}
