<?php

/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * The first PHP Library to support OAuth for Twitter's REST API.
 */

/* Load OAuth lib. You can find it at http://oauth.net */
//require_once('OAuth.php');

/**
 * Twitter OAuth class
 */
class TwitterOAuth {
  /* Contains the last HTTP status code returned. */
  public $http_code;
  /* Contains the last API call. */
  public $url;
  /* Set up the API root URL. */
  public $host = "https://api.twitter.com/1.1/";
  /* Set up the API root URL. */
  public $search_host = "http://search.twitter.com/";
  /* Set timeout default. */
  public $timeout = 300;
  /* Set connect timeout. */
  public $connecttimeout = 300; 
  /* Verify SSL Cert. */
  public $ssl_verifypeer = FALSE;
  /* Respons format. */
  public $format = 'json';
  /* Decode returned json data. */
  public $decode_json = TRUE;
  /* Contains the last HTTP headers returned. */
  public $http_info;
  /* Set the useragnet. */
  public $useragent = 'azbn_ru';
  /* Immediately retry the API call if the response was not successful. */
  //public $retry = TRUE;




  /**
   * Set API URLS
   */
  /*
  function accessTokenURL()  { return 'https://api.twitter.com/oauth/access_token'; }
  function authenticateURL() { return 'https://twitter.com/oauth/authenticate'; }
  function authorizeURL()	{ return 'https://twitter.com/oauth/authorize'; }
  function requestTokenURL() { return 'https://api.twitter.com/oauth/request_token'; }
  */
  function accessTokenURL()  { return 'https://api.twitter.com/oauth/access_token'; }
  function authenticateURL() { return 'https://twitter.com/oauth/authenticate'; }
  function authorizeURL()	{ return 'https://twitter.com/oauth/authorize'; }
  function requestTokenURL() { return 'https://api.twitter.com/oauth/request_token'; }

  /**
   * Debug helpers
   */
  function lastStatusCode() { return $this->http_status; }
  function lastAPICall() { return $this->last_api_call; }

  /**
   * construct TwitterOAuth object
   */
  function __construct($param_arr) {//($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
	$consumer_key=$param_arr['consumer_key'];
	$consumer_secret=$param_arr['consumer_secret'];
	if($param_arr['oauth_token']) {$oauth_token=$param_arr['oauth_token'];}
	if($param_arr['oauth_token_secret']) {$oauth_token_secret=$param_arr['oauth_token_secret'];}
	$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
	$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
	if (!empty($oauth_token) && !empty($oauth_token_secret)) {
	  $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
	} else {
	  $this->token = NULL;
	}
	//echo 1;
  }


  /**
   * Get a request_token from Twitter
   *
   * @returns a key/value array containing oauth_token and oauth_token_secret
   */
  function getRequestToken($oauth_callback = NULL) {
	$parameters = array();
	if (!empty($oauth_callback)) {
	  $parameters['oauth_callback'] = $oauth_callback;
	  //d$parameters['x_auth_access_type'] = 'RWD';
	} 
	$request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
	$token = OAuthUtil::parse_parameters($request);
	$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
	return $token;
  }

  /**
   * Get the authorize URL
   *
   * @returns a string
   */
  function getAuthorizeURL($token, $sign_in_with_twitter = TRUE) {
	if (is_array($token)) {
	  $token = $token['oauth_token'];
	}
	if (empty($sign_in_with_twitter)) {
	  return $this->authorizeURL() . "?oauth_token={$token}";
	} else {
	   return $this->authenticateURL() . "?oauth_token={$token}";
	}
  }

  /**
   * Exchange request token and secret for an access token and
   * secret, to sign API calls.
   *
   * @returns array("oauth_token" => "the-access-token",
   *				"oauth_token_secret" => "the-access-secret",
   *				"user_id" => "9436992",
   *				"screen_name" => "abraham")
   */
  function getAccessToken($oauth_verifier = FALSE) {
	$parameters = array();
	if (!empty($oauth_verifier)) {
	  $parameters['oauth_verifier'] = $oauth_verifier;
	}
	$request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
	$token = OAuthUtil::parse_parameters($request);
	$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
	return $token;
  }

