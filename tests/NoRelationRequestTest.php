<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use \Zoomyboy\BaseRequest\Tests\Requests\PostRequest;
use \Zoomyboy\BaseRequest\Tests\Models\Post;

class PostRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_gets_fillable_attributes() {
		$request = new PostRequest();
		$request->replace(['title' => 'testa2']);
		$this->assertEquals(['title' => 'testa2'], $request->getFillInput());
	}

	/** @test */
	public function it_saves_a_model_with_no_relations_to_the_db() {
		$request = new PostRequest();
		$request->replace(['title' => 'testa2']);

		$request->persist();

		$model = Post::where('title', 'testa2')->first();
		$this->assertNotNull($model);

		$this->assertEquals('testa2', $model->title);
	}

	/** @test */
	public function it_deletes_vars_from_the_request_that_are_not_in_the_fillable_array() {
		$request = new PostRequest();
		$request->replace(['title' => 'testa2', 'c' => 'deleteme']);
		$this->assertEquals(['title' => 'testa2'], $request->getFillInput());
	}

	/** @test */
	public function it_updates_a_model_in_the_database() {
		$relation = Post::create(['title' => 'oldtitle']);

		$request = new PostRequest();
		$request->replace(['title' => 'newtitle']);
		$request->persist($relation);

		$model = Post::where('title', 'newtitle')->first();
		$this->assertNotNull($model);

		$this->assertEquals('newtitle', $model->title);
	}
}
