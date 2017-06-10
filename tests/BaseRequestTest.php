<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use \Zoomyboy\BaseRequest\Tests\Requests\CommentRequest;
use \Zoomyboy\BaseRequest\Tests\Models\Post;

class BaseRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_can_set_a_model_directly_on_the_request() {
		$model = new Post();
		$model->fill(['title' => 'test']);

		$request = new CommentRequest();
		$request->setModel($model);

		$this->assertEquals('test', $request->getModel()->title);
	}
}
