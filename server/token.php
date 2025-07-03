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
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config not found: No such file or directory: `'.CONFIG_PATH.'`. on '.__FILE__.'#'.__LINE__);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Hint: Make the config: `'.json_encode($config).'`. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
$result['config'] = $config = array_merge($config, json_decode(file_get_contents(CONFIG_PATH),TRUE));

if(!isset($config['internal'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['internal']['databases'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal/databases. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['internal']['databases'][0])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal/databases[0]. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['internal']['databases'][0]['schema'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal/databases[0]/schema. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['internal']['databases'][0]['host'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal/databases[0]/host. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['internal']['databases'][0]['port'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal/databases[0]/port. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['internal']['databases'][0]['database'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal/databases[0]/database. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['internal']['databases'][0]['user'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal/databases[0]/user. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['internal']['databases'][0]['password'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /internal/databases[0]/password. on '.__FILE__.'#'.__LINE__);
	exit(1);
}

# Database parameters
/*
* [vars]
* $pdo_option
* $pdo_dsn
*
* [table]
* %preset_discordme
* discord userinfo(users/@me)
* %preset_token
* discord token(oauth2/token)
*
*/
$pdo_option = [
	\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
	\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
	\PDO::ATTR_EMULATE_PREPARES => true,
	\PDO::ATTR_PERSISTENT => true,
];

$pdo_dsn = '';
$pdo_dsn .= $config['internal']['databases'][0]['schema'];
$pdo_dsn .= ':';
$pdo_dsn .= 'host='     . $config['internal']['databases'][0]['host'] . ';';
$pdo_dsn .= 'port='     . $config['internal']['databases'][0]['port'] . ';';
$pdo_dsn .= 'dbname='   . $config['internal']['databases'][0]['database'] . ';';
$pdo_dsn .= 'user='     . $config['internal']['databases'][0]['user'] . ';';
$pdo_dsn .= 'password=' . $config['internal']['databases'][0]['password'] . ';';
$pdo_dsn .= '';

if(!isset($config['external'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['ipinfo'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/ipinfo. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['ipinfo']['token'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/ipinfo/token. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/discord. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord']['auth_sso'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/discord/auth_sso. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
$list=['client_id','client_secret','token','token_type'];
for($i=0;$i<count($list);$i++) {
	if(!isset($config['external']['discord']['auth_sso'][$list[$i]])){
		http_response_code(500);
		$result['result']=[
			'id'=>1,
			'level'=>result_setLevel(2),
			'description'=>'Config load failed: No such item: /external/discord/auth_sso/'.$list[$i].'.',
			'details'=>json_encode(null),
		];
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.$result['result']['level'].': '.$result['result']['description'].' evented on '.__FILE__.'#'.__LINE__);
		echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
		exit(1);
	}
}
if(!isset($config['external']['discord']['webhook'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/discord/webhook. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord']['webhook']['notice'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/discord/webhook/notice. on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord']['webhook']['notice']['endpoint'])){
	http_response_code(500);
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'Fetal: Config load failed: No such element: /external/discord/webhook/notice/endpoint. on '.__FILE__.'#'.__LINE__);
	exit(1);
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
			'details'=>json_encode(null),
		];
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.$result['result']['level'].': '.$result['result']['description'].'. on '.__FILE__.'#'.__LINE__);
		echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
		exit(1);
	}
}

$result['result']=[
	'id'=>0,
	'level'=>result_setLevel(6),
	'description'=>null,
	'details'=>json_encode(null),
	'directmessage_channel'=>[],
	'ipinfo'=>[],
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
		'details'=>json_encode(['Error: Unauthorized','HTTP Error: '.$curl_res_info]),
	];
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.$result['result']['level'].': '.$result['result']['description'].' evented on '.__FILE__.'#'.__LINE__);
	echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
	exit(1);
}

try {
	$pdo = new \PDO( $pdo_dsn, null, null, $pdo_option );
	$pdo_con = $pdo->prepare('SELECT count(scope) FROM '.$config['internal']['databases'][0]['tableprefix'].'_token WHERE userid=?;');
	$pdo_res = $pdo_con->execute([
		$request['code'],
	]);
	$pdo_res = $pdo_con->fetch(\PDO::FETCH_ASSOC);
	if($pdo_res['count']==0){
		$pdo_con = $pdo->prepare('INSERT INTO '.$config['internal']['databases'][0]['tableprefix'].'_token ('
		. 'userid,'
		. 'access_code,'
		. 'access_token,'
		. 'expires_in,'
		. 'refresh_token,'
		. 'scope,'
		. 'token_type'
		. ') VALUES (?,?,?,?,?,?,?);');
		$pdo_res = $pdo_con->execute([
			$request['code'],
			$request['code'],
			$result['result']['oauth2_token']['access_token'],
			$result['result']['oauth2_token']['expires_in'],
			$result['result']['oauth2_token']['refresh_token'],
			$result['result']['oauth2_token']['scope'],
			$result['result']['oauth2_token']['token_type'],
		]);
		if(!$pdo_res){
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO] Insert error:');
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     table='.$config['internal']['databases'][0]['tableprefix'].'_token');
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     ext-user-id='.$request['code']);
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     remote-addr='.$_SERVER['REMOTE_ADDR'].'('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')');
		}
	}
	$pdo = null;
} catch (\Exception $th) {
	error_log('['.$th->getLine().'] ['.$_SERVER['REMOTE_ADDR'].'] '.$th->getMessage());
}

# Get IPinfo Lite Data
try {
	$pdo = new \PDO( $pdo_dsn, null, null, $pdo_option );
	$pdo_con = $pdo->prepare('SELECT '
		. 'ip,'
		. 'asn,'
		. 'as_name,'
		. 'as_domain,'
		. 'country_code,'
		. 'country,'
		. 'continent_code,'
		. 'continent'
		. ' FROM '.$config['internal']['databases'][0]['tableprefix'].'_ipinfo WHERE ip = ? limit 1;');
	$pdo_res = $pdo_con->execute([
		$_SERVER['REMOTE_ADDR'],
	]);
	$pdo_res = $pdo_con->fetch(\PDO::FETCH_ASSOC);
	if(count($pdo_res)>0){
		$result['result']['ipinfo'] = array_merge($result['result']['ipinfo'], $pdo_res);
	} else {
		$endpoint='https://api.ipinfo.io/lite/' . $_SERVER['REMOTE_ADDR'] . '?token=' . $config['external']['ipinfo']['token'];
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '."curl ${endpoint}");
		$curl_res = file_get_contents($endpoint);
		$curl_res = json_decode($curl_res, TRUE);
		$result['result']['ipinfo'] = array_merge($result['result']['ipinfo'], $curl_res);
	}

	$pdo = null;
} catch (\Exception $th) {
	error_log('['.$th->getLine().'] ['.$_SERVER['REMOTE_ADDR'].'] '.$th->getMessage());
}

try {
	$pdo = new \PDO( $pdo_dsn, null, null, $pdo_option );
	$pdo_con = $pdo->prepare('INSERT INTO '.$config['internal']['databases'][0]['tableprefix'].'_ipinfo ('
		. 'id,'
		. 'ip,'
		. 'asn,'
		. 'as_name,'
		. 'as_domain,'
		. 'country_code,'
		. 'country,'
		. 'continent_code,'
		. 'continent'
		. ') VALUES (?,?,?,?,?,?,?,?,?);');
	$pdo_res = $pdo_con->execute([
		$request['code'],
		$result['result']['ipinfo']['ip'],
		$result['result']['ipinfo']['asn'],
		$result['result']['ipinfo']['as_name'],
		$result['result']['ipinfo']['as_domain'],
		$result['result']['ipinfo']['country_code'],
		$result['result']['ipinfo']['country'],
		$result['result']['ipinfo']['continent_code'],
		$result['result']['ipinfo']['continent'],
	]);
	if(!$pdo_res){
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO] Insert error:');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     table='.$config['internal']['databases'][0]['tableprefix'].'_token');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     ext-user-id='.$request['code']);
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     remote-addr='.$_SERVER['REMOTE_ADDR'].'('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')');
	}

	$pdo_con = $pdo->prepare('UPDATE '.$config['internal']['databases'][0]['tableprefix'].'_token SET ipinfo_id = :access_code WHERE access_code = :access_code;');
	$pdo_res = $pdo_con->execute([
		'access_code' => $request['code'],
	]);
	if(!$pdo_res){
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO] Insert error:');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     table='.$config['internal']['databases'][0]['tableprefix'].'_token');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     ext-user-id='.$request['code']);
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     remote-addr='.$_SERVER['REMOTE_ADDR'].'('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')');
	}
	$pdo = null;
} catch (\Exception $th) {
	error_log('['.$th->getLine().'] ['.$_SERVER['REMOTE_ADDR'].'] '.$th->getMessage());
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
		'details'=>json_encode(['Error: Unauthorized','HTTP Error: '.$curl_res_info]),
	];
	error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.$result['result']['level'].': '.$result['result']['description'].' evented on '.__FILE__.'#'.__LINE__);
	echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
	exit(1);
}

