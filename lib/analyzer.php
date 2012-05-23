<?php

class sphinxLogAnalyzer {
	const SLOW_QUERY_TRESHOLD = 0.5;

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
			 * 1: date
			 * 2: duration (seconds)
			 * 3: duration (milliseconds)
			 * 4: matchmode
			 * 5: filters count
			 * 6: sort mode
			 * 7: total matches
			 * 8: offset
			 * 9: limit
			 * 10: index
			 * 11: query
			 */

			if (preg_match('/\[(.+)\] (\d+).(\d+) sec \[(.+)\/(.+)\/(.+) (\d+) \((\d+),(\d+)\).*\] \[(.+)\] (.*)/i', $line, $matches)) {
				// prepare result container
				if (!isset($result['stats'][$matches[10]])) {
					$result['stats'][$matches[10]] = array();
				}
				if (!isset($result['stats'][$matches[10]][$matches[4]])) {
					$result['stats'][$matches[10]][$matches[4]] = array();
				}
				if (!isset($result['slow'][$matches[10]])) {
					$result['slow'][$matches[10]] = array();
				}

				// reference
				$stats = &$result['stats'][$matches[10]][$matches[4]];
				$slow = &$result['slow'][$matches[10]];

				// count hits
				if (!isset($stats['count'])) {
					$stats['count'] = 0;
				}
				++$stats['count'];

				// save dates
				$date = preg_replace('~\.\d+\s~', '', $matches[1]);
				$date = strtotime($date);
				if (!isset($stats['minDate']) || $stats['minDate'] > $date) {
					$stats['minDate'] = $date;
				}
				if (!isset($stats['maxDate']) || $stats['maxDate'] < $date) {
					$stats['maxDate'] = $date;
				}

				// count duration
				$duration = (float)$matches[2].'.'.$matches[3];
				if (!isset($stats['duration'])) {
					$stats['duration'] = 0;
				}
				$stats['duration'] += $duration;

				// add slow query
				if ($duration > self::SLOW_QUERY_TRESHOLD) {
					$slow['query'][] = $matches[10];
					$slow['duration'][] = $duration;
					$slow['matchmode'][] = $matches[4];
					$slow['sortmode'][] = $matches[6];
					$slow['filters'][] = $matches[5];
					$slow['matches'][] = $matches[7];
					$slow['offset'][] = $matches[8];
					$slow['limit'][] = $matches[9];
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
