# Instagram PHP API #

## About ##

This is my first PHP class, so please bear with me.
Any feedback and bugs is appreciated.

## Requirements ##

- PHP 5.2.x or higher
- cURL
- Registered Instagram App

## Get started ##

[Register your application](http://instagr.am/developer/register/) with Instagram, and receive your OAuth `client_id` and `client_secret`.
Take a look at the [uri guidlines](#redirect-uri) before registering a Redirect URI.

### Initialize the class ###

    <?php
        require_once 'instagram.class.php';
        
        $instagram = new Instagram(array(
          'apiKey'      => 'YOUR_APP_KEY',
          'apiSecret'   => 'YOUR_APP_SECRET',
          'apiCallback' => 'YOUR_APP_CALLBACK'
        ));
        
        echo "<a href='{$instagram->getLoginUrl()}'>Login with Instagram</a>";
    ?>

### Authenticate user (OAuth2) ###

    <?php
        // Grab user token
        $code = $_GET['code'];
        $userToken = $instagram->getOAuthToken($code);
        
        echo 'Your username is: '.$userToken->user->username;
    ?>

### Get user likes ###

    <?php
        // Get the last two likes
        $likes = $instagram->getUserLikes($userToken->access_token, 2);
        
        // Take a look at the API response
        echo '<pre>';
        print_r($likes);
        echo '<pre>';
    ?>

## Available methods ##

### Get login URL ###

`getLoginUrl(<array>)`

**Available scope parameters:**

> `basic` *[default]*, `likes`, `comments`, `relationships`

### Get token ###

`getOAuthToken($code, <true/false>)`

`true` : Returns only the OAuth token
`false` *[default]* : Returns OAuth token and Instagram user data

### Get user likes ###

`getUserLikes($token, $limit)`

### Further endpoints ###

It's planed always to extend the class with new methods.
Let me know, if you think that one of the missing endpoints has a especially priority.

**Missing Endpoints:**

> `Media`, `Likes`, `Relationships`, `Comments`, `Tags`, `Locations`, `Geographies`

## Samples for redirect URLs ##         {#redirect-uri}

<table>
  <tr>
    <th>Registered Redirect URI</th>
    <th>Redirect URI sent to /authorize</th>
    <th>Valid?</th>
  </tr>
  <tr>
    <td>http://yourcallback.com/</td>
    <td>http://yourcallback.com/</td>
    <td>yes</td>
  </tr>
  <tr>
    <td>http://yourcallback.com/</td>
    <td>http://yourcallback.com/?this=that</td>
    <td>yes</td>
  </tr>
  <tr>
    <td>http://yourcallback.com/?this=that</td>
    <td>http://yourcallback.com/</td>
    <td>no</td>
  </tr>
  <tr>
    <td>http://yourcallback.com/?this=that</td>
    <td>http://yourcallback.com/?this=that&another=true</td>
    <td>yes</td>
  </tr>
  <tr>
    <td>http://yourcallback.com/?this=that</td>
    <td>http://yourcallback.com/?another=true&this=that</td>
    <td>no</td>
  </tr>
  <tr>
    <td>http://yourcallback.com/callback</td>
    <td>http://yourcallback.com/</td>
    <td>no</td>
  </tr>
  <tr>
    <td>http://yourcallback.com/callback</td>
    <td>http://yourcallback.com/callback/?type=mobile</td>
    <td>yes</td>
  </tr>
</table>
**<sub>[original developer source](http://instagram.com/developer/auth)</sub>**

## History ##

**Instagram 0.8 - 16/11/2011**

- `release` First inital released version
- `feature` You can set the config data with an array (see example)

**Instagram 0.5 - 12/11/2011**

- `release` First inital released version 
