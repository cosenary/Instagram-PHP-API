<?php

namespace MetzWeb\Instagram;

/**
 * Instagram API class
 *
 * API Documentation: http://instagram.com/developer/
 * Class Documentation: https://github.com/cosenary/Instagram-PHP-API
 *
 * @author Christian Metz
 * @since 30.10.2011
 * @copyright Christian Metz - MetzWeb Networks 2011-2014
 * @version 2.2
 * @license BSD http://www.opensource.org/licenses/bsd-license.php
 */
class Instagram
{
    /**
     * The API base URL.
     */
    const API_URL = 'https://api.instagram.com/v1/';

    /**
     * The API OAuth URL.
     */
    const API_OAUTH_URL = 'https://api.instagram.com/oauth/authorize';

    /**
     * The OAuth token URL.
     */
    const API_OAUTH_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

    /**
     * The Instagram API Key.
     *
     * @var string
     */
    private $_apikey;

    /**
     * The Instagram OAuth API secret.
     *
     * @var string
     */
    private $_apisecret;

    /**
     * The callback URL.
     *
     * @var string
     */
    private $_callbackurl;

    /**
     * The user access token.
     *
     * @var string
     */
    private $_accesstoken;

    /**
     * Whether a signed header should be used.
     *
     * @var bool
     */
    private $_signedheader = false;

    /**
     * Available scopes.
     *
     * @var string[]
     */
    private $_scopes = array('basic', 'likes', 'comments', 'relationships', 'public_content', 'follower_list');

    /**
     * Available actions.
     *
     * @var string[]
     */
    private $_actions = array('follow', 'unfollow', 'approve', 'ignore');
    
    /**
     * Rate limit.
     *
     * @var int
     */
    private $_xRateLimitRemaining;

    /**
     * Proxy server.
     *
     * @var string
     */
    private $_proxyServer;

    /**
     * Proxy username.
     *
     * @var string
     */
    private $_proxyUser;

    /**
     * Proxy password.
     *
     * @var string
     */
    private $_proxyPwd;

    /**
     * Proxy port.
     *
     * @var int
     */
    private $_proxyPort;

    /**
     * Default constructor.
     *
     * @param array|string $config Instagram configuration data
     *
     * @return void
     *
     * @throws \MetzWeb\Instagram\InstagramException
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            // if you want to access user data
            $this->setApiKey($config['apiKey']);
            $this->setApiSecret($config['apiSecret']);
            $this->setApiCallback($config['apiCallback']);
        } elseif (is_string($config)) {
            // if you only want to access public data
            $this->setApiKey($config);
        } else {
            throw new InstagramException('Error: __construct() - Configuration data is missing.');
        }
    }

    /**
     * Generates the OAuth login URL.
     *
     * @param string[] $scopes Requesting additional permissions
     *
     * @return string Instagram OAuth login URL
     *
     * @throws \MetzWeb\Instagram\InstagramException
     */
    public function getLoginUrl($scopes = array('basic'))
    {
        if (is_array($scopes) && count(array_intersect($scopes, $this->_scopes)) === count($scopes)) {
            return self::API_OAUTH_URL . '?client_id=' . $this->getApiKey() . '&redirect_uri=' . urlencode($this->getApiCallback()) . '&scope=' . implode('+',
                $scopes) . '&response_type=code';
        }

        throw new InstagramException("Error: getLoginUrl() - The parameter isn't an array or invalid scope permissions used.");
    }

    /**
     * Search for a user.
     *
     * @param string $name Instagram username
     * @param int $limit Limit of returned results
     *
     * @return mixed
     */
    public function searchUser($name, $limit = 0)
    {
        $params = array();

        $params['q'] = $name;
        if ($limit > 0) {
            $params['count'] = $limit;
        }

        return $this->_makeCall('users/search', $params);
    }

    /**
     * Get user info.
     *
     * @param int $id Instagram user ID
     *
     * @return mixed
     */
    public function getUser($id = 0)
    {
        if ($id === 0) {
            $id = 'self';
        }

        return $this->_makeCall('users/' . $id);
    }

