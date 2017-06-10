<?php

namespace Zoomyboy\BaseRequest\Tests;

use Zoomyboy\BaseRequest\Tests\Providers\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase {
	public function setUp() {
		parent::setUp();

		$this->artisan('migrate', ['--database' => 'testbench']);
	}

	protected function getPackageProviders($app)
	{
		return ['Zoomyboy\BaseRequest\Tests\Providers\ServiceProvider'];
	}

	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 * @return void
	 */
	protected function getEnvironmentSetUp($app)
	{
		// Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		]);
	}

	/** @test */
	public function it_works() {
		$this->assertTrue(true);
	}
}
