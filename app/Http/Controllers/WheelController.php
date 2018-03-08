<?php

namespace App\Http\Controllers;

use App\WheelAnalyze;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Http\Request;

class WheelController extends Controller
{

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
