<?php

namespace Zoomyboy\BaseRequest\Tests\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {
	public function boot() {
		$this->loadMigrationsFrom(__DIR__.'/../Migrations');
	}
}
