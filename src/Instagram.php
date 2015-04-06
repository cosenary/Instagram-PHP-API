<?php

/*
 * This file is part of Instagram.
 *
 * (c) Christian Metz <christian-metz1@gmx.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MetzWeb\Instagram;

/**
 * This is the Instagram API class.
 *
 * @author Christian Metz <christian-metz1@gmx.net>
 */
class Instagram
{
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
     * The Instagram API Key.
     *
     * @var string
     */
    private $apiKey;

    /**
     * The Instagram OAuth API secret.
     *
     * @var string
     */
    private $apiSecret;

    /**
     * The callback URL.
     *
     * @var string
     */
    private $callbackUrl;

    /**
     * The user access token.
     *
     * @var string
     */
    private $accessToken;

    /**
     * Whether a signed header should be used.
     *
     * @var bool
     */
    private $signedHeader = false;

    /**
     * Available scopes.
     *
     * @var array
     */
    private $scopes = array('basic', 'likes', 'comments', 'relationships');

    /**
     * Available actions.
     *
     * @var array
     */
    private $actions = array('follow', 'unfollow', 'block', 'unblock', 'approve', 'deny');

    /**
     * Rate limit.
     *
     * @var int
     */
    private $xRateLimitRemaining;

    /**
     * Default constructor.
     *
     * @param string|array $config Instagram configuration data
     *
     * @return void
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
     * @param array $scope Requesting additional permissions
     *
     * @return string Instagram OAuth login URL
     * @throws \MetzWeb\Instagram\InstagramException
     */
    public function getLoginUrl($scope = array('basic'))
    {
        if (is_array($scope) && count(array_intersect($scope, $this->scopes)) === count($scope)) {
            return self::API_OAUTH_URL . '?client_id=' . $this->getApiKey() . '&redirect_uri=' . urlencode($this->getApiCallback()) . '&scope=' . implode('+', $scope) . '&response_type=code';
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
        return $this->makeCall('users/search', false, array('q' => $name, 'count' => $limit));
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
        $auth = false;

        if ($id === 0 && isset($this->accessToken)) {
            $id = 'self';
            $auth = true;
        }

        return $this->makeCall('users/' . $id, $auth);
    }

    /**
     * Get user activity feed.
     *
     * @param int $limit Limit of returned results
     *
     * @return mixed
     */
    public function getUserFeed($limit = 0)
    {
        return $this->makeCall('users/self/feed', true, array('count' => $limit));
    }

    /**
     * Get user recent media.
     *
     * @param int|string $id Instagram user ID
     * @param int $limit Limit of returned results
     *
     * @return mixed
     */
    public function getUserMedia($id = 'self', $limit = 0)
    {
        return $this->makeCall('users/' . $id . '/media/recent', strlen($this->getAccessToken()), array('count' => $limit));
    }

    /**
     * Get the liked photos of a user.
     *
     * @param int $limit Limit of returned results
     *
     * @return mixed
     */
    public function getUserLikes($limit = 0)
    {
        return $this->makeCall('users/self/media/liked', true, array('count' => $limit));
    }

    /**
     * Get the list of users this user follows
     *
     * @param int|string $id Instagram user ID.
     * @param int $limit Limit of returned results
     *
     * @return mixed
     */
    public function getUserFollows($id = 'self', $limit = 0)
    {
        return $this->makeCall('users/' . $id . '/follows', true, array('count' => $limit));
    }

    /**
     * Get the list of users this user is followed by.
     *
     * @param int|string $id Instagram user ID
     * @param int $limit Limit of returned results
     *
     * @return mixed
     */
    public function getUserFollower($id = 'self', $limit = 0)
    {
        return $this->makeCall('users/' . $id . '/followed-by', true, array('count' => $limit));
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
        return $this->makeCall('users/' . $id . '/relationship', true);
    }

    /**
     * Get the value of X-RateLimit-Remaining header field.
     *
     * @return int X-RateLimit-Remaining API calls left within 1 hour
     */
    public function getRateLimit()
    {
        return $this->xRateLimitRemaining;
    }

    /**
     * Modify the relationship between the current user and the target user.
     *
     * @param string $action Action command (follow/unfollow/block/unblock/approve/deny)
     * @param int $user Target user ID
     *
     * @return mixed
     * @throws \MetzWeb\Instagram\InstagramException
     */
    public function modifyRelationship($action, $user)
    {
        if (in_array($action, $this->actions) && isset($user)) {
            return $this->makeCall('users/' . $user . '/relationship', true, array('action' => $action), 'POST');
        }

        throw new InstagramException('Error: modifyRelationship() | This method requires an action command and the target user id.');
    }

    /**
     * Search media by its location.
     *
     * @param float $latitude Latitude of the center search coordinate
     * @param float $longitude Longitude of the center search coordinate
     * @param int $distance Distance in metres (default is 1km (distance=1000), max. is 5km)
     * @param long $minTimestamp Media taken later than this timestamp (default: 5 days ago)
     * @param long $maxTimestamp Media taken earlier than this timestamp (default: now)
     *
     * @return mixed
     */
    public function searchMedia($latitude, $longitude, $distance = 1000, $minTimestamp = null, $maxTimestamp = null)
    {
        return $this->makeCall('media/search', false, array('lat' => $latitude, 'lng' => $longitude, 'distance' => $distance, 'min_timestamp' => $minTimestamp, 'max_timestamp' => $maxTimestamp));
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
        return $this->makeCall('media/' . $id, isset($this->accessToken));
    }

    /**
     * Get the most popular media.
     *
     * @return mixed
     */
    public function getPopularMedia()
    {
        return $this->makeCall('media/popular');
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
        return $this->makeCall('tags/search', false, array('q' => $name));
    }

    /**
     * Get info about a tag.
     *
     * @param string $name Valid tag name
     *
     * @return mixed
     */
    public function getTag($name)
    {
        return $this->makeCall('tags/' . $name);
    }

    /**
     * Get a recently tagged media.
     *
     * @param string $name Valid tag name
     * @param int $limit Limit of returned results
     *
     * @return mixed
     */
    public function getTagMedia($name, $limit = 0)
    {
        return $this->makeCall('tags/' . $name . '/media/recent', false, array('count' => $limit));
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
        return $this->makeCall('media/' . $id . '/likes', true);
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
        return $this->makeCall('media/' . $id . '/comments', false);
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
        return $this->makeCall('media/' . $id . '/comments', true, array('text' => $text), 'POST');
    }

    /**
     * Remove user comment on a media.
     *
     * @param int $id Instagram media ID
     * @param string $commentId User comment ID
     *
     * @return mixed
     */
    public function deleteMediaComment($id, $commentId)
    {
        return $this->makeCall('media/' . $id . '/comments/' . $commentId, true, null, 'DELETE');
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
        return $this->makeCall('media/' . $id . '/likes', true, null, 'POST');
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
        return $this->makeCall('media/' . $id . '/likes', true, null, 'DELETE');
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
        return $this->makeCall('locations/' . $id, false);
    }

    /**
    * Get recent media from a given location.
    *
    * @param int $id Instagram location ID
    *
    * @return mixed
    */
    public function getLocationMedia($id)
    {
        return $this->makeCall('locations/' . $id . '/media/recent', false);
    }

    /**
     * Get recent media from a given location.
     *
     * @param float $latitude                    Latitude of the center search coordinate
     * @param float $longitude                    Longitude of the center search coordinate
     * @param int $distance  Distance in meter (max. distance: 5km = 5000)
     *
     * @return mixed
     */
    public function searchLocation($latitude, $longitude, $distance = 1000)
    {
        return $this->makeCall('locations/search', false, array('lat' => $latitude, 'lng' => $longitude, 'distance' => $distance));
    }

    /**
     * Pagination feature.
     *
     * @param object $obj Instagram object returned by a method
     * @param int $limit Limit of returned results
     *
     * @return mixed
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
            $auth = (strpos($apiCall[1], 'access_token') !== false);

            if (isset($obj->pagination->next_max_id)) {
                return $this->makeCall($function, $auth, array('max_id' => $obj->pagination->next_max_id, 'count' => $limit));
            }

            return $this->makeCall($function, $auth, array('cursor' => $obj->pagination->next_cursor, 'count' => $limit));
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
     * @param bool $auth Whether the function requires an access token
     * @param array $params Additional request parameters
     * @param string $method Request type GET|POST
     *
     * @return mixed
     * @throws \MetzWeb\Instagram\InstagramException
     */
    protected function makeCall($function, $auth = false, $params = null, $method = 'GET')
    {
        if (!$auth) {
            // if the call doesn't requires authentication
            $authMethod = '?client_id=' . $this->getApiKey();
        } else {
            // if the call needs an authenticated user
            if (!isset($this->accessToken)) {
                throw new InstagramException("Error: makeCall() | $function - This method requires an authenticated users access token.");
            }

            $authMethod = '?access_token=' . $this->getAccessToken();
        }

        $paramString = null;

        if (isset($params) && is_array($params)) {
            $paramString = '&' . http_build_query($params);
        }

        $apiCall = self::API_URL . $function . $authMethod . (('GET' === $method) ? $paramString : null);

        // signed header of POST/DELETE requests
        $headerData = array('Accept: application/json');
        if ($this->signedHeader && 'GET' !== $method) {
            $headerData[] = 'X-Insta-Forwarded-For: ' . $this->_signHeader();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiCall);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, ltrim($paramString, '&'));
        } elseif ('DELETE' === $method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $jsonData = curl_exec($ch);

        // split header from JSON data
        // and assign each to a variable
        list($headerContent, $jsonData) = explode("\r\n\r\n", $jsonData, 2);

        // convert header content into an array
        $headers = $this->processHeaders($headerContent);

        // get the 'X-Ratelimit-Remaining' header value
        $this->xRateLimitRemaining = $headers['X-Ratelimit-Remaining'];

        if (!$jsonData) {
            throw new InstagramException('Error: makeCall() - cURL error: ' . curl_error($ch));
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
     * @throws \MetzWeb\Instagram\InstagramException
     */
    private function _makeOAuthCall($apiData)
    {
        $apiHost = self::API_OAUTH_TOKEN_URL;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiHost);
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
     * Sign header by using the app's IP and its API secret.
     *
     * @return string
     */
    private function _signHeader()
    {
        $ipAddress = (isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : gethostbyname(gethostname());

        $signature = hash_hmac('sha256', $ipAddress, $this->apiSecret, false);

        return join('|', array($ipAddress, $signature));
    }

    /**
     * Read and process response header content.
     *
     * @param array $headerContent
     *
     * @return array
     */
    private function processHeaders($headerContent)
    {
        $headers = array();

        foreach (explode("\r\n", $headerContent) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list($key, $value) = explode(':', $line);
                $headers[$key] = $value;
            }
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

        $this->accessToken = $token;
    }

    /**
     * Access Token Getter.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * API-key Setter.
     *
     * @param string $apiKey
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * API Key Getter.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * API Secret Setter.
     *
     * @param string $apiSecret
     *
     * @return void
     */
    public function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }

    /**
     * API Secret Getter.
     *
     * @return string
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * API Callback URL Setter.
     *
     * @param string $apiCallback
     * @return void
     */
    public function setApiCallback($apiCallback)
    {
        $this->callbackUrl = $apiCallback;
    }

    /**
     * API Callback URL Getter.
     *
     * @return string
     */
    public function getApiCallback()
    {
        return $this->callbackUrl;
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
        $this->signedHeader = $signedHeader;
    }
}
