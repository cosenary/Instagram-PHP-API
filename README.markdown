# Instagram PHP API #

## What it does ##

## Get started ##

[Register your application](http://instagr.am/developer/register/) with Instagram, and receive your OAuth <code>client_id</code> and <code>client_secret</code>.

## Requirements ##

- PHP 5.2.x or higher
- cURL
- TMDb API-key

### Initialize the class ###

    <?php
      require_once 'instagram.class.php';
      
      $ig = new Instagram('Client ID', 'Client Secret', 'Callback URL');
      
      // Display login URL
      echo "<a href='{$ig->getLoginUrl()}'>Login with Instagram</a>";
    ?>

### Authenticate user (OAuth2) ###

    <?php
      // Grab user token
      $code = $_GET['code'];
      $userToken = $ig->getOAuthToken($code);
      
      echo 'Your username is: '.$userToken->user->username;
    ?>

### Get user likes ###

    <?php
      // Get the last two likes
      $likes = getUserLikes($userToken->access_token, 2);
      
      // Take a look at the API response
      echo '<pre>';
      print_r($likes);
      echo '<pre>';
    ?>

## Available methods ##

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