  /**
   * One time exchange of username and password for access token and secret.
   *
   * @returns array("oauth_token" => "the-access-token",
   *				"oauth_token_secret" => "the-access-secret",
   *				"user_id" => "9436992",
   *				"screen_name" => "abraham",
   *				"x_auth_expires" => "0")
   */  
  function getXAuthToken($username, $password) {
	$parameters = array();
	$parameters['x_auth_username'] = $username;
	$parameters['x_auth_password'] = $password;
	$parameters['x_auth_mode'] = 'client_auth';
	$request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
	$token = OAuthUtil::parse_parameters($request);
	$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
	return $token;
  }

  /**
   * GET wrapper for oAuthRequest.
   */
  function get($url, $parameters = array()) {
	$response = $this->oAuthRequest($url, 'GET', $parameters);
	if ($this->format === 'json' && $this->decode_json) {
	  return json_decode($response);
	}
	return $response;
  }
  
  /**
   * POST wrapper for oAuthRequest.
   */
  function post($url, $parameters = array()) {
	$response = $this->oAuthRequest($url, 'POST', $parameters);
	if ($this->format === 'json' && $this->decode_json) {
	  return json_decode($response);
	}
	return $response;
  }

  /**
   * DELETE wrapper for oAuthReqeust.
   */
  function delete($url, $parameters = array()) {
	$response = $this->oAuthRequest($url, 'DELETE', $parameters);
	if ($this->format === 'json' && $this->decode_json) {
	  return json_decode($response);
	}
	return $response;
  }

  /**
   * Format and sign an OAuth / API request
   */
  function oAuthRequest($url, $method, $parameters) {
	if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
	  $url = "{$this->host}{$url}.{$this->format}";
	}
	$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
	$request->sign_request($this->sha1_method, $this->consumer, $this->token);
	switch ($method) {
	case 'GET':
	  return $this->http($request->to_url(), 'GET');
	default:
	  return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
	}
  }

  /**
   * Make an HTTP request
   *
   * @return API results
   */
  function http($url, $method, $postfields = NULL) {
	$this->http_info = array();
	$ci = curl_init();
	/* Curl settings */
	curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
	curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
	curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
	curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
	curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
	//curl_setopt($ci, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data;","Content-Transfer-Encoding: base64;"));
	//curl_setopt($ci, CURLOPT_HEADER, true);
	curl_setopt($ci, CURLOPT_HEADER, FALSE);

	switch ($method) {
	  case 'POST':
		curl_setopt($ci, CURLOPT_POST, TRUE);
		if (!empty($postfields)) {
		  curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
		}
		break;
	  case 'DELETE':
		curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
		if (!empty($postfields)) {
		  $url = "{$url}?{$postfields}";
		}
	}

	curl_setopt($ci, CURLOPT_URL, $url);
	$response = curl_exec($ci);
	$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
	$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
	$this->url = $url;
	curl_close ($ci);
	return $response;
  }

  /**
   * Get the header info to store.
   */
  function getHeader($ch, $header) {
	$i = strpos($header, ':');
	if (!empty($i)) {
	  $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
	  $value = trim(substr($header, $i + 2));
	  $this->http_header[$key] = $value;
	}
	return strlen($header);
  }
  
  function process_json($url,$postargs=false){
		
		$ch = curl_init($url);

		if($postargs !== false){
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);
		}
		
		if($this->username !== false && $this->password !== false)
			curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
		
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

		$response = curl_exec($ch);
		
		$this->responseInfo=curl_getinfo($ch);
		curl_close($ch);
			 
		if(intval($this->responseInfo['http_code'])==200){
			return json_decode($response);
		}else{
			return false;
		}
	}
	/*
	function search($string=false){
		if( !$string )
			return false;
		$postargs='q='.urlencode($string);
		return $this->process_json($this->search_host,$postargs);
	}
	*/
	function search($param){
		$response = $this->searchRequest('search', 'GET', $param);
		if ($this->format === 'json' && $this->decode_json) {
		  return json_decode($response);
		}
		return $response;
	}
	
	function searchRequest($url, $method, $parameters) {
	if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
	  $url = "{$this->search_host}{$url}.{$this->format}";
	}
	$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
	$request->sign_request($this->sha1_method, $this->consumer, $this->token);
	switch ($method) {
	case 'GET':
	  return $this->http($request->to_url(), 'GET');
	default:
	  return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
	}
  }
	
}

?>