    /**
     * Get user recent media.
     *
     * @param int|string $id Instagram user ID
     * @param int $limit Limit of returned results
     * @param int $min_id Return media later than this min_id
     * @param int $max_id Return media earlier than this max_id
     *
     * @return mixed
     */
    public function getUserMedia($id = 'self', $limit = 0, $min_id = null, $max_id = null)
    {
        $params = array();

        if ($limit > 0) {
            $params['count'] = $limit;
        }
        if (isset($min_id)) {
            $params['min_id'] = $min_id;
        }
        if (isset($max_id)) {
            $params['max_id'] = $max_id;
        }

        return $this->_makeCall('users/' . $id . '/media/recent', $params);
    }

    /**
     * Get the liked photos of a user.
     *
     * @param int $limit Limit of returned results
     * @param int $max_like_id Return media liked before this id
     *
     * @return mixed
     */
    public function getUserLikes($limit = 0, $max_like_id = null)
    {
        $params = array();

        if ($limit > 0) {
            $params['count'] = $limit;
        }
        if (isset($max_id)) {
            $params['max_like_id'] = $max_like_id;
        }

        return $this->_makeCall('users/self/media/liked', $params);
    }

    /**
     * DEPRECATED
     * Get the list of users this user follows
     *
     * @param int|string $id Instagram user ID.
     * @param int $limit Limit of returned results
     *
     * @return void
     */
    public function getUserFollows($id = 'self', $limit = 0)
    {
        return $this->getFollows($id, $limit);
    }

    /**
     * Get the list of users the authenticated user follows.
     *
     * @return mixed
     */
    public function getFollows($id = 'self', $limit = 0)
    {
        $params = array();

        if ($limit > 0) {
            $params['count'] = $limit;
        }

        return $this->_makeCall('users/' . $id . '/follows', $params);
    }

    /**
     * DEPRECATED
     * Get the list of users this user is followed by.
     *
     * @param int|string $id Instagram user ID
     * @param int $limit Limit of returned results
     *
     * @return void
     */
    public function getUserFollower($id = 'self', $limit = 0)
    {
        return $this->getFollower($id, $limit);
    }

    /**
     * Get the list of users this user is followed by.
     *
     * @return mixed
     */
    public function getFollower($id = 'self', $limit = 0)
    {
        $params = array();

        if ($limit > 0) {
            $params['count'] = $limit;
        }

        return $this->_makeCall('users/' . $id . '/followed-by', $params);
    }

    /**
     * Get information about a relationship to another user.
     *
     * @param int $id Instagram user ID
     *
     * @return mixed
     */
    public function getUserRelationship($id)
    {
        return $this->_makeCall('users/' . $id . '/relationship');
    }
    
    /**
     * Get the value of X-RateLimit-Remaining header field.
     *
     * @return int X-RateLimit-Remaining API calls left within 1 hour
     */
    public function getRateLimit()
    {
        return $this->_xRateLimitRemaining;
    }

    /**
     * Modify the relationship between the current user and the target user.
     *
     * @param string $action Action command (follow/unfollow/approve/ignore)
     * @param int $user Target user ID
     *
     * @return mixed
     *
     * @throws \MetzWeb\Instagram\InstagramException
     */
    public function modifyRelationship($action, $user)
    {
        if (in_array($action, $this->_actions) && isset($user)) {
            return $this->_makeCall('users/' . $user . '/relationship', array('action' => $action), 'POST');
        }

        throw new InstagramException('Error: modifyRelationship() | This method requires an action command and the target user id.');
    }

    /**
     * Search media by its location.
     *
     * @param float $lat Latitude of the center search coordinate
     * @param float $lng Longitude of the center search coordinate
     * @param int $distance Distance in metres (default is 1km (distance=1000), max. is 5km)
     *
     * @return mixed
     */
    public function searchMedia($lat, $lng, $distance = 1000)
    {
        return $this->_makeCall('media/search', array(
            'lat' => $lat,
            'lng' => $lng,
            'distance' => $distance
        ));
    }

    /**
     * Get media by its id.
     *
     * @param int $id Instagram media ID
     *
     * @return mixed
     */
    public function getMedia($id)
    {
        return $this->_makeCall('media/' . $id);
    }

    /**
     * Search for tags by name.
     *
     * @param string $name Valid tag name
     *
     * @return mixed
     */
    public function searchTags($name)
    {
        return $this->_makeCall('tags/search', array('q' => $name));
    }

