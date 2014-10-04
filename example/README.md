# Example project

### Get started

First of all, [register your application](http://instagr.am/developer/register/) at Instagram. You will receive a `client_id` and a `client_secret`.

**Important:** Your *OAuth redirect uri* must point to the `success.php` file on your webserver.

Finally, set your credentials in the example project files (`index.php`, `success.php`, `popular.php`):

```php
$instagram = new Instagram(array(
  'apiKey'      => 'YOUR_APP_KEY',
  'apiSecret'   => 'YOUR_APP_SECRET',
  'apiCallback' => 'YOUR_APP_CALLBACK'
));
```

### Authenticated (user) endpoints

This example project guides you through the required steps of the OAuth2 login process. It includes a very basic session handling.

After successfully logging in (`index.php`) you will see your personal Instagram media stream. Videos are supported (HTML5 mobile friendly).

The `success.php` page is a good place to test the class different [user methods](../README.md#user-methods).

Please note that some methods require additional permissions. An overview of the available scopes can be found here: [Instagram scopes](../README.md#get-login-url)

![Image](http://cl.ly/image/221T1g3w3u2J/preview.png)

### Public endpoints

The `popular.php` page demonstrates how to use the Instagram API class to communicate with the **public** endpoints.

### Contribute

If you've done improvements or added some fancy features, please share them and submit a pull-request. Thanks :)

---

Credit for the awesome Instagram icons goes to [Ricardo de Zoete Pro](http://dribbble.com/RZDESIGN).