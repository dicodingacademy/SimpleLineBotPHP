<?php

require __DIR__ . '/vendor/autoload.php';

// load config
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// initiate app
$configs =  [
	'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

/* ROUTES */
$app->get('/', function ($request, $response) {
	return "hello world";
});

$app->post('/', function ($request, $response)
{
	$body 	   = file_get_contents('php://input');
	$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	// is LINE_SIGNATURE exists in request header?
	if (empty($signature))
		return $response->withStatus(400, 'Signature not set');

	// is this request comes from LINE?
	if(SignatureValidator::validateSignature($body, $_SERVER('CHANNEL_SECRET'), $signature))
		return $response->withStatus(400, 'Invalid signature');

	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_SERVER('CHANNEL_ACCESS_TOKEN'));
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_SERVER('CHANNEL_SECRET')]);

	$events = json_decode($body, true);

	foreach ($events as $event)
	{
		if ($event['type'] == 'message')
		{
			if($event['message']['type'] == 'text')
			{
				$result = $bot->replyText('U6e98397e2214e9681cfe2b3eaf95933a', $event['message']['text']);
				return $result->getHTTPStatus() . ' ' . $result->getRawBody();
			}
		}
	}

});

// $app->get('/push/{to}/{message}', function ($request, $response, $args)
// {
// 	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_SERVER('CHANNEL_ACCESS_TOKEN'));
// 	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_SERVER('CHANNEL_SECRET')]);

// 	$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($args['message']);
// 	$result = $bot->pushMessage($args['to'], $textMessageBuilder);

// 	return $result->getHTTPStatus() . ' ' . $result->getRawBody();
// });

/* JUST RUN IT */
$app->run();