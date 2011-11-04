<?php

header('Content-Type: text/html; charset=utf-8');

ini_set('display_errors', true);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '128M');

// process logfile
if (isset($_GET['submit']) && is_readable($_GET['file']) && !empty($_GET['name'])) {
	require dirname(__FILE__).'/lib/analyzer.php';
	$sphinxAnalyzer = new sphinxLogAnalyzer();
	$file = 'logs/'.base64_encode($_GET['name']).'.txt';
	$result = $sphinxAnalyzer->analyze($_GET['file']);
	file_put_contents($file, serialize($result));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv=content-type content="text/html; charset=UTF-8">
	<title>searchd query log analyzer</title>
	<style type="text/css">
		body, tr, td
		{
			margin: 0;
			padding: 0;
			font-family: Verdana, Arial, Sans-Serif;
			color: #000000;
		}
		body
		{
			padding-left: 40px;
			margin-right: 50px;
			margin-top: 10px;
		}
		form {
			background-color: lightgreen;
			padding: 10px;
		}
		h1
		{
			width: 100%;
			background-color: lightblue;
			font-size: 16pt;
			margin-left: -40px;
			padding: 3px;
			padding-left: 40px;
		}
		h2
		{
			font-size: 12pt;
			cursor: pointer;
		}
		table.result
		{
			width: 100%;
			margin-bottom: 20px;
		}
		table.result tbody tr td
		{
			background-color: #DDDDDD;
		}
		table.result thead tr th
		{
			text-align: left;
			background-color: lightblue;
		}

		table.resultSlow thead tr th
		{
			cursor: pointer;
		}
		table.result thead tr th.headerSortUp {
			background-image: url(asc.png);
			background-repeat: no-repeat;
			background-position: top right;
		}
		table.result thead tr th.headerSortDown {
			background-image: url(desc.png);
			background-repeat: no-repeat;
			background-position: top right;
		}
		table.result tbody tr td, table.result thead tr th
		{
			padding: 3px;
		}
		div.resultsets
		{
			margin-top: 10px;
			background-color: yellow;
			padding: 10px;
		}
	</style>
	<script type="text/javascript" src="jquery-1.2.6.min.js"></script>
	<script type="text/javascript" src="jquery.tablesorter.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('table.resultSlow').tablesorter({
				headers: {
					0: {
						sorter: 'digit'
					},
					1: {
						sorter: 'text'
					},
					2: {
						sorter: 'text'
					},
					3: {
						sorter: 'digit'
					},
					4: {
						sorter: 'digit'
					},
					5: {
						sorter: 'digit'
					},
					6: {
						sorter: 'digit'
					},
					7: {
						sorter: 'text'
					}
				},
				sortList: [[0, 1]]
			});
		});
	</script>
</head>
<body>
<form action="index.php" method="get">
	<table>
		<tr>
			<td>
				<label for="file">
					Path to query log file:
				</label>
			</td>
			<td>
				<input type="text" name="file" id="file" style="width: 400px;"/>
			</td>
		</tr>
		<tr>
			<td>
				<label for="name">
					Label for analyzed log:
				</label>
			</td>
			<td>
				<input type="text" name="name" id="name" style="width: 400px;"/>
			</td>
		</tr>
		<tr>
			<td>&#160;</td>
			<td>
				<input type="submit" name="submit" value="Let's go!" />
			</td>
		</tr>
	</table>
</form>
<?php

require dirname(__FILE__).'/lib/renderer.php';
$sphinxRenderer = new sphinxLogRenderer();

// show resultsets
$sphinxRenderer->showAnalyzedLogs();

// load resultset
if (isset($_GET['md5'])) {
	$result = unserialize(file_get_contents('logs/'.$_GET['md5'].'.txt'));
	if (!empty($result)) {
		$sphinxRenderer->showAnalyzedLogContent($result);
	}
}

?>
</body>
</html>