<?php

require 'instagram.class.php';

// Initialize class
$instagram = new Instagram(array(
  'apiKey'      => 'YOUR_APP_KEY',
  'apiSecret'   => 'YOUR_APP_SECRET',
  'apiCallback' => 'YOUR_APP_CALLBACK'
));

// Receive OAuth code parameter
$authCode = $_GET['code'];

// Check whether the user granted access
if (true === isset($authCode)) {

  // Get informations about the authenticated user
  $userData = $instagram->getOAuthToken($authCode);
  echo 'Your username is: '.$userData->user->username;

  // Store user access token
  $token = $userData->access_token;
  $instagram->setAccessToken($token);

  // Get all user likes
  $likes = $instagram->getUserLikes();

  // Display all user likes
  foreach ($likes->data as $entry) {
    echo "<img src=\"{$entry->images->thumbnail->url}\">";
  }

} else {

  // Check whether an error occurred
  if (true === isset($_GET['error'])) {
    echo 'An error occurred: '.$_GET['error_description'];
  }

}

?>