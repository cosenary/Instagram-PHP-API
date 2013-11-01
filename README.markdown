# Instagram PHP API

## About

A PHP wrapper for the Instagram API.  
Feedback or bug reports are appreciated.

> Supports [Instagram video](#instagram-videos) responses.

## Requirements

- PHP 5.2.x or higher
- cURL
- Registered Instagram App

## Get started

[Register your application](http://instagr.am/developer/register/) with Instagram, and receive your OAuth `client_id` and `client_secret`.  
Take a look at the [uri guidlines](#samples-for-redirect-urls) before registering a redirect URI.

> A good place to get started is the example App.

### Initialize the class

```php
<?php
    require_once 'instagram.class.php';
    
    $instagram = new Instagram(array(
      'apiKey'      => 'YOUR_APP_KEY',
      'apiSecret'   => 'YOUR_APP_SECRET',
      'apiCallback' => 'YOUR_APP_CALLBACK'
    ));
    
    echo "<a href='{$instagram->getLoginUrl()}'>Login with Instagram</a>";
?>
```

### Authenticate user (OAuth2)

```php
<?php
    // Grab OAuth callback code
    $code = $_GET['code'];
    $data = $instagram->getOAuthToken($code);
    
    echo 'Your username is: ' . $data->user->username;
?>
```

### Get user likes

```php
<?php
    // Store user access token
    $instagram->setAccessToken($data);
    
    // Get all user likes
    $likes = $instagram->getUserLikes();
    
    // Take a look at the API response
    echo '<pre>';
    print_r($likes);
    echo '<pre>';
?>
```

**All methods return the API data `json_decode()` - so you can directly access the data.**

## Available methods

### Setup Instagram

`new Instagram(<array>/<string>);`

`array` if you want to authenticate a user and access its data:

```php
new Instagram(array(
  'apiKey'      => 'YOUR_APP_KEY',
  'apiSecret'   => 'YOUR_APP_SECRET',
  'apiCallback' => 'YOUR_APP_CALLBACK'
));
```

`string` if you *only* want to access public data:

```php
new Instagram('YOUR_APP_KEY');
```

### Get login URL

`getLoginUrl(<array>)`

```php
getLoginUrl(array(
  'basic',
  'likes'
));
```

**Optional scope parameters:**

<table>
  <tr>
    <th>Scope</th>
    <th>Legend</th>
    <th>Methods</th>
  </tr>
  <tr>
    <td><code>basic</code></td>
    <td>to use all user related methods [default]</td>
    <td><code>getUser()</code>, <code>getUserFeed()</code>, <code>getUserFollower()</code> etc.</td>
  </tr>
  <tr>
    <td><code>relationships</code></td>
    <td>to follow and unfollow users</td>
    <td><code>modifyRelationship()</code></td>
  </tr>
  <tr>
    <td><code>likes</code></td>
    <td>to like and unlike items</td>
    <td><code>getMediaLikes()</code>, <code>likeMedia()</code>, <code>deleteLikedMedia()</code></td>
  </tr>
  <tr>
    <td><code>comments</code></td>
    <td>to create or delete comments</td>
    <td><code>getMediaComments()</code>, <code>addMediaComment()</code>, <code>deleteMediaComment()</code></td>
  </tr>
</table>

### Get OAuth token

`getOAuthToken($code, <true>/<false>)`

`true` : Returns only the OAuth token  
`false` *[default]* : Returns OAuth token and profile data of the authenticated user

### Set / Get access token

Stores access token, for further method calls:  
`setAccessToken($token)`

Returns access token, if you want to store it for later usage:  
`getAccessToken()`

### User methods

**Public methods**

- `getUser($id)`
- `searchUser($name, <$limit>)`

**Authenticated methods**

- `getUser()`
- `getUserLikes(<$limit>)`
- `getUserFeed(<$limit>)`
- `getUserMedia(<$id>, <$limit>)`
    - if an `$id` isn't defined, it returns the media of the logged in user

> [Sample responses of the User Endpoints.](http://instagram.com/developer/endpoints/users/)

### Relationship methods

**Authenticated methods**

- `getUserFollows(<$id>, <$limit>)`
- `getUserFollower(<$id>, <$limit>)`
- `getUserRelationship($id)`
- `modifyRelationship($action, $user)`
    - `$action` : Action command (follow / unfollow / block / unblock / approve / deny)
    - `$user` : Target user id

```php
<?php
    // Follow the user with the ID 1574083
    $instagram->modifyRelationship('follow', 1574083);
?>
```

---

Please note that the `modifyRelationship()` method requires the `relationships` [scope](#get-login-url).

---

> [Sample responses of the Relationship Endpoints.](http://instagram.com/developer/endpoints/relationships/)

### Media methods

**Public methods**

- `getMedia($id)`
- `getPopularMedia()`
- `searchMedia($lat, $lng, <$distance>)`
    - `$lat` and `$lng` are coordinates and have to be floats like: `48.145441892290336`,`11.568603515625`
    - `$distance` Radial distance in meter (max. distance: 5km = 5000)

All `<$limit>` parameters are optional. If the limit is undefined, all available results will be returned.

> [Sample responses of the Media Endpoints.](http://instagram.com/developer/endpoints/media/)

### Comment methods

**Public methods**

- `getMediaComments($id)`

**Authenticated methods**

- `addMediaComment($id, $text)`
    - **restricted access:** please email `apidevelopers[at]instagram.com` for access
- `deleteMediaComment($id, $commentID)`
    - the comment must be authored by the authenticated user

---

Please note that the authenticated methods require the `comments` [scope](#get-login-url).

---

> [Sample responses of the Comment Endpoints.](http://instagram.com/developer/endpoints/comments/)

### Tag methods

**Public methods**

- `getTag($name)`
- `getTagMedia($name)`
- `searchTags($name)`

> [Sample responses of the Tag Endpoints.](http://instagram.com/developer/endpoints/tags/)

### Likes methods

**Authenticated methods**

- `getMediaLikes($id)`
- `likeMedia($id)`
- `deleteLikedMedia($id)`

> How to like a Media: [Example usage](https://gist.github.com/3287237)  
> [Sample responses of the Likes Endpoints.](http://instagram.com/developer/endpoints/likes/)

### Further endpoints

It's planned to extend the class with new methods.
Let me know, if you think, that one of the missing endpoints has priority.

**Missing Endpoints:**

`Locations`, `Geographies`

For all parameters in the configuration array exists a public setter and getter method.

## Instagram videos

`isVideo()` indicated by the `type` value.

```
coming with version 2.0 beta 3
```

## Pagination

Each endpoint has a maximum range of results, so increasing the `limit` parameter above the limit won't help (e.g. `getUserMedia()` has a limit of 90).

That's the point where the "pagination" feature comes into play.  
Simply pass an object into the `pagination()` method and receive your next dataset:

```php
<?php
    $photos = $instagram->getTagMedia('kitten');

    $result = $instagram->pagination($photos);
?>
```

Iteration with `do-while` loop.

## Samples for redirect URLs

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

> If you need further information about an endpoint, take a look at the [Instagram API docs](http://instagram.com/developer/authentication/).

## Example App

To get started with the class, take a look at the example App, located in the `example/` folder.  
Its code is well documented and will take you through all required steps, of the OAuth2 process.  
The great Instagram Sign In button is designed by [Murat Mutlu](http://twitter.com/mutlu82/).

A short tutorial, about how to build an Instagram login with my class, has been published at [9lessons](http://www.9lessons.info/2012/05/login-with-instagram-php.html).

## History ##

> Version 2.0 is in development.  
> Your feedback is always welcome!

**Instagram 2.0 alpha 3 - 01/11/2013**

- `feature` Comment endpoint implemented
- `update` Improved documentation
- `draft` New example with a fancy GUI

**Instagram 2.0 alpha 2 - 04/09/2013**

- `merge` Merged master branch updates
    - `update` Updated documentation
    - `bug` / `change` cURL CURLOPT_SSL_VERIFYPEER disabled (fixes #6, #7, #8, #16)
    - `feature` Added cURL error message
    - `feature` Added `limit` to `getTagMedia()` method

**Instagram 2.0 alpha 1 - 14/06/2013**

- `feature` Improved Pagination functionality
- `change` Added `distance` parameter to `searchMedia()` method (thanks @jonathanwkelly)

**Instagram 1.6 alpha - 28/05/2012**

- `feature` Added Pagination method
- `feature` Added User Relationship endpoints
- `feature` Added scope parameter table for the `getLoginUrl()` method

**Instagram 1.5 - 31/01/2012**

- `release` Second master version
- `feature` Added Tag endpoints
- `change` Edited the "Get started" example
- `change` Now you can pass the `getOAuthToken()` object directly into `setAccessToken()`

**Instagram 1.0 - 20/11/2011**

- `release` First public release
- `feature` Added sample App with documented code
- `update` New detailed documentation

**Instagram 0.8 - 16/11/2011**

- `release` First inital released version
- `feature` Initialize the class with a config array or string (see example)

**Instagram 0.5 - 12/11/2011**

- `release` Beta version
- `update` Small documentation

## Credits

Copyright (c) 2011-2013 - Programmed by Christian Metz  
Released under the [BSD License](http://www.opensource.org/licenses/bsd-license.php).
