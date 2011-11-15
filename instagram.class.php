<?php

/**
 * Instagram API class
 * API Documentation: http://instagram.com/developer/
 * 
 * @author Christian Metz
 * @since 30.10.2011
 * @copyright Christian Metz | MetzWeb Networks
 * @version 1.0
 * 
 * @todo Extend error handling, Add new methods, Contruct with an array
 * 
 * @example Get started:
 * $ig = new Instagram('API Key', 'API Secret', 'Callback URL');
 * echo "<a href='{$ig->getLoginUrl()}'>Login</a>";
 * 
 * @example Get user token:
 * $code = $_GET['code'];
 * $userToken = $ig->getOAuthToken($code);
 * echo 'Your username is: '.$userToken->user->username;
 * 
 * @example Get user likes:
 * $likes = getUserLikes($userToken->access_token, 2);
 * echo "<pre>";
 * print_r($likes);
 * echo "<pre>";
 */ 

class Instagram {

  /**
   * The API base URL
   */
  const API_URL = 'https://api.instagram.com/v1/';

  /**
   * The API OAuth URL
   */
  const API_OAUTH_URL = 'https://api.instagram.com/oauth/authorize';

  /**
   * The OAuth token URL
   */
  const API_OAUTH_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

  /**
   * The Instagram API Key
   * 
   * @var string
   */
  private $_apikey;

  /**
   * The Instagram OAuth API secret
   * 
   * @var string
   */
  private $_apisecret;

  /**
   * The callback URL
   * 
   * @var string
   */
  private $_callbackurl;

  /**
   * Available scopes
   * 
   * @var array
   */
  private $_scopes = array('basic', 'likes', 'comments', 'relationships');


  /**
   * Default constructor
   * 
   * @param string/array $apiKey          Instagram API Key / Configuration
   * @param string $apiSecret             Instagram OAuth Secret
   * @param string $apiCallback           Website Callback URL
   * @return void
   */
  public function __construct($apiKey, $apiSecret = null, $apiCallback = null) {
    if (is_string($apiKey) && isset($apiSecret) && isset($apiCallback)) {
      $this->setApiKey($apiKey);
      $this->setApiSecret($apiSecret);
      $this->setApiCallback($apiCallback);
    } else if (is_array($apiKey)) {
      $this->setApiKey($apiKey[apiKey]);
      $this->setApiSecret($apiKey[apiSecret]);
      $this->setApiCallback($apiKey[apiCallback]);
    } else {
      throw new Exception("Error: __construct() - The parameter / array isn't valid.");
    }
  }

  /**
   * Generates the OAuth login URL
   *
   * @param array [optional] $scope       Requesting additional permissions
   * @return string                       Instagram OAuth login URL
   */
  public function getLoginUrl($scope = array('basic')) {
    if (is_array($scope) && count(array_intersect($scope, $this->_scopes)) === count($scope)) {
      return self::API_OAUTH_URL.'?client_id='.$this->getApiKey().'&redirect_uri='.$this->getApiCallback().'&scope='.implode('+', $scope).'&response_type=code';
    } else {
      throw new Exeption("Error: getLoginUrl() - The parameter isn't an array or invalid scope permissions used.");
    }
  }

  /**
   * Get the liked photos of a user
   *
   * @param string $token                 Valid Instagram token (can be recived)
   * @param string [optional] $limit      Limit of returned results
   * @return mixed
   */
  public function getUserLikes($token, $limit = 8) {
    return $this->_makeCall('users/self/media/liked?access_token='.$token.'&count='.$limit);
  }

  /**
   * Get the OAuth data of a user by the returned callback code
   *
   * @param string $code                  OAuth code variable (after a successful login)
   * @param boolean [optional] $token     If it's true, only the access token will be returned
   * @return mixed
   */
  public function getOAuthToken($code, $token = false) {
    $apiData = array(
      'grant_type'      => 'authorization_code',
      'client_id'       => $this->getApiKey(),
      'client_secret'   => $this->getApiSecret(),
      'redirect_uri'    => $this->getApiCallback(),
      'code'            => $code
    );
    
    $result = $this->_makeOAuthCall($apiData);
    return (false === $token) ? $result : $result->access_token;
  }

  /**
   * The call operator
   *
   * @param string $function              API data string
   * @return mixed
   */
  private function _makeCall($function) {
    $apiCall = self::API_URL.$function;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiCall);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $jsonData = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($jsonData);
  }

  /**
   * The OAuth call operator
   *
   * @param array $apiData                The post API data
   * @return mixed
   */
  private function _makeOAuthCall($apiData) {
    $apiHost = self::API_OAUTH_TOKEN_URL;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiHost);
    curl_setopt($ch, CURLOPT_POST, count($apiData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    $jsonData = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($jsonData);
  }

  /**
   * API-key Setter
   * 
   * @param string $apiKey
   * @return void
   */
  public function setApiKey($apiKey) {
    $this->_apikey = $apiKey;
  }

  /**
   * API Key Getter
   * 
   * @return string
   */
  public function getApiKey() {
    return $this->_apikey;
  }

  /**
   * API Secret Setter
   * 
   * @param string $apiSecret 
   * @return void
   */
  public function setApiSecret($apiSecret) {
    $this->_apisecret = $apiSecret;
  }

  /**
   * API Secret Getter
   * 
   * @return string
   */
  public function getApiSecret() {
    return $this->_apisecret;
  }
	
  /**
   * API Callback URL Setter
   * 
   * @param string $apiCallback
   * @return void
   */
  public function setApiCallback($apiCallback) {
    $this->_callbackurl = $apiCallback;
  }

  /**
   * API Callback URL Getter
   * 
   * @return string
   */
  public function getApiCallback() {
    return $this->_callbackurl;
  }

}

?>