    /**
     * Get info about a tag
     *
     * @param string $name Valid tag name
     *
     * @return mixed
     */
    public function getTag($name)
    {
        return $this->_makeCall('tags/' . $name);
    }

    /**
     * Get a recently tagged media.
     *
     * @param string $name Valid tag name
     * @param int $limit Limit of returned results
     * @param int $min_tag_id Return media before this min_tag_id
     * @param int $max_tag_id Return media after this max_tag_id
     *
     * @return mixed
     */
    public function getTagMedia($name, $limit = 0, $min_tag_id = null, $max_tag_id = null)
    {
        $params = array();

        if ($limit > 0) {
            $params['count'] = $limit;
        }
        if (isset($min_tag_id)) {
            $params['min_tag_id'] = $min_tag_id;
        }
        if (isset($max_tag_id)) {
            $params['max_tag_id'] = $max_tag_id;
        }

        return $this->_makeCall('tags/' . $name . '/media/recent', $params);
    }

    /**
     * Get a list of users who have liked this media.
     *
     * @param int $id Instagram media ID
     *
     * @return mixed
     */
    public function getMediaLikes($id)
    {
        return $this->_makeCall('media/' . $id . '/likes');
    }

    /**
     * Get a list of comments for this media.
     *
     * @param int $id Instagram media ID
     *
     * @return mixed
     */
    public function getMediaComments($id)
    {
        return $this->_makeCall('media/' . $id . '/comments');
    }

    /**
     * Add a comment on a media.
     *
     * @param int $id Instagram media ID
     * @param string $text Comment content
     *
     * @return mixed
     */
    public function addMediaComment($id, $text)
    {
        return $this->_makeCall('media/' . $id . '/comments', array('text' => $text), 'POST');
    }

    /**
     * Remove user comment on a media.
     *
     * @param int $id Instagram media ID
     * @param string $commentID User comment ID
     *
     * @return mixed
     */
    public function deleteMediaComment($id, $commentID)
    {
        return $this->_makeCall('media/' . $id . '/comments/' . $commentID, null, 'DELETE');
    }

    /**
     * Set user like on a media.
     *
     * @param int $id Instagram media ID
     *
     * @return mixed
     */
    public function likeMedia($id)
    {
        return $this->_makeCall('media/' . $id . '/likes', null, 'POST');
    }

    /**
     * Remove user like on a media.
     *
     * @param int $id Instagram media ID
     *
     * @return mixed
     */
    public function deleteLikedMedia($id)
    {
        return $this->_makeCall('media/' . $id . '/likes', null, 'DELETE');
    }

    /**
     * Get information about a location.
     *
     * @param int $id Instagram location ID
     *
     * @return mixed
     */
    public function getLocation($id)
    {
        return $this->_makeCall('locations/' . $id);
    }

    /**
     * Get recent media from a given location.
     *
     * @param int $id Instagram location ID
     * @param int $min_id Return media before this min_id
     * @param int $max_id Return media after this max_id
     *
     * @return mixed
     */
    public function getLocationMedia($id, $min_id = null, $max_id = null)
    {
        $params = array();

        if (isset($min_id)) {
            $params['min_id'] = $min_id;
        }
        if (isset($max_id)) {
            $params['max_id'] = $max_id;
        }

        return $this->_makeCall('locations/' . $id . '/media/recent', $params);
    }

    /**
     * Get recent media from a given location.
     *
     * @param float $lat Latitude of the center search coordinate
     * @param float $lng Longitude of the center search coordinate
     * @param int $distance Distance in meter (max. distance: 5km = 5000)
     * @param int $facebook_places_id Returns a location mapped off of a
     *                                Facebook places id. If used, a Foursquare
     *                                id and lat, lng are not required.
     * @param int $foursquare_id Returns a location mapped off of a foursquare v2
     *                                api location id. If used, you are not
     *                                required to use lat and lng.
     *
     * @return mixed
     */
    public function searchLocation($lat, $lng, $distance = 1000, $facebook_places_id = null, $foursquare_id = null)
    {
        $params['lat'] = $lat;
        $params['lng'] = $lng;
        $params['distance'] = $distance;
        if (isset($facebook_places_id)) {
            $params['facebook_places_id'] = $facebook_places_id;
        }
        if (isset($foursquare_id)) {
            $params['foursquare_id'] = $foursquare_id;
        }

        return $this->_makeCall('locations/search', $params);
    }