try {
	# Update userid access_code ... discord.user.id
	$pdo = new \PDO( $pdo_dsn, null, null, $pdo_option );
	$pdo_con = $pdo->prepare('UPDATE '.$config['internal']['databases'][0]['tableprefix'].'_token SET userid = :userid WHERE access_code = :access_code;');
	$pdo_res = $pdo_con->execute([
		'userid'=>$result['result']['users_me']['id'],
		'access_code'=>$request['code'],
	]);
	if(!$pdo_res){
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO] Update error:');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     table='.$config['internal']['databases'][0]['tableprefix'].'_token');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     ext-user-id='.$request['code'].'('.$result['result']['users_me']['id'].')');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     remote-addr='.$_SERVER['REMOTE_ADDR'].'('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')');
		throw new Exception('[PDO] Update error: table='.$config['internal']['databases'][0]['tableprefix'].'_token', 1);
	}
	$pdo = null;
} catch (\Exception $th) {
	error_log('['.$th->getLine().'] ['.$_SERVER['REMOTE_ADDR'].'] '.$th->getMessage());
}
try {
	$pdo = new \PDO( $pdo_dsn, null, null, $pdo_option );
	$pdo_con = $pdo->prepare('SELECT count(userid) FROM '.$config['internal']['databases'][0]['tableprefix'].'_discordme WHERE userid=?;');
	$pdo_res = $pdo_con->execute([
		$result['result']['users_me']['id'],
	]);
	$pdo_res = $pdo_con->fetch(\PDO::FETCH_ASSOC);
	if($pdo_res['count']>0){
		$pdo_con = $pdo->prepare('DELETE FROM '.$config['internal']['databases'][0]['tableprefix'].'_discordme WHERE userid=?;');
		$pdo_res = $pdo_con->execute([
			$result['result']['users_me']['id'],
		]);
	}
	$pdo_con = $pdo->prepare('INSERT INTO '.$config['internal']['databases'][0]['tableprefix'].'_discordme'.' ('
		. 'userid,'
		. 'username,'
		. 'global_name,'
		. 'avatar,'
		. 'discriminator,'
		. 'public_flags,'
		. 'flags,'
		. 'banner,'
		. 'accent_color,'
		. 'avatar_decoration_data,'
		. 'collectibles,'
		. 'banner_color,'
		. 'clan,'
		. 'primary_guild,'
		. 'locale,'
		. 'premium_type'
		. ') VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);');
	$pdo_res = $pdo_con->execute([
		$result['result']['users_me']['id'],
		$result['result']['users_me']['username'],
		$result['result']['users_me']['global_name'],
		$result['result']['users_me']['avatar'],
		$result['result']['users_me']['discriminator'],
		$result['result']['users_me']['public_flags'],
		$result['result']['users_me']['flags'],
		$result['result']['users_me']['banner'],
		$result['result']['users_me']['accent_color'],
		$result['result']['users_me']['avatar_decoration_data'],
		$result['result']['users_me']['collectibles'],
		$result['result']['users_me']['banner_color'],
		json_encode($result['result']['users_me']['clan']),
		json_encode($result['result']['users_me']['primary_guild']),
		$result['result']['users_me']['locale'],
		$result['result']['users_me']['premium_type'],
	]);
	if(!$pdo_res){
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO] Insert error:');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     table='.$config['internal']['databases'][0]['tableprefix'].'_discordme');
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     ext-user-id='.$result['result']['users_me']['id']);
		error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     remote-addr='.$_SERVER['REMOTE_ADDR'].'('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')');
	}
	$pdo = null;
} catch (\Exception $th) {
	error_log('['.$th->getLine().'] ['.$_SERVER['REMOTE_ADDR'].'] '.$th->getMessage());
}

