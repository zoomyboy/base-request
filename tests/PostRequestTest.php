<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use \Zoomyboy\BaseRequest\Tests\Requests\PostRequest;
use \Zoomyboy\BaseRequest\Tests\Models\Post;
use \Zoomyboy\BaseRequest\Handler;

class PostRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_gets_fillable_attributes() {
		$handler = new Handler(new Post(), ['title' => 'testa2']);
		$this->assertEquals(['title' => 'testa2'], $handler->getFillInput());
	}

	/** @test */
	public function it_saves_a_model_with_no_relations_to_the_db() {
		$handler = new Handler(new Post(), ['title' => 'testa2']);

		$handler->handle();

		$model = Post::where('title', 'testa2')->first();
		$this->assertNotNull($model);

		$this->assertEquals('testa2', $model->title);
	}

	/** @test */
	public function it_deletes_vars_from_the_request_that_are_not_in_the_fillable_array() {
		$handler = new Handler(new Post(), ['title' => 'testa2', 'c' => 'deleteme']);
		$request = new PostRequest();
		$this->assertEquals(['title' => 'testa2'], $handler->getFillInput());
	}

	/** @test */
	public function it_updates_a_model_in_the_database() {
		$relation = Post::create(['title' => 'oldtitle']);

		$handler = new Handler($relation, ['title' => 'newtitle']);
		$relation = $handler->handle();

		$model = Post::where('title', 'newtitle')->first();
		$this->assertNotNull($model);

		$this->assertEquals('newtitle', $model->title);
	}
}