    /**
     * Pagination feature.
     *
     * @param object $obj Instagram object returned by a method
     * @param int $limit Limit of returned results
     *
     * @return mixed
     *
     * @throws \MetzWeb\Instagram\InstagramException
     */
    public function pagination($obj, $limit = 0)
    {
        if (is_object($obj) && !is_null($obj->pagination)) {
            if (!isset($obj->pagination->next_url)) {
                return;
            }

            $apiCall = explode('?', $obj->pagination->next_url);

            if (count($apiCall) < 2) {
                return;
            }

            $function = str_replace(self::API_URL, '', $apiCall[0]);
            $count = ($limit) ? $limit : count($obj->data);

            if (isset($obj->pagination->next_max_tag_id)) {
                return $this->_makeCall($function, array('max_tag_id' => $obj->pagination->next_max_tag_id, 'count' => $count));
            }

            return $this->_makeCall($function, array('next_max_id' => $obj->pagination->next_max_id, 'count' => $count));
        }
        throw new InstagramException("Error: pagination() | This method doesn't support pagination.");
    }

    /**
     * Get the OAuth data of a user by the returned callback code.
     *
     * @param string $code OAuth2 code variable (after a successful login)
     * @param bool $token If it's true, only the access token will be returned
     *
     * @return mixed
     */
    public function getOAuthToken($code, $token = false)
    {
        $apiData = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->getApiKey(),
            'client_secret' => $this->getApiSecret(),
            'redirect_uri' => $this->getApiCallback(),
            'code' => $code
        );

        $result = $this->_makeOAuthCall($apiData);

