<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use \Zoomyboy\BaseRequest\Tests\Requests\CommentRequest;
use \Zoomyboy\BaseRequest\Tests\Models\Comment;
use \Zoomyboy\BaseRequest\Tests\Models\Post;

class BelongsToRelationRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_gets_fillable_attributes_without_the_belongsto_relation() {
		$request = new CommentRequest();
		$request->replace(['content' => 'This is my comment for that post', 'post_id' => 2]);
		$this->assertEquals(['content' => 'This is my comment for that post'], $request->getFillInput());
	}

	/** @test */
	public function it_gets_all_the_fillable_vars_to_set() {
		$request = new CommentRequest();
		$request->replace(['post' => 3, 'content' => 'con']);
		$this->assertEquals(['content' => 'con'], $request->getFillInput());
	}

	/** @test */
	public function it_gets_all_the_belongsto_vars_to_set() {
		$request = new CommentRequest();
		$request->replace(['post' => 3, 'content' => 'con']);
		$this->assertEquals(['post' => 3], $request->getBelongsToValues());
	}

	/** @test */
	public function it_sets_the_belongsto_id_of_an_already_saved_related_model() {
		$post = Post::create(['title' => 'Im a post']);

		$model = new Comment(['content' => 'This is another Comment for another Post']);
		$this->assertNull($model->post);

		$request = new CommentRequest();
		$request->replace(['post' => $post->id]);
		$request->setModel($model);

		$request->createBelongsTo();

		$this->assertEquals($post->id, $request->getModel()->post->id);
	}
}
