<?php
date_default_timezone_set('Asia/Tokyo');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Disposition, Content-Type, Content-Length, Accept-Encoding, Origin, Accept, Access-Control-Allow-Headers, X-Token');
header('Access-Control-Expose-Headers: *');

header('Server: Hidden');
header('X-Powered-By: Hidden');

$result=[
	'_request'=>$_REQUEST,
	'_server'=> $_SERVER,
];

$config = [
	'external' => [
		'discord' => [
			'client_id' => 0,
			'client_secret' => 'Follow the https://discord.com/developers/applications, then set appropriate value.',
			'redirect_uri' => 'https://example.org/',
		],
	],
];
define('CONFIG_PATH', realpath(__DIR__.'/../.secret/config.json'));
if(!file_exists(CONFIG_PATH)){
	http_response_code(500);
	exit(1);
}

echo json_encode($result);
