<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use \Zoomyboy\BaseRequest\Tests\Requests\UserRequest;
use \Zoomyboy\BaseRequest\Tests\Models\User;
use \Zoomyboy\BaseRequest\Tests\Models\Right;

class BelongsToManyRelationRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_gets_fillable_attributes_without_the_belongstomany_relation() {
		$request = new UserRequest();
		$request->replace(['name' => 'User Name', 'rights' => [2,5]]);
		$this->assertEquals(['name' => 'User Name'], $request->getFillInput());
	}

	/** @test */
	public function it_gets_all_the_fillable_vars_to_set() {
		$request = new UserRequest();
		$request->replace(['name' => 'user name']);
		$this->assertEquals(['name' => 'user name'], $request->getFillInput());
	}

	/** @test */
	public function it_gets_all_the_belongstomany_vars_to_set() {
		$request = new UserRequest();
		$request->replace(['name' => 'user name', 'rights' => [2,5]]);
		$this->assertEquals(['rights' => [2,5]], $request->getBelongsToManyValues());
	}

	/** @test */
	public function it_attaches_existing_related_models_to_a_new_saving_model() {
		$rights = collect([
			Right::create(['title' => 'view']),
			Right::create(['title' => 'edit']),
			Right::create(['title' => 'delete'])
		]);

		$request = new UserRequest();
		$request->replace([
			'rights' => $rights->only([0,1])->pluck('id')->toArray()
		]);

		$request->setModel(User::create(['name' => 'user name']));
		$request->createBelongsToMany();

		$this->assertEquals(
			[$rights[0]->id,$rights[1]->id],
			$request->getModel()->rights->pluck('id')->toArray()
		);
	}

	/** @test */
	public function it_attaches_existing_related_models_to_a_new_request() {
		$rights = collect([
			Right::create(['title' => 'view']),
			Right::create(['title' => 'edit']),
			Right::create(['title' => 'delete'])
		]);

		$request = new UserRequest();
		$request->replace([
			'name' => 'user name',
			'rights' => $rights->only([0,1])->pluck('id')->toArray()
		]);

		$user = $request->persist();

		$this->assertEquals(
			[$rights[0]->id,$rights[1]->id],
			$user->rights->pluck('id')->toArray()
		);
	}
}
