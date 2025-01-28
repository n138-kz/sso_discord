<?php
date_default_timezone_set('Asia/Tokyo');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Disposition, Content-Type, Content-Length, Accept-Encoding, Origin, Accept, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-Token');
header('Access-Control-Expose-Headers: *');

header('Server: Hidden');
header('X-Powered-By: Hidden');

echo json_encode($_REQUEST);
