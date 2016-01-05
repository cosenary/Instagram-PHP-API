# ![Image](example/assets/instagram.png) Instagram PHP API V2

> **Note:** On the 17 Nov 2015 [Instagram](http://developers.instagram.com/post/133424514006/instagram-platform-update) made [changes to their API ](https://instagram.com/developer/changelog/). Apps created before Nov 17, 2015 wont be affected until Jun 2016. Apps created on or after Nov 17 2015 will require to use their updated API. Please note that this library doesn't yet support their new updates. For more information, please see [#182](https://github.com/cosenary/Instagram-PHP-API/issues/182).

A PHP wrapper for the Instagram API. Feedback or bug reports are appreciated.

[![Total Downloads](http://img.shields.io/packagist/dm/cosenary/instagram.svg?style=flat)](https://packagist.org/packages/cosenary/instagram)
[![Latest Stable Version](http://img.shields.io/packagist/v/cosenary/instagram.svg?style=flat)](https://packagist.org/packages/cosenary/instagram)
[![License](https://img.shields.io/packagist/l/cosenary/instagram.svg?style=flat)](https://packagist.org/packages/cosenary/instagram)

> [Composer](#installation) package available.  
> Supports [Instagram Video](#instagram-videos) and [Signed Header](#signed-header).

## Requirements

- PHP 5.3 or higher
- cURL
- Registered Instagram App

## Get started

To use the Instagram API you have to register yourself as a developer at the [Instagram Developer Platform](http://instagr.am/developer/register/) and create an application. Take a look at the [uri guidelines](#samples-for-redirect-urls) before registering a redirect URI. You will receive your `client_id` and `client_secret`.

---

Please note that Instagram mainly refers to »Clients« instead of »Apps«. So »Client ID« and »Client Secret« are the same as »App Key« and »App Secret«.

---

> A good place to get started is the [example project](example/README.md).

### Installation

I strongly advice using [Composer](https://getcomposer.org) to keep updates as smooth as possible.

```
$ composer require cosenary/instagram
```

### Initialize the class

```php
use MetzWeb\Instagram\Instagram;

$instagram = new Instagram(array(
	'apiKey'      => 'YOUR_APP_KEY',
	'apiSecret'   => 'YOUR_APP_SECRET',
	'apiCallback' => 'YOUR_APP_CALLBACK'
));

echo "<a href='{$instagram->getLoginUrl()}'>Login with Instagram</a>";
```

### Authenticate user (OAuth2)

```php
// grab OAuth callback code
$code = $_GET['code'];
$data = $instagram->getOAuthToken($code);

echo 'Your username is: ' . $data->user->username;
```

### Get user likes

```php
// set user access token
$instagram->setAccessToken($data);

// get all user likes
$likes = $instagram->getUserLikes();

// take a look at the API response
echo '<pre>';
print_r($likes);
echo '<pre>';
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

- Set the access token, for further method calls: `setAccessToken($token)`
- Get the access token, if you want to store it for later usage: `getAccessToken()`

### User methods

**Public methods**

- `getUser($id)`
- `searchUser($name, <$limit>)`
- `getUserMedia($id, <$limit>)`

**Authenticated methods**

- `getUser()`
- `getUserLikes(<$limit>)`
- `getUserFeed(<$limit>)`
- `getUserMedia(<$id>, <$limit>)`
	- if an `$id` isn't defined or equals `'self'`, it returns the media of the logged in user

> [Sample responses of the User Endpoints.](http://instagram.com/developer/endpoints/users/)

### Relationship methods

**Authenticated methods**

- `getUserFollows($id, <$limit>)`
- `getUserFollower($id, <$limit>)`
- `getUserRelationship($id)`
- `modifyRelationship($action, $user)`
	- `$action` : Action command (follow / unfollow / block / unblock / approve / deny)
	- `$user` : Target user id

```php
// Follow the user with the ID 1574083
$instagram->modifyRelationship('follow', 1574083);
```

---

Please note that the `modifyRelationship()` method requires the `relationships` [scope](#get-login-url).

---

> [Sample responses of the Relationship Endpoints.](http://instagram.com/developer/endpoints/relationships/)

### Media methods

**Public methods**

- `getMedia($id)`
	- authenticated users receive the info, whether the queried media is liked
- `getPopularMedia()`
- `searchMedia($lat, $lng, <$distance>, <$minTimestamp>, <$maxTimestamp>)`
	- `$lat` and `$lng` are coordinates and have to be floats like: `48.145441892290336`,`11.568603515625`
	- `$distance` : Radial distance in meter (default is 1km = 1000, max. is 5km = 5000)
	- `$minTimestamp` : All media returned will be taken *later* than this timestamp (default: 5 days ago)
	- `$maxTimestamp` : All media returned will be taken *earlier* than this timestamp (default: now)

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

All `<...>` parameters are optional. If the limit is undefined, all available results will be returned.

## Instagram videos

Instagram entries are marked with a `type` attribute (`image` or `video`), that allows you to identify videos.

An example of how to embed Instagram videos by using [Video.js](http://www.videojs.com), can be found in the `/example` folder.

---

**Please note:** Instagram currently doesn't allow to filter videos.

---

## Signed Header

In order to prevent that your access tokens gets stolen, Instagram recommends to sign your requests with a hash of your API secret, the called endpoint and parameters.

1. Activate ["Enforce Signed Header"](http://instagram.com/developer/clients/manage/) in your Instagram client settings.
2. Enable the signed-header in your Instagram class:

```php
$instagram->setSignedHeader(true);
```

3. You are good to go! Now, all your requests will be secured with a signed header.

Go into more detail about how it works in the [Instagram API Docs](http://instagram.com/developer/restrict-api-requests/#enforce-signed-header).

## Pagination

Each endpoint has a maximum range of results, so increasing the `limit` parameter above the limit won't help (e.g. `getUserMedia()` has a limit of 90).

That's the point where the "pagination" feature comes into play.
Simply pass an object into the `pagination()` method and receive your next dataset:

```php
$photos = $instagram->getTagMedia('kitten');

$result = $instagram->pagination($photos);
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

![Image](http://cl.ly/image/221T1g3w3u2J/preview.png)

This example project, located in the `example/` folder, helps you to get started.
The code is well documented and takes you through all required steps of the OAuth2 process.
Credit for the awesome Instagram icons goes to [Ricardo de Zoete Pro](http://dribbble.com/RZDESIGN).

#### More examples and tutorials:

- [User likes](https://gist.github.com/cosenary/3287237)
- [Follow user](https://gist.github.com/cosenary/8322459)
- [User follower](https://gist.github.com/cosenary/7267139)
- [Load more button](https://gist.github.com/cosenary/2975779)
- [User most recent media](https://gist.github.com/cosenary/1711218)
- [Instagram login](https://gist.github.com/cosenary/8803601)
- [Instagram signup (9lessons tutorial)](http://www.9lessons.info/2012/05/login-with-instagram-php.html)
- [Laravel Wrapper](https://github.com/vinkla/instagram)

> Let me know if you have to share a code example, too.

## Changelog

Please see the [changelog file](CHANGELOG.md) for more information.

## Credits

Copyright (c) 2011-2015 - Programmed by Christian Metz

Released under the [BSD License](LICENSE).