        return !$token ? $result : $result->access_token;
    }

    /**
     * The call operator.
     *
     * @param string $function API resource path
     * @param array $params Additional request parameters
     * @param string $method Request type GET|POST
     *
     * @return mixed
     *
     * @throws \MetzWeb\Instagram\InstagramException
     */
    protected function _makeCall($function, $params = null, $method = 'GET')
    {
        if (!isset($this->_accesstoken)) {
            throw new InstagramException("Error: _makeCall() | $function - This method requires an authenticated users access token.");
        }

        $authMethod = '?access_token=' . $this->getAccessToken();

        $paramString = null;

        if (isset($params) && is_array($params)) {
            $paramString = '&' . http_build_query($params);
        }

        $apiCall = self::API_URL . $function . $authMethod . (('GET' === $method) ? $paramString : null);

        // we want JSON
        $headerData = array('Accept: application/json');

        if ($this->_signedheader) {
            $apiCall .= (strstr($apiCall, '?') ? '&' : '?') . 'sig=' . $this->_signHeader($function, $authMethod, $params);
        }

        $ch = curl_init();

        if ($this->_proxyServer) {
            curl_setopt($ch, CURLOPT_PROXY, $this->_proxyServer);
            curl_setopt($ch, CURLOPT_PROXYPORT,  $this->_proxyPort);
            if ($this->_proxyUser) {
                $proxyauth = $this->_proxyUser . ':' . $this->_proxyPwd;
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $apiCall);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, count($params));
                curl_setopt($ch, CURLOPT_POSTFIELDS, ltrim($paramString, '&'));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        $jsonData = curl_exec($ch);

        if (!$jsonData) {
            throw new InstagramException('Error: _makeCall() - cURL error: ' . curl_error($ch));
        }

        // split header from JSON data
        // and assign each to a variable
        list($headerContent, $jsonData) = explode("\r\n\r\n", $jsonData, 2);

        // convert header content into an array
        $headers = $this->processHeaders($headerContent);
        
        // get the 'X-Ratelimit-Remaining' header value
        if (isset($headers['X-Ratelimit-Remaining'])) {
            $this->_xRateLimitRemaining = trim($headers['X-Ratelimit-Remaining']);
        }

        curl_close($ch);

        return json_decode($jsonData);
    }

    /**
     * The OAuth call operator.
     *
     * @param array $apiData The post API data
     *
     * @return mixed
     *
     * @throws \MetzWeb\Instagram\InstagramException
     */
    private function _makeOAuthCall($apiData)
    {
        $apiHost = self::API_OAUTH_TOKEN_URL;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiHost);

        if ($this->_proxyServer) {
            curl_setopt($ch, CURLOPT_PROXY, $this->_proxyServer);
            curl_setopt($ch, CURLOPT_PROXYPORT,  $this->_proxyPort);
            if ($this->_proxyUser) {
                $proxyAuth = $this->_proxyUser . ':' . $this->_proxyPwd;
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
            }
        }

        curl_setopt($ch, CURLOPT_POST, count($apiData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $jsonData = curl_exec($ch);

        if (!$jsonData) {
            throw new InstagramException('Error: _makeOAuthCall() - cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($jsonData);
    }

    /**
     * Sign header by using endpoint, parameters and the API secret.
     *
     * @param string
     * @param string
     * @param array
     *
     * @return string The signature
     */
    private function _signHeader($endpoint, $authMethod, $params)
    {
        if (!is_array($params)) {
            $params = array();
        }
        if ($authMethod) {
            list($key, $value) = explode('=', substr($authMethod, 1), 2);
            $params[$key] = $value;
        }
        $baseString = '/' . $endpoint;
        ksort($params);
        foreach ($params as $key => $value) {
            $baseString .= '|' . $key . '=' . $value;
        }
        $signature = hash_hmac('sha256', $baseString, $this->_apisecret, false);

        return $signature;
    }

    /**
     * Read and process response header content.
     *
     * @param array
     *
     * @return array
     */
    private function processHeaders($headerContent)
    {
        $headers = array();

        foreach (explode("\r\n", $headerContent) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
                continue;
            }

            list($key, $value) = explode(':', $line);
            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Access Token Setter.
     *
     * @param object|string $data
     *
     * @return void
     */
    public function setAccessToken($data)
    {
        $token = is_object($data) ? $data->access_token : $data;

        $this->_accesstoken = $token;
    }

    /**
     * Access Token Getter.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->_accesstoken;
    }

    /**
     * API-key Setter
     *
     * @param string $apiKey
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->_apikey = $apiKey;
    }

    /**
     * API Key Getter
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apikey;
    }

    /**
     * API Secret Setter
     *
     * @param string $apiSecret
     *
     * @return void
     */
    public function setApiSecret($apiSecret)
    {
        $this->_apisecret = $apiSecret;
    }

    /**
     * API Secret Getter.
     *
     * @return string
     */
    public function getApiSecret()
    {
        return $this->_apisecret;
    }

    /**
     * API Callback URL Setter.
     *
     * @param string $apiCallback
     *
     * @return void
     */
    public function setApiCallback($apiCallback)
    {
        $this->_callbackurl = $apiCallback;
    }

    /**
     * API Callback URL Getter.
     *
     * @return string
     */
    public function getApiCallback()
    {
        return $this->_callbackurl;
    }

    /**
     * Enforce Signed Header.
     *
     * @param bool $signedHeader
     *
     * @return void
     */
    public function setSignedHeader($signedHeader)
    {
        $this->_signedheader = $signedHeader;
    }

    /**
     * Get the proxy server.
     *
     * @return string
     */
    public function getProxyServer() {
        return $this->_proxyServer;
    }

    /**
     * Set the proxy server.
     *
     * @param string $server
     *
     * @return void
     */
    public function setProxyServer($server) {
        $this->_proxyServer = $server;
    }

    /**
     * Get the proxy server username.
     *
     * @return string
     */
    public function getProxyUser(){
        return $this->_proxyUser;
    }

    /**
     * Get the proxy server password.
     *
     * @return string
     */
    public function getProxyPwd(){
        return $this->_proxyPwd;
    }

    /**
     * Set the proxy username.
     *
     * @param string $user
     *
     * @return void
     */
    public function setProxyUser($user){
        $this->_proxyUser = $user;
    }

    /**
     * Set the proxy password.
     * 
     * @param string $pwd
     *
     * @return void
     */
    public function setProxyPwd($pwd){
        $this->_proxyPwd = $pwd;
    }

    /**
     * Get proxy port.
     *
     * @return int
     */
    public function getProxyPort() {
        return $this->_proxyPort;
    }

    /**
     * Set the proxy port.
     *
     * @param int $port
     *
     * @return void
     */
    public function setProxyPort($port){
        $this->_proxyPort = $port;
    }
}

