<?php

require 'instagram.class.php';

// Initialize class
$instagram = new Instagram(array(
  'apiKey'      => '1b293f9782834b098460f1fb44d15638',
  'apiSecret'   => '7b252796ae024a82ab07a8ee24eedf03',
  'apiCallback' => 'http://metzweb.net/labs/instagram/success.php'
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

  // Take a look at the API response (already json decoded)
  echo '<pre>';
  print_r($likes);
  echo '<pre>';

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