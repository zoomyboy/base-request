<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use \Zoomyboy\BaseRequest\Tests\Requests\Article2Request;
use \Zoomyboy\BaseRequest\Tests\Models\OneTag2;
use \Zoomyboy\BaseRequest\Tests\Models\Article2;
use Zoomyboy\BaseRequest\Handler;

class MorphOneRecursiveRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_gets_fillable_attributes_without_the_morphone_relation() {
		$request = new Article2Request();
		$handler = new Handler (new Article2, ['content' => 'This Article Has Seo Contents', 'oneTag' => 55555]);
		$this->assertEquals(['content' => 'This Article Has Seo Contents'], $handler->getFillInput());
	}

	/** @test */
	public function it_gets_related_morphone_attributes() {
		$request = new Article2Request();
		$handler = new Handler(new Article2, ['content' => 'This Article Has Seo Contents', 'oneTag' => 55555]);
		$this->assertEquals(['oneTag' => 55555], $handler->getSaveOneValues());
	}

	/** @test */
	public function it_saves_a_new_morphone_module() {
		$handler = new Handler(new Article2(), [
			'content' => 'This Article Has Seo Contents',
			'oneTag' => ['title' => 'Seo', 'priority' => 3]
		]);
		$this->assertEquals(['oneTag' => ['title' => 'Seo', 'priority' => 3]], $handler->getSaveOneValues());


		$article = $handler->handle();

		$article = Article2::find($article->id);
		$this->assertNotNull($article);
		$this->assertEquals('This Article Has Seo Contents', $article->content);
		$this->assertNotNull($article->oneTag);
		$this->assertEquals('Seo', $article->oneTag->title);
		$this->assertNotNull($article->oneTag->priority);
		$this->assertEquals('M', $article->oneTag->priority->title);
	}

	/** @test */
	public function it_updates_an_existing_morphone_relation() {
		$request = new Article2Request();
		$handler = new Handler(new Article2(), [
			'content' => 'This Article Has Seo Contents',
			'oneTag' => ['title' => 'Seo', 'priority' => 3]
		]);
		$article = $handler->handle();

		$article = Article2::find($article->id);
		$tagId = $article->oneTag->id;
		$this->assertEquals('Seo', $article->oneTag->title);
		$this->assertEquals('M', $article->oneTag->priority->title);

		$handler = new Handler($article, [
			'content' => 'This Article Has Seo Contents2',
			'oneTag' => ['priority' => 4, 'title' => 'SeoNeu']
		]);
		$this->assertEquals(['oneTag' => ['title' => 'SeoNeu', 'priority' => 4]], $handler->getSaveOneValues());
		$article = $handler->handle();
		
		$article->load('oneTag');
		$this->assertEquals('SeoNeu', $article->oneTag->title);
		$this->assertEquals('L', $article->oneTag->priority->title);
		$this->assertEquals($tagId, $article->oneTag->id);
	}
}
