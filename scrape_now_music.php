<?php
	$index = file_get_contents('https://www.nowmusic.com/albums/');

	preg_match_all('/https:\/\/www\.nowmusic\.com\/albums\/.*?\//', $index, $matches);

	$output = '';

	foreach ($matches[0] as $url) {
		if (preg_match('/\/feed\//', $url)) continue;
		if (preg_match('/\/now-107\//', $url)) continue;
		if (preg_match('/\/now-100-hits-60s-no-1s\//', $url)) continue;
		if (preg_match('/\/now-100-hits-legends\//', $url)) continue;

		//print($url."\n");

		$page = file_get_contents($url);
		//file_put_contents('test.html', $page);
		//print_r($page);

		$year = 0;

		preg_match('/<title>(.*?) - .*?<\/title>/', $page, $title);
		if (preg_match('/\s(\d*)s/', $title[1], $match)) {
			$year = $match[1];
			if ($year == '00') $year = 2000;
			if (strlen($year) == 2) $year = '19' . $year;
		}
		$title = $title[1];

		if (!preg_match('/\d/', $title)) continue;
		if (preg_match('/(Party|Dance|Running|Century|Club|Rock|Years|What I Call NOW)/', $title)) continue;
		if (preg_match('/100 Hits/', $title) && !preg_match('/\d\ds/', $title)) continue;
		if (!preg_match('/100 Hits/', $title) && preg_match('/No\.1/', $title)) continue;

		print($title . "\n");

		if ($year == 0) {
			preg_match('/<p>Released .*?(\d*)<\/p>/', $page, $releaseDate);
			$year = $releaseDate[1];
		}

		$year = substr($year, 0, 3) . '0';

		preg_match_all('/<strong>.*?<\/strong>\s*<p>.*?<\/p>/', $page, $tracks);
		
		foreach ($tracks[0] as $track) {
			preg_match('/<strong>(.*?)\s*<\/strong>\s*<p>(.*?)\s*<\/p>/', $track, $split);
			$song = $split[1];
			$artist = $split[2];
			$output .= $title . ',' . $year . ',"' . $song . '","' . $artist . '"' . "\n";
			//print $title . ',' . $year . ',' . $song . ',' . $artist . "\n";
		}

		//break;
	};
	file_put_contents('output.csv', $output);
?>