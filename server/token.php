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
	error_log('Fetal: Config not found: No such file or directory: `'.CONFIG_PATH.'`. evented on '.__FILE__.'#'.__LINE__);
	error_log('Hint: Make the config: `'.json_encode($config).'`. evented on '.__FILE__.'#'.__LINE__);
	exit(1);
}
$result['config'] = $config = array_merge($config, json_decode(file_get_contents(CONFIG_PATH),TRUE));
if(!isset($config['external'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such element: /external. evented on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such element: /external/discord. evented on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord']['auth_sso'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such element: /external/discord/auth_sso. evented on '.__FILE__.'#'.__LINE__);
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
			'details'=json_encode(null),
		];
		error_log($result['result']['level'].': '.$result['result']['description'].' evented on '.__FILE__.'#'.__LINE__);
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

$list=['code', 'redirect_url'];
for($i=0;$i<count($list);$i++) {
	if(!isset($request[$list[$i]])){
		http_response_code(400);
		$result['result']=[
			'id'=>1,
			'level'=>result_setLevel(3),
			'description'=>'Not attempted parameter `'.$list[$i].'`',
			'details'=json_encode(null),
		];
		error_log($result['result']['level'].': '.$result['result']['description'].'. evented on '.__FILE__.'#'.__LINE__);
		echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
		exit(1);
	}
}

$result['result']=[
	'id'=>0,
	'level'=>result_setLevel(6),
	'description'=>null,
	'details'=>json_encode(null),
	'oauth2_token'=>[
		'access_token'=>null,
		'expires_in'=>null,
		'id_token'=>null,
		'refresh_token'=>null,
		'scope'=>null,
		'token_type'=>null,
		'error'=>null,
		'error_description'=>null,
		'api_http_response_code'=>null,
	],
	'users_me'=>[
		'id'=>null,
		'username'=>null,
		'global_name'=>null,
		'avatar'=>null,
		'avatar_decoration_data'=>null,
		'discriminator'=>null,
		'public_flags'=>null,
		'flags'=>null,
		'email'=>null,
		'verified'=>null,
		'locale'=>null,
		'mfa_enabled'=>null,
		'collectibles'=>null,
		'banner_color'=>null,
		'clan'=>null,
		'primary_guild'=>null,
		'premium_type'=>null,
		'api_http_response_code'=>null,
	],
];

# oauth2_token
/*
* sh:
discord_client_id=$config['external']['discord']['auth_sso']['client_id']
discord_client_secret=$config['external']['discord']['auth_sso']['client_secret']
discord_redirect_uri=$config['external']['discord']['auth_sso']['redirect_uri']
code='xxxxxxxxxxxxxxxxxxxxxxxxxx.yyyyyyyyyyyyyyyyyyyyyyyyyyyyyy'
curl \
    -s \
    -i \
    -X POST \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    -d 'grant_type=authorization_code&client_id='${discord_client_id}'&client_secret='${discord_client_secret}'&redirect_uri='${discord_redirect_uri}'&code='${code} \
    'https://discordapp.com/api/oauth2/token'
*/
$endpoint='https://discordapp.com/api/oauth2/token';
$parameter='grant_type=authorization_code';
$parameter.='&client_id='.$config['external']['discord']['auth_sso']['client_id'];
$parameter.='&client_secret='.$config['external']['discord']['auth_sso']['client_secret'];
$parameter.='&code='.$request['code'];
$parameter.='&redirect_uri='.$request['redirect_url'];
$curl_req = curl_init($endpoint);
curl_setopt($curl_req, CURLOPT_POST,           TRUE);
curl_setopt($curl_req, CURLOPT_POSTFIELDS,     $parameter);
curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
$curl_res=curl_exec($curl_req);
$curl_res=json_decode($curl_res, TRUE);
$curl_res_info=curl_getinfo($curl_req, CURLINFO_RESPONSE_CODE);
$result['result']['oauth2_token'] = array_merge($result['result']['oauth2_token'], $curl_res);
$result['result']['oauth2_token']['api_http_response_code'] = $curl_res_info;

if($curl_res_info>399 || $curl_res_info<200){
	http_response_code(401);
	$result['result']=[
		'id'=>1,
		'level'=>result_setLevel(3),
		'description'=>'Unauthorized('.$curl_res_info.').',
		'details'=json_encode(['Error: Unauthorized','HTTP Error: '.$curl_res_info]),
	];
	error_log($result['result']['level'].': '.$result['result']['description'].' evented on '.__FILE__.'#'.__LINE__);
	echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
	exit(1);
}

# users_@me
$endpoint='https://discordapp.com/api/users/@me';
$parameter=[
	'Authorization: Bearer '.$result['result']['oauth2_token']['access_token'],
];
$curl_req = curl_init($endpoint);
curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($curl_req, CURLOPT_HTTPHEADER,     $parameter);
$curl_res=curl_exec($curl_req);
$curl_res=json_decode($curl_res, TRUE);
$curl_res_info=curl_getinfo($curl_req, CURLINFO_RESPONSE_CODE);
$result['result']['users_me'] = array_merge($result['result']['users_me'], $curl_res);
$result['result']['users_me']['api_http_response_code'] = $curl_res_info;

if($curl_res_info>299 || $curl_res_info<200){
	http_response_code(401);
	$result['result']=[
		'id'=>1,
		'level'=>result_setLevel(3),
		'description'=>'Unauthorized('.$curl_res_info.').',
		'details'=json_encode(['Error: Unauthorized','HTTP Error: '.$curl_res_info]),
	];
	error_log($result['result']['level'].': '.$result['result']['description'].' evented on '.__FILE__.'#'.__LINE__);
	echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
	exit(1);
}

# refrash token
$endpoint='https://discordapp.com/api/oauth2/token';
$parameter='grant_type=refresh_token';
$parameter.='&client_id='.$config['external']['discord']['auth_sso']['client_id'];
$parameter.='&client_secret='.$config['external']['discord']['auth_sso']['client_secret'];
$parameter.='&refresh_token='.$result['result']['oauth2_token']['refresh_token'];
$curl_req = curl_init($endpoint);
curl_setopt($curl_req, CURLOPT_POST,           TRUE);
curl_setopt($curl_req, CURLOPT_POSTFIELDS,     $parameter);
curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
$curl_res=curl_exec($curl_req);
$curl_res=json_decode($curl_res, TRUE);
$curl_res_info=curl_getinfo($curl_req, CURLINFO_RESPONSE_CODE);
$result['result']['oauth2_token'] = array_merge($result['result']['oauth2_token'], $curl_res);
$result['result']['oauth2_token']['api_http_response_code'] = $curl_res_info;

$result['config']['external']['discord']['auth_sso']['client_secret']='CLIENT_SECRET::Hidden';
echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
