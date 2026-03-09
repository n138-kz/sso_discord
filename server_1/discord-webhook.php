<?php 
class discord{
	public $endpoint;
	public $postdata;
	public $postfile;
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
	function set_file($file){
		$this->postfile=true;
		$this->set_value('file', $file);
	}
	function exec_curl(){
		$curl_req = curl_init($this->endpoint);
		curl_setopt($curl_req,CURLOPT_POST,           TRUE);
		if($this->postfile){
			$headers=[
				'Content-Type: multipart/form-data',
			];
			curl_setopt($curl_req,CURLOPT_HTTPHEADER, $headers);
		}
		if($this->postfile){
			curl_setopt($curl_req,CURLOPT_POSTFIELDS, $this->postdata);
		} else {
			curl_setopt($curl_req,CURLOPT_POSTFIELDS, http_build_query($this->postdata));
		}
		curl_setopt($curl_req,CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl_req,CURLOPT_FOLLOWLOCATION, TRUE); // Locationヘッダを追跡

		$curl_res=curl_exec($curl_req);
		$curl_res=json_decode($curl_res, TRUE);
		$curl_err=curl_error($curl_req);
		$curl_hdr=curl_getinfo($curl_req);

		$curl_res=($curl_res=='')?null:$curl_res;
		$curl_err=($curl_err=='')?null:$curl_err;

		return ['result'=>$curl_res,'errors'=>['code'=>curl_errno($curl_req),'details'=>$curl_err],'response_header'=>$curl_hdr];
	}
}

