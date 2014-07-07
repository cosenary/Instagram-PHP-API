<?php namespace Andreyco\InstagramPHP\Support\Laravel;

use Andreyco\InstagramPHP\Client;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('andreyco/instagramphp');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('andreyco.instagramphp', function($app) {
			return new Client([
               'apiKey'      => $app['config']->get('instagramphp::config.clientId'),
               'apiSecret'   => $app['config']->get('instagramphp::config.clientSecret'),
               'apiCallback' => $app['config']->get('instagramphp::config.redirectUri'),
           ]);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('andreyco.instagramphp');
	}

}
