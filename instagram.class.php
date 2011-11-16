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
 * $ig = new Instagram(array(
 *  apiKey      => '',
 *  apiSecret   => '',
 *  apiCallback => ''
 * ));
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
   * The user access token 
   */
  private $_accesstoken;

  /**
   * Available scopes
   * 
   * @var array
   */
  private $_scopes = array('basic', 'likes', 'comments', 'relationships');


  /**
   * Default constructor
   * 
   * @param array/string $config          Instagram configuration data
   * @return void
   */
  public function __construct($config) {
    if (true === is_array($config)) {
      // if you want to access user data
      $this->setApiKey($config[apiKey]);
      $this->setApiSecret($config[apiSecret]);
      $this->setApiCallback($config[apiCallback]);
    } else if (true === is_string($config)) {
      // if you only want to access public data
      $this->setApiKey($config);
    } else {
      throw new Exception("Error: __construct() - Configuration data is missing.");
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
   * Search for a user
   *
   * @param string $name                  Instagram username
   * @return mixed
   */
  public function searchUser($name) {
    return $this->_makeCall('users/search?q='.$name);
  }

  /**
   * Get user info by it's id
   *
   * @param string $id                    Instagram user id
   * @return mixed
   */
  public function getUser($id) {
    return $this->_makeCall('users/'.$id);
  }

  /**
   * Get user activity feed
   *
   * @return mixed
   */
  public function getUserFeed() {
    return $this->_makeCall('users/self/feed');
  }

  /**
   * Get user recent media
   *
   * @param string $id                    Instagram user id
   * @return mixed
   */
  public function getUserMedia($id) {
    return $this->_makeCall('users/'.$id.'/media/recent');
  }

  /**
   * Get the liked photos of a user
   *
   * @param string [optional] $limit      Limit of returned results
   * @return mixed
   */
  public function getUserLikes($limit = 8) {
    return $this->_makeCall('users/self/media/liked');
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
   * @param boolean $auth                 Whether the function requires an access token
   * @return mixed
   */
  private function _makeCall($function, $auth = false) {
    // check authentication method
    if (false === $auth) {
      // if the call doesn't requires authentication
      $authMethod = '&client_id='.$this->getApiKey();
    } else {
      // if the call needs a authenticated user
      if (true === isset(getAccessToken()) {
        $authMethod = '&access_token='.$this->getAccessToken();
      } else {
        throw new Exeption("Error: _makeCall() | $function - This method requires an authenticated user's access token.");
      }
    }
    // (false === $auth) ? $authMethod = 'client_id='.$this->getApiKey(); : $authMethod = 'access_token='.$this->getAccessToken();
    
    $apiCall = self::API_URL.$function.$authMethod;
    
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
   * Access Token Setter
   * 
   * @param string $token
   * @return void
   */
  public function setAccessToken($token) {
    $this->_accesstoken = $token;
  }

  /**
   * Access Token Getter
   * 
   * @return string
   */
  public function getAccessToken() {
    return $this->_accesstoken;
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