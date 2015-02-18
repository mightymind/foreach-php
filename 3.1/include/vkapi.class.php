<?php

/**
 * VKAPI class for vk.com social network
 *
 * @package server API methods
 * @link http://vk.com/developers.php
 * @autor Oleg Illarionov
 * @version 1.0
 */
 
class vkapi {
private $api_secret;
private $app_id;
private $api_url;
	
	function __construct($param_arr) {
		$this->app_id = $param_arr['id'];
		$this->api_secret = $param_arr['secret'];
		$this->api_url = $param_arr['url'];
	}
	
	function api($method,$params=false,$query_url=false) {
		if (!$params) $params = array();
		if (!$query_url) $query_url = $this->api_url;
		$params['api_id'] = $this->app_id;
		$params['v'] = '3.0';
		$params['method'] = $method;
		$params['timestamp'] = time();
		$params['format'] = 'json';
		$params['random'] = rand(0,10000);
		ksort($params);
		$sig = '';
		foreach($params as $k=>$v) {
			$sig .= $k.'='.$v;
		}
		$sig .= $this->api_secret;
		$params['sig'] = md5($sig);
		$query = $query_url.'?'.$this->params($params);
		$res = file_get_contents($query);
		return json_decode($res, true);
	}
	
	function params($params) {
		$pice = array();
		foreach($params as $k=>$v) {
			$pice[] = $k.'='.urlencode($v);
		}
		return implode('&',$pice);
	}
}
?>
