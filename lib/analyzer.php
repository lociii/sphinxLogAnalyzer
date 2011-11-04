<?php

class sphinxLogAnalyzer {
	public function analyze($file) {
		$result = array(
			'stats'	=> array(),
			'slow'	=> array(
				'query'			=> array(),
				'duration'		=> array(),
				'matchmode'		=> array(),
				'sortmode'		=> array(),
				'filters'		=> array(),
				'matches'		=> array(),
				'offset'		=> array(),
				'limit'			=> array(),
			),
			'miss'	=> array(),
		);

		// open logfile
		$handle = fopen($file, 'r');

		// process file line by line
		while ($line = fgets($handle)) {
			$matches = array();
			/*
			 * 1: duration (seconds)
			 * 2: duration (milliseconds)
			 * 3: matchmode
			 * 4: filters count
			 * 5: sort mode
			 * 6: total matches
			 * 7: offset
			 * 8: limit
			 * 9 4: index
			 * 10 5: query
			 */

			if (preg_match('/\[.+\] (\d+).(\d+) sec \[(.+)\/(.+)\/(.+) (\d+) \((\d+),(\d+)\).*\] \[(.+)\] (.*)/i', $line, $matches)) {
				// prepare result container
				if (!isset($result['stats'][$matches[9]])) {
					$result['stats'][$matches[9]] = array();
				}
				if (!isset($result['stats'][$matches[9]][$matches[3]])) {
					$result['stats'][$matches[9]][$matches[3]] = array();
				}
				if (!isset($result['slow'][$matches[9]])) {
					$result['slow'][$matches[9]] = array();
				}

				// reference
				$stats = &$result['stats'][$matches[9]][$matches[3]];
				$slow = &$result['slow'][$matches[9]];

				// count hits
				if (!isset($stats['count'])) {
					$stats['count'] = 0;
				}
				++$stats['count'];

				// count duration
				$duration = (float)$matches[1].'.'.$matches[2];
				if (!isset($stats['duration'])) {
					$stats['duration'] = 0;
				}
				$stats['duration'] += $duration;

				// add slow query
				if ($duration > 0.5) {
					$slow['query'][] = $matches[10];
					$slow['duration'][] = $duration;
					$slow['matchmode'][] = $matches[3];
					$slow['sortmode'][] = $matches[5];
					$slow['filters'][] = $matches[4];
					$slow['matches'][] = $matches[6];
					$slow['offset'][] = $matches[7];
					$slow['limit'][] = $matches[8];
				}
			}
			else {
				$result['miss'][] = $line;
			}
		}
		fclose($handle);

		// return result
		return $result;
	}
}
