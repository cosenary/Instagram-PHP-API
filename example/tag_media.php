<?php

require 'instagram.class.php';

// Initialize class for public requests
$instagram = new Instagram('YOUR_APP_KEY');

$tag = 'winter';

// Get recently tagged media
$media = json_decode($instagram->getTagMedia($tag));

// Display results
foreach ($media->data as $data) {
  echo "<img src=\"{$data->images->thumbnail->url}\">";
}

?>