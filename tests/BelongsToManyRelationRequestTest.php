<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use \Zoomyboy\BaseRequest\Tests\Requests\UserRequest;
use \Zoomyboy\BaseRequest\Handler;
use \Zoomyboy\BaseRequest\Tests\Models\User;
use \Zoomyboy\BaseRequest\Tests\Models\Right;

class BelongsToManyRelationRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_gets_fillable_attributes_without_the_belongstomany_relation() {
		$handler = new Handler (new user(), ['name' => 'User Name', 'rights' => [2,5]]);
		$this->assertEquals(['name' => 'User Name'], $handler->getFillInput());
	}

	/** @test */
	public function it_gets_all_the_fillable_vars_to_set() {
		$handler = new Handler (new user(), ['name' => 'User Name']);
		$this->assertEquals(['name' => 'User Name'], $handler->getFillInput());
	}

	/** @test */
	public function it_gets_all_the_belongstomany_vars_to_set() {
		$handler = new Handler (new user(), ['name' => 'user name', 'rights' => [2,5]]);
		$this->assertEquals(['rights' => [2,5]], $handler->getBelongsToManyValues());
	}

	/** @test */
	public function it_attaches_existing_related_models_to_a_new_saving_model() {
		$rights = collect([
			Right::create(['title' => 'view']),
			Right::create(['title' => 'edit']),
			Right::create(['title' => 'delete'])
		]);

		$user = User::create(['name' => 'user name']);

		$handler = new Handler($user, ['rights' => $rights->only([0,1])->pluck('id')->toArray()]);

		$handler->createBelongsToMany();

		$this->assertEquals(
			[$rights[0]->id,$rights[1]->id],
			$handler->model->rights->pluck('id')->toArray()
		);
	}

	/**
	 * @test
	 * @expectedException Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public function it_throws_exception_if_related_model_wasnt_found() {
		$rights = collect([
			Right::create(['title' => 'view']),
			Right::create(['title' => 'edit']),
			Right::create(['title' => 'delete'])
		]);

		$user = User::create(['name' => 'user name']);

		$handler = new Handler($user, ['rights' => [999]]);

		$handler->createBelongsToMany();
	}

	/** @test */
	public function it_attaches_existing_related_models_to_a_new_request() {
		$rights = collect([
			Right::create(['title' => 'view']),
			Right::create(['title' => 'edit']),
			Right::create(['title' => 'delete'])
		]);

		$handler = new Handler(new User(), [
			'name' => 'user name',
			'rights' => $rights->only([0,1])->pluck('id')->toArray()
		]);
		$user = $handler->handle();

		$this->assertEquals(
			[$rights[0]->id,$rights[1]->id],
			$user->rights->pluck('id')->toArray()
		);
	}
}
