<?php
date_default_timezone_set('Asia/Tokyo');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Disposition, Content-Type, Content-Length, Accept-Encoding, Origin, Accept, Access-Control-Allow-Headers, X-Token');
header('Access-Control-Expose-Headers: *');

header('Server: Hidden');
header('X-Powered-By: Hidden');

$result=[
	'_request'=>$_REQUEST,
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
if(!isset($config['external']['discord']['auth_sso']['client_id'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such item: /external/discord/auth_sso/client_id. evented on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord']['auth_sso']['client_secret'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such item: /external/discord/auth_sso/client_secret. evented on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord']['auth_sso']['redirect_uri'])){
	http_response_code(500);
	error_log('Fetal: Config load failed: No such item: /external/discord/auth_sso/redirect_uri. evented on '.__FILE__.'#'.__LINE__);
	exit(1);
}
if(!isset($config['external']['discord']['auth_sso']['admin_users'])){
	$config['external']['discord']['auth_sso']['admin_users']=[];
}
if(!isset($_SERVER['REQUEST_METHOD'])||($_SERVER['REQUEST_METHOD']!='OPTIONS'&&$_SERVER['REQUEST_METHOD']!='GET')){
	http_response_code(405);
	error_log('Fetal: ['.$_SERVER['REMOTE_ADDR'].'] Requested method('.$_SERVER['REQUEST_METHOD'].') has not allowed.');
	exit(1);
}

/* Client token --> discord_access_token **/
if(!isset($_SERVER['HTTP_X_TOKEN'])){
	http_response_code(400);
	error_log('Fetal: ['.$_SERVER['REMOTE_ADDR'].'] Not set X-Token');
	exit(1);
}
define('CLIENT_ID', $config['external']['discord']['auth_sso']['client_id']);
define('CLIENT_SECRET', $config['external']['discord']['auth_sso']['client_secret']);
define('REDIRECT_URI', $config['external']['discord']['auth_sso']['redirect_uri']);
define('ACCESS_CODE', $_SERVER['HTTP_X_TOKEN']);

$api = [
	'base_url' => 'https://discordapp.com/api/oauth2/token',
	'http_method' => 'POST',
	'headers' => [
		'Content-Type: application/x-www-form-urlencoded',
	],
	'payload' => http_build_query([
		'grant_type' => 'authorization_code',
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
$result['result']['token_authorization'][] = $curl_res;

if (!isset($curl_res['access_token'])) {
	http_response_code(401);
	error_log('Fetal: `'.json_encode($curl_res).'` evented on '.__FILE__.'#'.__LINE__);
	exit(1);
}

/* discord_access_token --> accesable user-info **/
define('ACCESS_TOKEN', $curl_res['access_token']);

$api = [
	'base_url' => 'https://discordapp.com/api/users/@me',
	'http_method' => 'GET',
	'headers' => [
		'Content-Type: application/x-www-form-urlencoded',
		'Authorization: Bearer '.ACCESS_TOKEN,
	],
	'payload' => http_build_query([
		'grant_type' => 'authorization_code',
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
$result['result']['d_user'] = $curl_res;
$result['result']['d_user']['avatar_url'] = 'https://cdn.discordapp.com/avatars/'.$curl_res['id'].'/'.$curl_res['avatar'];
$result['result']['d_user']['is_local_admin'] = FALSE;

if (array_search($curl_res['id'], $config['external']['discord']['auth_sso']['admin_users'])===FALSE){
} else if (array_search($curl_res['id'], $config['external']['discord']['auth_sso']['admin_users'])>=0) {
	/* Admin only item(s) **/
	$result['result']['d_user']['is_local_admin']=TRUE;
}

/* Get Refresh token **/
define('REFRESH_TOKEN', $result['result']['token_authorization'][count($result['result']['token_authorization'])-1]['refresh_token']);

$api = [
	'base_url' => 'https://discordapp.com/api/oauth2/token',
	'http_method' => 'POST',
	'headers' => [
		'Content-Type: application/x-www-form-urlencoded',
	],
	'payload' => http_build_query([
		'grant_type' => 'refresh_token',
		'client_id' => CLIENT_ID,
		'client_secret' => CLIENT_SECRET,
		'refresh_token' => REFRESH_TOKEN,
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
$result['result']['token_authorization'][] = $curl_res;
$result['result']['d_user']['access_token'] = $curl_res['access_token'];

/* End **/
unset($i,$j,$k,$v);
if($config['external']['discord']['webhook']['notice']['active']){
	try{
		require_once('discord-webhook.php');
		if(!isset($config['external']['discord']['webhook']['notice']['endpoint'])){throw new Exception('Undefined key(/external/discord/webhook/notice/endpoint)');}
		$webhook=new discord();
		$webhook->set_endpoint($config['external']['discord']['webhook']['notice']['endpoint']);
		$webhook->set_value('username', 'Bot-WebHook');
		$webhook->set_value('avatar_url', $result['result']['d_user']['avatar_url']);
		$webhook->set_value('content', '```json'.PHP_EOL.json_encode($result['result']['d_user']).PHP_EOL.'```');
		$tmp=tempnam(sys_get_temp_dir(), 'php_'.hash('crc32',time()));
		file_put_contents($tmp,json_encode($result,JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE),LOCK_EX);
		$tmp=new \CURLFile($tmp, 'application/json', 'tmp'.time().'.json');
		$webhook->set_file($tmp);
		$embeds=[
			[
				'title'=>'Debug LOG discord-webhook',
				'color'=>$config['external']['discord']['webhook']['notice']['color'],
				'fields'=>[
					[
						'inline'=>FALSE,
						'name'=>'',
						'value'=>'',
					],
				],
			],
		];
		/* $webhook->set_value('embeds', $embeds); **/
		$webhook=$webhook->exec_curl();
		$discord_returnmesg=array_search('Must be 2000 or fewer in length.', $webhook['result']['result']['content']);
		if(isset($webhook['result']['content'])&&is_array($webhook['result']['content'])&&$discord_returnmesg!==FALSE&&$discord_returnmesg>=0){
			error_log('Discord webhook has response message: '. $webhook['result']['result']['content'][$discord_returnmesg]);
			$webhook=new discord();
			$webhook->set_endpoint($config['external']['discord']['webhook']['notice']['endpoint']);
			$webhook->set_value('username', 'Bot-WebHook');
			$webhook->set_value('avatar_url', $result['result']['d_user']['avatar_url']);
			$webhook->set_value('content', $webhook['result']['result']['content'][$discord_returnmesg]);
			$webhook=$webhook->exec_curl();
		}

		if($webhook['errors']['code']!==0){
			error_log(json_encode($webhook));
			$webhook=new discord();
			$webhook->set_endpoint($config['external']['discord']['webhook']['notice']['endpoint']);
			$webhook->set_value('username', 'Bot-WebHook');
			$webhook->set_value('avatar_url', $result['result']['d_user']['avatar_url']);
			$webhook->set_value('content', 'DEBUG Export:'.PHP_EOL.$webhook['errors']['details']);
			$tmp=tempnam(sys_get_temp_dir(), 'php_'.hash('crc32',time()));
			file_put_contents($tmp,json_encode($webhook,JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE),LOCK_EX);
			$tmp=new \CURLFile($tmp, 'application/json', 'tmp'.time().'.json');
			$webhook->set_file($tmp);
			$webhook=$webhook->exec_curl();
		}

		if(isset($i)&&is_array($i)){
			foreach($i as $k => $v){
				$webhook=new discord();
				$webhook->set_endpoint($config['external']['discord']['webhook']['notice']['endpoint']);
				$webhook->set_value('username', 'Bot-WebHook');
				$webhook->set_value('avatar_url', $result['result']['d_user']['avatar_url']);
				$webhook->set_value('content', 'DEBUG Export');
				$tmp=tempnam(sys_get_temp_dir(), 'php_'.hash('crc32',time()));
				file_put_contents($tmp,json_encode($v,JSON_PRETTY_PRINT|JSON_INVALID_UTF8_IGNORE),LOCK_EX);
				$tmp=new \CURLFile($tmp, 'application/json', 'tmp'.time().'.json');
				$webhook->set_file($tmp);
				$webhook=$webhook->exec_curl();
			}
		}

	}catch(\Throwable $e){
		error_log('Fetal: discord-webhook error: This was caught: '.$e->getMessage());
	}catch(\Exception $e){
		error_log('Fetal: discord-webhook error: '.$e->getMessage());
	}
}
echo json_encode($result['result']['d_user']);
exit();

