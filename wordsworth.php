<?php
$started = date("Y-m-d H:i:s");

$botToken = '<insert bot token here>';

// Link to add this bot to a server:
// https://discordapp.com/oauth2/authorize?&client_id=743092479762366484&scope=bot&permissions=10240

// Include PHP Discord library
include __DIR__.'/vendor/autoload.php';

$roundNumber = 0;
// Get word list
$words = json_decode(file_get_contents('https://1word.app/interface.php?action=wordsworth&key=toothycat'))->{'results'};
$wordCount = count($words);
$wordNumber = 0;
// Get song list
$songs = json_decode(file_get_contents('https://1word.app/interface.php?action=songs&key=toothycat'))->{'results'};
$songCount = count($songs);
$songNumber = 0;

$discord = new \Discord\Discord([
    'token' => $botToken
]);

$discord->on('ready', function ($discord) {
    echo "Bot is ready.", PHP_EOL;

    // Listen for events here
    $discord->on('message', function ($message) {
		global $words, $wordCount, $wordNumber, $songs, $songCount, $songNumber, $started, $roundNumber;
		
		if ($message->content == '/help') {
			deleteMessage($message);

			$text = "Commands I respond to:\n/help Displays this message\n/info Display word count and uptime statistics\n/word Generates a new random word\n/word <name> Generates a new random word for <name>\n/song Generates a random song title\n/song <name> Generates a random song title for <name>";
			$message->channel->sendMessage($text);
		}
		else if ($message->content == '/info') {
			deleteMessage($message);

			$text = "Word count: {$wordCount}\nSong count: {$songCount}\nRunning since: {$started}\nRounds played since last restart: {$roundNumber}";
			$message->channel->sendMessage($text);
		}
		else if (preg_match('/^\/word(.*)$/', $message->content, $matches)) {
			deleteMessage($message);

			$person = $matches[1] ? ' for' . $matches[1] : '';

			$word = $words[$wordNumber]->{'word'};
			$wordNumber++;
			$roundNumber++;
			if ($wordNumber >= $wordCount) {
				$wordNumber = 0;
			}
			$word = padString($word, 20);
			$text = ":see_no_evil: Word{$person}: ||{$word}||";
			$message->channel->sendMessage($text);
		}
		else if (preg_match('/^\/song(.*)$/', $message->content, $matches)) {
			deleteMessage($message);

			$person = $matches[1] ? ' for' . $matches[1] : '';

			$song = $songs[$songNumber]->{'song'} . ($songs[$songNumber]->{'artist'} ? ' - ' . $songs[$songNumber]->{'artist'} : '') . '     (' . $songs[$songNumber]->{'category'} . ')';
			$songNumber++;
			$roundNumber++;
			if ($songNumber >= $songCount) {
				$songNumber = 0;
			}
			$song = padString($song, 100);
			$text = ":see_no_evil: Song{$person}: ||{$song}||";
			$message->channel->sendMessage($text);
		}
	});
});

$discord->run();

function padString($string, $targetLength) {
	// Pads the given string with spaces at either side to make it the target length.
	if (strlen($string) >= $targetLength) return $string;
	while (strlen($string) < $targetLength) {
		$string = ' ' . $string . ' ';
	}
	$string = substr($string, 0, $targetLength);
	return $string;
}

function deleteMessage($message) {
	// Deletes a message from a Discord channel.
	global $botToken;

	$channelId = $message->channel->{'id'};
	$messageId = $message->{'id'};

	$url = "https://discordapp.com/api/channels/{$channelId}/messages/{$messageId}";

	//Create an $options array that can be passed into stream_context_create.
    $options = array(
        'http' =>
            array(
                'method' => 'DELETE',
				'header' => "Authorization: Bot {$botToken}"
            )
    );
    //Pass our $options array into stream_context_create.
    //This will return a stream context resource.
    $streamContext  = stream_context_create($options);
    //Use PHP's file_get_contents function to carry out the request.
	//We pass the $streamContext variable in as a third parameter.
	try {
		$result = file_get_contents($url, false, $streamContext);
	}
	catch(exception $e) {
		//If the request failed, log an error.
		print('Delete message failed: ' . error_get_last()['message'] . "\n");
		return;
	}
    //If $result is FALSE, then the request has failed.
    if ($result === false){
        //If the request failed, log an error.
		print('Delete message failed: ' . error_get_last()['message'] . "\n");
		return;
    }
    //If everything went OK, return the response.
    return $result;
}
