<?php
header('Content-Type: Application/json');

$requests=$_REQUEST;

# -- method check
$allowed_methods = ['GET', 'POST', 'OPTIONS'];
header('Access-Control-Allow-Methods: ' . implode(', ', $allowed_methods));
if (!in_array($_SERVER['REQUEST_METHOD'], $allowed_methods)) {
	http_response_code(405);
	header('Allow: ' . implode(', ', $allowed_methods));
	exit('Method Not Allowed');
}

# -- discord authn/authz
$discord_client_id='$(( secret.DISCORD_CLIENT_ID ))';
$discord_client_secret='$(( secret.DISCORD_CLIENT_SECRET ))';
$discord_access_uri='$(( secret.DISCORD_ACCESS_URI ))';
$discord_access_token=[];
$discord_default_value=[
	'discord_access_token' => [
		'token_type'=>null,
		'access_token'=>null,
		'expires_in'=>null,
		'refresh_token'=>null,
		'scope'=>null,
		'id_token'=>null,
		'error'=>null,
		'error_description'=>null,
	]
];
try {
	# -- access_token
	if (!empty($requests['code'])) {
		$requests['access_code'] = $requests['code'];
	}
	if (FALSE) {
	} elseif (!empty($requests['access_token'])) {
		$discord_access_token['access_token'] = $requests['access_token'];
	} elseif(!empty($requests['refresh_token'])) {
		$curl=curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://discordapp.com/api/oauth2/token');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
			'grant_type' => 'refresh_token',
			'client_id' => $discord_client_id,
			'client_secret' => $discord_client_secret,
			'refresh_token' => $requests['refresh_token'],
		]));
		$discord_access_token=curl_exec($curl);
		$discord_access_token=mb_convert_encoding($discord_access_token, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
		$discord_access_token=json_decode($discord_access_token, TRUE);
		$discord_access_token=array_merge($discord_default_value['discord_access_token'], $discord_access_token);
		if (empty($discord_access_token['access_token'])) {
			http_response_code(403);
			exit('Unauthorized');
		}
	} elseif (empty($requests['access_code'])&&empty($requests['access_token'])&&empty($requests['refresh_token'])) {
			http_response_code(403);
			exit('access_token is Required: https://discord.com/oauth2/authorize?client_id='.$discord_client_id.'&response_type=code&redirect_uri='.urlencode('https://www.n138.jp/dev1/').'&scope=identify+email');
	} else {
		$curl=curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://discordapp.com/api/oauth2/token');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
			'grant_type' => 'authorization_code',
			'client_id' => $discord_client_id,
			'client_secret' => $discord_client_secret,
			'code' => $requests['access_code'],
			'redirect_uri' => $discord_access_uri,
		]));
		$discord_access_token=curl_exec($curl);
		$discord_access_token=mb_convert_encoding($discord_access_token, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
		$discord_access_token=json_decode($discord_access_token, TRUE);
		$discord_access_token=array_merge($discord_default_value['discord_access_token'], $discord_access_token);
		if (empty($discord_access_token['access_token'])) {
			http_response_code(403);
			exit('Unauthorized');
		}
	}

	# -- users/@me
	$curl=curl_init();
	curl_setopt($curl, CURLOPT_URL, 'https://discordapp.com/api/users/@me');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $discord_access_token['access_token']]);
	$discord_userinfo_me=curl_exec($curl);
	$discord_userinfo_me=mb_convert_encoding($discord_userinfo_me, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
	$discord_userinfo_me=json_decode($discord_userinfo_me, TRUE);
	foreach($discord_userinfo_me as $k1 => $v1) {
		$discord_userinfo_me[$k1] = (is_numeric($v1)) ? intval($v1) : $v1;
	}
	$discord_userinfo_me['accent_color'] = (is_numeric($discord_userinfo_me['accent_color'])) ? sprintf("#%06x", $discord_userinfo_me['accent_color']) : $discord_userinfo_me['accent_color'];
	$discord_userinfo_me['avatar_url'] = (!empty($discord_userinfo_me['avatar']))?'https://cdn.discordapp.com/avatars/'.$discord_userinfo_me['id'].'/'.$discord_userinfo_me['avatar']:null;

	# -- users/@me/guilds
	$curl=curl_init();
	curl_setopt($curl, CURLOPT_URL, 'https://discordapp.com/api/users/@me/guilds');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $discord_access_token['access_token']]);
	$discord_userinfo_guilds=curl_exec($curl);
	$discord_userinfo_guilds=mb_convert_encoding($discord_userinfo_guilds, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
	$discord_userinfo_guilds=json_decode($discord_userinfo_guilds, TRUE);
	$discord_userinfo_me['guilds']=$discord_userinfo_guilds;
	unset($discord_userinfo_guilds);

} catch (\Exception $e) {
	http_response_code(403);
	exit('Unauthorized');
}

# -- params check
$required = ['type', 'url'];
$missing = [];
foreach ($required as $param) {
	if (empty($requests[$param])) {
		$missing[] = $param;
	}
}
if (!empty($missing)) {
	http_response_code(400);
	echo 'access_token: ' . $discord_access_token['access_token'] . PHP_EOL . 'refresh_token: ' . $discord_access_token['refresh_token'] . PHP_EOL;
	exit(implode(', ', $missing) . ' is Required');
}

# -- params check
$required_params = [];
$required_params['type'] = ['direct', 'convert'];
if (!in_array($requests['type'], $required_params['type'])) {
	http_response_code(400);
	exit('Unknown type: ' . $requests['type']);
}

if(0==preg_match('/^(https?):\/\//', $requests['url'], $m)) {
	http_response_code(400);
	exit('Must be scheme http or https: ' . $requests['url']);
}

# ------------------------------
echo json_encode([
	$requests,
	$discord_access_token,
	$discord_userinfo_me,
]);
