<?php
$cfg=file_get_contents('../.secret/config.json');
$cfg=json_decode($cfg, TRUE);
var_dump($cfg['external']['discord']['webhook']['notice']['endpoint']);
$tmp=tempnam(sys_get_temp_dir(), 'php_'.hash('crc32',time()));
$tmp_data=json_encode(['time'=>time(),'_server'=>$_SERVER]);
file_put_contents($tmp,$tmp_data,LOCK_EX);

$curl_file = new \CURLFile($tmp, 'application/json', 'tmp'.time().'.json');
$postdata = $param = [
	'file'=>$curl_file,
	'content'=>'Hello World,',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $cfg['external']['discord']['webhook']['notice']['endpoint']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
$result = curl_exec($ch);
$result_decode = json_decode($result, true);

unlink($tmp);