# refresh token
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

try {
	$pdo = new \PDO( $pdo_dsn, null, null, $pdo_option );
	$pdo_con = $pdo->prepare('SELECT count(scope) FROM '.$config['internal']['databases'][0]['tableprefix'].'_token WHERE userid=?;');
	$pdo_res = $pdo_con->execute([
		$request['code'],
	]);
	$pdo_res = $pdo_con->fetch(\PDO::FETCH_ASSOC);
	if($pdo_res['count']==0){
		$pdo_con = $pdo->prepare('INSERT INTO '.$config['internal']['databases'][0]['tableprefix'].'_token ('
		. 'userid,'
		. 'access_code,'
		. 'access_token,'
		. 'expires_in,'
		. 'refresh_token,'
		. 'scope,'
		. 'token_type,'
		. 'ipinfo_id'
		. ') VALUES (?,?,?,?,?,?,?,?);');
		$pdo_res = $pdo_con->execute([
			$result['result']['users_me']['id'],
			$request['code'],
			$result['result']['oauth2_token']['access_token'],
			$result['result']['oauth2_token']['expires_in'],
			$result['result']['oauth2_token']['refresh_token'],
			$result['result']['oauth2_token']['scope'],
			$result['result']['oauth2_token']['token_type'],
			$request['code'],
		]);
		if(!$pdo_res){
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO] Insert error:');
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     table='.$config['internal']['databases'][0]['tableprefix'].'_token');
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     ext-user-id='.$request['code']);
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     remote-addr='.$_SERVER['REMOTE_ADDR'].'('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')');
		}
	}
	$pdo = null;
} catch (\Exception $th) {
	error_log('['.$th->getLine().'] ['.$_SERVER['REMOTE_ADDR'].'] '.$th->getMessage());
}

