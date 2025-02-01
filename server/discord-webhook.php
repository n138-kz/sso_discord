<?php 
class discord{
	public $endpoint;
	public $postdata;
	function __construct() {
		$this->endpoint = 'https://discord.com/api/webhooks/';
		$this->postdata['content'] = '';
		$this->postdata['username'] = '';
		$this->postdata['title'] = '';
		$this->postdata['avater_url'] = '';
	}
	function set_endpoint($endpoint){
		$this->endpoint=$endpoint;
	}
	function set_value($key, $val){
		if ( isset($key) !== true ){ return false; }
		if ( isset($val) !== true ){ return false; }
		if ( $key === '' ){ return false; }
		if ( $val === '' ){ return false; }

		$this->postdata[$key] = $val;
	}
	function exec_curl(){
		$curl_req = curl_init($this->endpoint);
		curl_setopt($curl_req,CURLOPT_POST,           TRUE);
		curl_setopt($curl_req,CURLOPT_POSTFIELDS,     http_build_query($this->postdata));
		curl_setopt($curl_req,CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl_req,CURLOPT_FOLLOWLOCATION, TRUE); // Locationヘッダを追跡

		$curl_res=curl_exec($curl_req);
		$curl_res=json_decode($curl_res, TRUE);
		$curl_err=curl_error($curl_req);

		$curl_res=($curl_res=='')?null:$curl_res;
		$curl_err=($curl_err=='')?null:$curl_err;

		return [$curl_res,$curl_err];
	}
}

