<?php

namespace Zoomyboy\BaseRequest\Tests\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Zoomyboy\BaseRequest\Tests\Migrator;

class ServiceProvider extends BaseServiceProvider {
	public function boot() {
		$this->loadMigrationsFrom(__DIR__.'/../Migrations');
	}
}