# Notify to @me
$endpoint='https://discordapp.com/api/users/@me/channels';
$parameter=[
	'Authorization: '.$config['external']['discord']['auth_sso']['token_type'].' '.$config['external']['discord']['auth_sso']['token'],
	'Content-Type: application/json',
];
$payload=[ 'recipient_id' => $result['result']['users_me']['id'], ];
$payload=json_encode($payload);
$curl_req = curl_init($endpoint);
curl_setopt($curl_req, CURLOPT_POST,           TRUE);
curl_setopt($curl_req, CURLOPT_HTTPHEADER,     $parameter);
curl_setopt($curl_req, CURLOPT_POSTFIELDS,     $payload);
curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
$curl_res=curl_exec($curl_req);
$curl_res=json_decode($curl_res, TRUE);
$curl_res_info=curl_getinfo($curl_req, CURLINFO_RESPONSE_CODE);
$curl_res = (is_null($curl_res))?[]:$curl_res;
$curl_res = array_merge([
	'http'=>[
		'response'=>[
			'code'=>$curl_res_info,
		]
	]
], $curl_res);
error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.json_encode($curl_res));
$result['result']['directmessage_channel'] = array_merge($result['result']['directmessage_channel'], $curl_res);
$result['result']['directmessage_channel']['api_http_response_code'] = $curl_res_info;

