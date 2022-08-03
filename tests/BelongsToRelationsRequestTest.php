<?php

namespace Zoomyboy\BaseRequest\Tests;

use Zoomyboy\BaseRequest\Handler;
use Zoomyboy\BaseRequest\Tests\Models\Comment;
use Zoomyboy\BaseRequest\Tests\Models\Post;

class BelongsToRelationsRequestTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function itGetsFillableAttributesWithoutTheBelongstoRelation()
    {
        $handler = new Handler(new Comment(), ['content' => 'This is my comment for that post', 'post_id' => 2]);
        $this->assertEquals(['content' => 'This is my comment for that post'], $handler->getFillInput());
    }

    /** @test */
    public function itGetsAllTheFillableVarsToSet()
    {
        $handler = new Handler(new Comment(), ['post' => 3, 'content' => 'con']);
        $this->assertEquals(['content' => 'con'], $handler->getFillInput());
    }

    /** @test */
    public function itGetsAllTheBelongstoVarsToSet()
    {
        $handler = new Handler(new Comment(), ['post' => 3, 'content' => 'con']);
        $this->assertEquals(['post' => 3], $handler->getBelongsToValues());
    }

    /** @test */
    public function itSetsTheBelongstoIdOfAnAlreadySavedRelatedModel()
    {
        $post = Post::create(['title' => 'Im a post']);

        $model = new Comment(['content' => 'This is another Comment for another Post']);
        $this->assertNull($model->post);

        $handler = new Handler(new Comment(), ['post' => $post->id]);

        $handler->createBelongsTo();

        $this->assertEquals($post->id, $handler->model->post->id);
    }

    /** @test */
    public function itCreatesARelatedModel()
    {
        $handler = new Handler(new Comment(), [
            'content' => 'This is another Comment for another Post',
            'post' => [
                'title' => 'Post Title',
            ],
        ]);

        $model = $handler->handle();

        $this->assertEquals('This is another Comment for another Post', $model->content);
        $this->assertEquals('Post Title', $model->post->title);
    }
}
