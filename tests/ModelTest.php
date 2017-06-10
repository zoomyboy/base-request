<?php

namespace Zoomyboy\BaseRequest\Tests;

use Zoomyboy\BaseRequest\Tests\Models\Post;

class ModelTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_creates_a_test_model_and_saves_it_to_the_database_without_any_request() {
		$model = new Post();
		$model->fill(['title' => 'test']);
		$model->save();
		$id = $model->id;

		$model = Post::find($id);
		$this->assertNotNull($model);

		$this->assertEquals('test', $model->title);
	}
}