$list=[
	'https://discord.com/api/channels/'.$result['result']['directmessage_channel']['id'].'/messages',
];
$list[]=$config['external']['discord']['webhook']['notice']['endpoint'];
foreach($list as $k => $endpoint) {
$parameter=[
	'Authorization: '.$config['external']['discord']['auth_sso']['token_type'].' '.$config['external']['discord']['auth_sso']['token'],
	'Content-Type: application/json',
];
$payload=[
	'content' => null,
	'embeds' => [
		[],
	],
];
$payload['embeds'][0]['title'] = '【ログイン通知】Login notice';
$payload['embeds'][0]['description'] = '';
$payload['embeds'][0]['description'] .= '<t:'.time().':F>にDiscordにログインしましたか？' . "\n";
$payload['embeds'][0]['description'] .= 'あなた自身が行った場合はこのメッセージは無視していただいて問題ありません。' . "\n\n";
$payload['embeds'][0]['description'] .= 'あなたではない場合今すぐ確認してください。' . "\n\n";
$payload['embeds'][0]['description'] .= 'https://discord.com/login';
$payload['embeds'][0]['fields'][] = [ 'name' => '接続元IPアドレス', 'value' => '['.$_SERVER['REMOTE_ADDR'].'](https://ipinfo.io/'.$_SERVER['REMOTE_ADDR'].')'.' ('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')', 'inline' => false, ];
$payload['embeds'][0]['fields'][] = [ 'name' => '接続元地理', 'value' => $result['result']['ipinfo']['continent'].'/'.$result['result']['ipinfo']['country'], 'inline' => false, ];
$payload['embeds'][0]['fields'][] = [ 'name' => '接続元プロバイダー', 'value' => $result['result']['ipinfo']['as_name'].' '.$result['result']['ipinfo']['as_domain'].'('.$result['result']['ipinfo']['asn'].')', 'inline' => false, ];
$payload['embeds'][0]['fields'][] = [ 'name' => 'ログイン元WEBサイト', 'value' => $request['redirect_url'], 'inline' => false, ];
$payload['embeds'][0]['fields'][] = [ 'name' => 'ログインユーザー', 'value' => '<@'.$result['result']['users_me']['id'].'>', 'inline' => false, ];
$payload['embeds'][0]['url'] = 'https://discord.com/login';
$payload['embeds'][0]['timestamp'] = date('c');
$payload['embeds'][0]['author'] = [];
$payload['embeds'][0]['author']['name'] = '';
$payload['embeds'][0]['author']['url'] = '';
$payload['embeds'][0]['author']['icon_url'] = '';
$payload['embeds'][0]['image'] = [];
$payload['embeds'][0]['image']['url'] = '';
$payload['embeds'][0]['thumbnail'] = [];
$payload['embeds'][0]['thumbnail']['url'] = '';
$payload['embeds'][0]['color'] = '#5865F2';
$payload['embeds'][0]['color'] = hexdec($payload['embeds'][0]['color']);
$payload['avatar_url'] = 'https://cdn.discordapp.com/embed/avatars/0.png';
$payload = json_encode($payload);
$curl_req = curl_init($endpoint);
curl_setopt($curl_req, CURLOPT_POST,           TRUE);
curl_setopt($curl_req, CURLOPT_HTTPHEADER,     $parameter);
curl_setopt($curl_req, CURLOPT_POSTFIELDS,     $payload);
curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
$curl_res=curl_exec($curl_req);
$curl_res=json_decode($curl_res, TRUE);
$curl_res = (is_null($curl_res))?[]:$curl_res;
$curl_res = array_merge([
	'http'=>[
		'response'=>[
			'code'=>curl_getinfo($curl_req, CURLINFO_RESPONSE_CODE)
		]
	]
], $curl_res);
error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.json_encode($curl_res));
}

# refresh token
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

try {
	$pdo = new \PDO( $pdo_dsn, null, null, $pdo_option );
	$pdo_con = $pdo->prepare('SELECT count(scope) FROM '.$config['internal']['databases'][0]['tableprefix'].'_token WHERE userid=?;');
	$pdo_res = $pdo_con->execute([
		$request['code'],
	]);
	$pdo_res = $pdo_con->fetch(\PDO::FETCH_ASSOC);
	if($pdo_res['count']==0){
		$pdo_con = $pdo->prepare('INSERT INTO '.$config['internal']['databases'][0]['tableprefix'].'_token ('
		. 'userid,'
		. 'access_code,'
		. 'access_token,'
		. 'expires_in,'
		. 'refresh_token,'
		. 'scope,'
		. 'token_type,'
		. 'ipinfo_id'
		. ') VALUES (?,?,?,?,?,?,?,?);');
		$pdo_res = $pdo_con->execute([
			$result['result']['users_me']['id'],
			$request['code'],
			$result['result']['oauth2_token']['access_token'],
			$result['result']['oauth2_token']['expires_in'],
			$result['result']['oauth2_token']['refresh_token'],
			$result['result']['oauth2_token']['scope'],
			$result['result']['oauth2_token']['token_type'],
			$request['code'],
		]);
		if(!$pdo_res){
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO] Insert error:');
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     table='.$config['internal']['databases'][0]['tableprefix'].'_token');
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     ext-user-id='.$request['code']);
			error_log('['.__LINE__.'] ['.$_SERVER['REMOTE_ADDR'].'] '.'[PDO]     remote-addr='.$_SERVER['REMOTE_ADDR'].'('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')');
		}
	}
	$pdo = null;
} catch (\Exception $th) {
	error_log('['.$th->getLine().'] ['.$_SERVER['REMOTE_ADDR'].'] '.$th->getMessage());
}

/*
* ブラウザで確認可能なアクセストークンから関連セッションすべて表示するクエリ
* sql
SELECT * FROM public.sso_discord_token
	WHERE access_code = (SELECT access_code
	FROM public.sso_discord_token
	WHERE access_token LIKE '%.koz0uAgn33O1PEm8dRkCFbGm20v7iZ')
	ORDER BY "timestamp" DESC
	
*/

# export
$result['config']['external']['discord']['auth_sso']['client_secret']='CLIENT_SECRET::Hidden';
echo json_encode($result['result'],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE|JSON_UNESCAPED_UNICODE);
