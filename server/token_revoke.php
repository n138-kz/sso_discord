<?php
date_default_timezone_set('Asia/Tokyo');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Disposition, Content-Type, Content-Length, Accept-Encoding, Origin, Accept, Access-Control-Allow-Headers, X-Token');
header('Access-Control-Expose-Headers: *');

header('Server: Hidden');
header('X-Powered-By: Hidden');

function result_setLevel($level=6) {
	$level_text='';
	switch($level){
		case 0:
			$level_text='Emerg';break;
		case 1:
			$level_text='Alert';break;
		case 2:
			$level_text='Crit';break;
		case 3:
			$level_text='Error';break;
		case 4:
			$level_text='Warning';break;
		case 5:
			$level_text='Notice';break;
		case 6:
			$level_text='Information';break;
		case 7:
			$level_text='Debug';break;
	}
	return $level_text.';'.$level;
}

$result=[
	'_request'=>[],
	'_server'=> $_SERVER,
	'result' => [],
	'config' => [],
];

$config = [
	'external' => [
		'discord' => [
			'auth_sso' => [
				'client_id' => 0,
				'client_secret' => 'Follow the https://discord.com/developers/applications, then set appropriate value.',
				'redirect_uri' => 'https://example.org/',
				'admin_users' => [],
			],
		],
	],
];
define('CONFIG_PATH', realpath(__DIR__.'/../.secret/config.json'));
if(!file_exists(CONFIG_PATH)){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config not found: No such file or directory: `'.CONFIG_PATH.'`.');
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Hint: Make the config: `'.json_encode($config).'`.');
	exit(1);
}
$result['config'] = $config = array_merge($config, json_decode(file_get_contents(CONFIG_PATH),TRUE));
if(!isset($config['external'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external.');
	exit(1);
}
if(!isset($config['external']['discord'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/discord.');
	exit(1);
}
if(!isset($config['external']['discord']['auth_sso'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/discord/auth_sso.');
	exit(1);
}
$list=['client_id','client_secret',];
for($i=0;$i<count($list);$i++) {
	if(!isset($config['external']['discord']['auth_sso'][$list[$i]])){
		http_response_code(500);
		$result['result']=[
			'id'=>1,
			'level'=>result_setLevel(2),
			'description'=>'Config load failed: No such item: /external/discord/auth_sso/'.$list[$i].'.',
			'details'=>json_encode(null),
		];
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.$result['result']['level'].': '.$result['result']['description']);
		echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
		exit(1);
	}
}

if(FALSE) {
} elseif( TRUE ) {
	# multipart/form-data
	$result['_request']=$_REQUEST;
} elseif( FALSE ) {
	# application/json
	$result['_request']=file_get_contents('php://input');
}
$request=$result['_request'];

$list=['token',];
for($i=0;$i<count($list);$i++) {
	if(!isset($request[$list[$i]])){
		http_response_code(400);
		$result['result']=[
			'id'=>1,
			'level'=>result_setLevel(3),
			'description'=>'Not attempted parameter `'.$list[$i].'`',
			'details'=>json_encode(null),
		];
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.$result['result']['level'].': '.$result['result']['description'].'.');
		echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
		exit(1);
	}
}

$result['result']=[
	'id'=>0,
	'level'=>result_setLevel(6),
	'description'=>null,
	'details'=>json_encode(null),
];

# oauth2_token_revoke
/*
* sh:
discord_client_id=$config['external']['discord']['auth_sso']['client_id']
discord_client_secret=$config['external']['discord']['auth_sso']['client_secret']
token='xxxxxxxxxxxxxxxxxxxxxxxxxx.yyyyyyyyyyyyyyyyyyyyyyyyyyyyyy'
curl \
    -s \
    -i \
    -X POST \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'client_id='${discord_client_id}'&client_secret='${discord_client_secret}'&token='${token} \
    'https://discordapp.com/api/oauth2/token/revoke'
*/
$endpoint='https://discordapp.com/api/oauth2/token/revoke';
$parameter='client_id='.$config['external']['discord']['auth_sso']['client_id'];
$parameter.='&client_secret='.$config['external']['discord']['auth_sso']['client_secret'];
$parameter.='&token='.$request['token'];
$curl_req = curl_init($endpoint);
curl_setopt($curl_req, CURLOPT_POST,           TRUE);
curl_setopt($curl_req, CURLOPT_POSTFIELDS,     $parameter);
curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
$curl_res=curl_exec($curl_req);
$curl_res=json_decode($curl_res, TRUE);
$curl_res_info=curl_getinfo($curl_req, CURLINFO_RESPONSE_CODE);

if($curl_res_info>399 || $curl_res_info<200){
	http_response_code(401);
	$result['result']=[
		'id'=>1,
		'level'=>result_setLevel(3),
		'description'=>'Unauthorized('.$curl_res_info.').',
		'details'=>json_encode(['Error: Unauthorized','HTTP Error: '.$curl_res_info]),
	];
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.$result['result']['level'].': '.$result['result']['description']);
	echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
	exit(1);
}

$result['result'] = array_merge($result['result'], [
	'level'=>result_setLevel(6),
	'description'=>'Token has revoked',
	'details'=>json_encode(['Token has revoked. Have a nice day.']),
]);
echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
