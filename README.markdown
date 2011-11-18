# Instagram PHP API #

## About ##

A PHP wrapper for the Instagram API.
This is my first PHP class, so please bear with me.
Any feedback or bug reports are appreciated.

## Requirements ##

- PHP 5.2.x or higher
- cURL
- Registered Instagram App

## Get started ##

[Register your application](http://instagr.am/developer/register/) with Instagram, and receive your OAuth `client_id` and `client_secret`.
Take a look at the [uri guidlines](#redirect-uri) before registering a Redirect URI.
> **A good place to get started is the example App.**

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
        // Grab OAuth callback code
        $code = $_GET['code'];
        $userData = $instagram->getOAuthToken($code);
        
        echo 'Your username is: '.$userData->user->username;
    ?>

### Get user likes ###

    <?php
        // Store user access token
        $token = $userData->access_token;
        $instagram->setAccessToken($token);
        
        // Get the last two likes
        $likes = $instagram->getUserLikes($token, 2);
        
        // Take a look at the API response
        echo '<pre>';
        print_r($likes);
        echo '<pre>';
    ?>

**All methods return the API data `json_decode()` - so you can directly access the data.**

## Available methods ##

### Setup Instagram ###

`new Instagram(<array>/<string>)`

`array` if you want to authenticate a user and access it's data:

    new Instagram(array(
      'apiKey'      => 'YOUR_APP_KEY',
      'apiSecret'   => 'YOUR_APP_SECRET',
      'apiCallback' => 'YOUR_APP_CALLBACK'
    ));

`string` if you *only* want to access public data:

    new Instagram('YOUR_APP_KEY')

### Get login URL ###

`getLoginUrl(<array>)`

**Available scope parameters:**

`basic` *[default]*, `likes`, `comments`, `relationships`

### Get OAuth token ###

`getOAuthToken($code, <true>/<false>)`

`true` : Returns only the OAuth token, that you can directly pass into `setAccessToken()`
`false` *[default]* : Returns OAuth token and profile data of the authenticated user

### Set / Get access token ###

Stores the access token, for further method calls:
`setAccessToken($token)`

Returns the access token, if you want to store it for later usage:
`getAccessToken()`

### User methods ###

**Public methods**

- `searchUser($name)`
- `getUser($id)`

**Authenticated user methods**

- `getUserLikes()`
- `getUserFeed()`
- `getUserMedia($id)`

### Media methods ###

**Public methods**

- `getMedia($id)`
- `getPopularMedia()`

### Further endpoints ###

It's planned always to extend the class with new methods.
Let me know, if you think that one of the missing endpoints has a especially priority.

**Missing Endpoints:**

`Media`, `Likes`, `Relationships`, `Comments`, `Tags`, `Locations`, `Geographies`

For all parameters in the configuration array exists a public setter and getter method.

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
**<sub>If you need additional informations, take a look at [Instagrams API docs](http://instagram.com/developer/auth).</sub>**

## Example App ##

The small App, which is located in the `example/` folder, helps you to get started with the class.
Its whole code is documented and will take you through all steps of the OAuth2 process.
The great Instagram Sign In button is designed by [Murat Mutlu](http://twitter.com/mutlu82/).

## History ##

**Instagram 0.8 - 16/11/2011**

- `release` First inital released version
- `feature` Added sample App with documented code
- `feature` Initialize the class with a config array or string (see example)
- `update` New detailed documentation

**Instagram 0.5 - 12/11/2011**

- `release` Beta version
- `update` Small documentation

## Credits ##

Copyright (c) 2011 - Programmed by Christian Metz
Released under the [BSD License](http://www.opensource.org/licenses/bsd-license.php).