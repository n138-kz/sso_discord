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
	'result' => [],
];

$config = [
	'external' => [
		'discord' => [
			'client_id' => 0,
			'client_secret' => 'Follow the https://discord.com/developers/applications, then set appropriate value.',
			'redirect_uri' => 'https://example.org/',
			'admin_users' => [],
		],
	],
];
define('CONFIG_PATH', realpath(__DIR__.'/../.secret/config.json'));
if(!file_exists(CONFIG_PATH)){
	http_response_code(500);
	error_log('Fetal: Config not found: No such file or directory: `'.CONFIG_PATH.'`. evented on '.__FILE__);
	error_log('Hint: Make the config: `'.json_encode($config).'`. evented on '.__FILE__);
	exit(1);
}
$config = array_merge($config, json_decode(file_get_contents(CONFIG_PATH),TRUE));
if(!isset($config['external'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such element: /external. evented on '.__FILE__);
	exit(1);
}
if(!isset($config['external']['discord'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such element: /external/discord. evented on '.__FILE__);
	exit(1);
}
if(!isset($config['external']['discord']['client_id'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such item: /external/discord/client_id. evented on '.__FILE__);
	exit(1);
}
if(!isset($config['external']['discord']['client_secret'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such item: /external/discord/client_secret. evented on '.__FILE__);
	exit(1);
}
if(!isset($config['external']['discord']['redirect_uri'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such item: /external/discord/redirect_uri. evented on '.__FILE__);
	exit(1);
}
if(!isset($config['external']['discord']['admin_users'])){
	$config['external']['discord']['admin_users']=[];
}
define('CLIENT_ID', $config['external']['discord']['client_id']);
define('CLIENT_SECRET', $config['external']['discord']['client_secret']);
define('REDIRECT_URI', $config['external']['discord']['redirect_uri']);
define('ACCESS_CODE', $_SERVER['HTTP_X_TOKEN']);
define('GRANT_TYPE', 'authorization_code');

$api = [
	'base_url' => 'https://discordapp.com/api/oauth2/token',
	'http_method' => 'POST',
	'headers' => [
		'Content-Type: application/x-www-form-urlencoded',
	],
	'payload' => http_build_query([
		'grant_type' => GRANT_TYPE,
		'client_id' => CLIENT_ID,
		'client_secret' => CLIENT_SECRET,
		'code' => ACCESS_CODE,
		'redirect_uri' => REDIRECT_URI,
	]),
];
$curl_req = curl_init($api['base_url']);
curl_setopt($curl_req,CURLOPT_CUSTOMREQUEST, mb_strtoupper($api['http_method']));
curl_setopt($curl_req,CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_req,CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($curl_req,CURLOPT_HTTPHEADER, $api['headers']);
curl_setopt($curl_req,CURLOPT_POSTFIELDS, $api['payload']);
$curl_res=curl_exec($curl_req);
$curl_res=json_decode($curl_res, TRUE);
$result['result'][] = $curl_res;

if (!isset($curl_res['access_token'])) {
	http_response_code(401);
	error_log('Fetal: `'.json_encode($result['result'][count($result['result'])-1]).'` evented on '.__FILE__);
	exit(1);
}

define('ACCESS_TOKEN', $curl_res['access_token']);

$api = [
	'base_url' => 'https://discordapp.com/api/users/@me',
	'http_method' => 'GET',
	'headers' => [
		'Content-Type: application/x-www-form-urlencoded',
		'Authorization: Bearer '.ACCESS_TOKEN,
	],
	'payload' => http_build_query([
		'grant_type' => GRANT_TYPE,
		'client_id' => CLIENT_ID,
		'client_secret' => CLIENT_SECRET,
		'code' => ACCESS_CODE,
		'redirect_uri' => REDIRECT_URI,
	]),
];

$curl_req = curl_init($api['base_url']);
curl_setopt($curl_req,CURLOPT_CUSTOMREQUEST, mb_strtoupper($api['http_method']));
curl_setopt($curl_req,CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_req,CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($curl_req,CURLOPT_HTTPHEADER, $api['headers']);
$curl_res=curl_exec($curl_req);
$curl_res=json_decode($curl_res, TRUE);
$result['result'][] = $curl_res;

if (array_search($curl_res['id'], $config['external']['discord']['admin_users'])) {
	echo json_encode($result);
	exit();
}
echo json_encode($result['result']);
