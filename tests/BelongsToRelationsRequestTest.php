<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use \Zoomyboy\BaseRequest\Tests\Requests\CommentRequest;
use \Zoomyboy\BaseRequest\Handler;
use \Zoomyboy\BaseRequest\Tests\Models\Comment;
use \Zoomyboy\BaseRequest\Tests\Models\Post;

class BelongsToRelationRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_gets_fillable_attributes_without_the_belongsto_relation() {
		$handler = new Handler(new Comment(), ['content' => 'This is my comment for that post', 'post_id' => 2]);
		$this->assertEquals(['content' => 'This is my comment for that post'], $handler->getFillInput());
	}

	/** @test */
	public function it_gets_all_the_fillable_vars_to_set() {
		$handler = new Handler(new Comment(), ['post' => 3, 'content' => 'con']);
		$this->assertEquals(['content' => 'con'], $handler->getFillInput());
	}

	/** @test */
	public function it_gets_all_the_belongsto_vars_to_set() {
		$handler = new Handler(new Comment(), ['post' => 3, 'content' => 'con']);
		$this->assertEquals(['post' => 3], $handler->getBelongsToValues());
	}

	/** @test */
	public function it_sets_the_belongsto_id_of_an_already_saved_related_model() {
		$post = Post::create(['title' => 'Im a post']);

		$model = new Comment(['content' => 'This is another Comment for another Post']);
		$this->assertNull($model->post);

		$handler = new Handler(new Comment(), ['post' => $post->id]);

		$handler->createBelongsTo();

		$this->assertEquals($post->id, $handler->model->post->id);
	}
}
