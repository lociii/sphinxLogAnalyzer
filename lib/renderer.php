<?php

class sphinxLogRenderer {
	public function showAnalyzedLogContent($result) {
		// display and save result
		ksort($result['stats']);
		foreach ($result['stats'] as $index => $data) {
			ksort($data);
			echo '<h1>'.$index.'</h1>';

			// show statistics
			echo '<table class="result resultOverview"><thead><tr>';
			echo '<th style="width: 25%;">Matchmode</th>';
			echo '<th style="width: 25%;">Query count</th>';
			echo '<th style="width: 25%;">Overall time (s)</th>';
			echo '<th style="width: 25%;">Average time (s)</th>';
			echo '</tr></thead>';
			foreach ($data as $statsIndex => $dataset) {
				$average = ($dataset['duration'] / $dataset['count']);
				echo '<tr>';
				echo '<td>'.$statsIndex.'</td>';
				echo '<td>'.$dataset['count'].'</td>';
				echo '<td>'.number_format($dataset['duration'], 3).'</td>';
				echo '<td'.($average >= 0.1 ? ' style="background-color: red;"' : '').'>'.number_format($average, 3).'</td>';
				echo '</tr>';
			}
			echo '</table>';

			if (!empty($_GET['slow'])) {
				continue;
			}

			// show slow queries
			if (!empty($result['slow'][$index]['duration'])) {
				$index_clean = str_replace(' ', '', $index);
				echo '<h2 onclick="$(\'#slowQueries_'.$index_clean.'\').toggle();">Slow queries ('.count($result['slow'][$index]['duration']).')</h2>';
				echo '<table class="result resultSlow" id="slowQueries_'.$index_clean.'" style="display: none;"><thead><tr>';
				echo '<th style="width: 150px;">Time (s)</th>';
				echo '<th style="width: 120px;">Matchmode</th>';
				echo '<th style="width: 120px;">Sortmode</th>';
				echo '<th style="width: 80px;">Filters</th>';
				echo '<th style="width: 140px;">Total matches</th>';
				echo '<th style="width: 80px;">Offset</th>';
				echo '<th style="width: 80px;">Limit</th>';
				echo '<th>Query</th></tr></thead>';
				foreach ($result['slow'][$index]['duration'] as $slowIndex => $dataset) {
					echo '<tr>';
					echo '<td>'.number_format($dataset, 3).'</td>';
					echo '<td>'.$result['slow'][$index]['matchmode'][$slowIndex].'</td>';
					echo '<td>'.$result['slow'][$index]['sortmode'][$slowIndex].'</td>';
					echo '<td>'.$result['slow'][$index]['filters'][$slowIndex].'</td>';
					echo '<td>'.$result['slow'][$index]['matches'][$slowIndex].'</td>';
					echo '<td>'.$result['slow'][$index]['offset'][$slowIndex].'</td>';
					echo '<td>'.$result['slow'][$index]['limit'][$slowIndex].'</td>';
					echo '<td>'.$result['slow'][$index]['query'][$slowIndex].'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}
		}

		// show misses
		if (!empty($result['miss'])) {
			echo '<table class="result resultMisses"><thead><tr><th>Query</th></tr></thead>';
			foreach ($result['miss'] as $dataset) {
				echo '<tr>';
				echo '<td>'.$dataset.'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}

	public function showAnalyzedLogs() {
		// initialize
		$data = array(
			'name'	=> array(),
			'file'	=> array(),
		);

		// read directory
		$d = dir('logs/');
		while (false !== ($entry = $d->read())) {
			if (substr($entry, -4, 4) == '.txt') {
				$data['name'][] = base64_decode(substr($entry, 0, -4));
				$data['file'][] = substr($entry, 0, -4);
			}
		}
		$d->close();
		array_multisort($data['name'], SORT_ASC, $data['file']);

		// render it
		if (!empty($data['name'])) {
			echo '<div class="resultsets"><strong>Analyzed logs</strong><br /><br />';
			foreach ($data['name'] as $key => $value) {
				echo '<a href="index.php?md5='.$data['file'][$key].'">'.$value.'</a><br />';
			}
			echo '</div>';
		}
	}
}
