<?php

namespace Zoomyboy\BaseRequest\Tests;

use \Illuminate\Http\Request;
use \Zoomyboy\BaseRequest\Tests\Requests\ArticleRequest;
use \Zoomyboy\BaseRequest\Tests\Models\OneTag;
use \Zoomyboy\BaseRequest\Tests\Models\Article;

class MorphOneRequestTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_gets_fillable_attributes_without_the_morphone_relation() {
		$request = new ArticleRequest();
		$request->replace(['content' => 'This Article Has Seo Contents', 'oneTag' => 55555]);
		$this->assertEquals(['content' => 'This Article Has Seo Contents'], $request->getFillInput());
	}

	/** @test */
	public function it_gets_related_morphone_attributes() {
		$request = new ArticleRequest();
		$request->replace(['content' => 'This Article Has Seo Contents', 'oneTag' => 55555]);
		$this->assertEquals(['oneTag' => 55555], $request->getSaveOneValues());
	}

	/** @test */
	public function it_saves_a_new_morphone_module() {
		$request = new ArticleRequest();
		$request->replace([
			'content' => 'This Article Has Seo Contents',
			'oneTag' => ['title' => 'Seo']
		]);
		$this->assertEquals(['oneTag' => ['title' => 'Seo']], $request->getSaveOneValues());
		$article = $request->persist();

		$article = Article::find($article->id);
		$this->assertNotNull($article);
		$this->assertEquals('This Article Has Seo Contents', $article->content);
		$this->assertNotNull($article->oneTag);
		$this->assertEquals('Seo', $article->oneTag->title);
	}

	/** @test */
	public function it_updates_an_existing_morphone_relation() {
		$request = new ArticleRequest();
		$request->replace([
			'content' => 'This Article Has Seo Contents',
			'oneTag' => ['title' => 'Seo']
		]);
		$article = $request->persist();

		$article = Article::find($article->id);
		$tagId = $article->oneTag->id;
		$this->assertEquals('Seo', $article->oneTag->title);

		$request = new ArticleRequest();
		$request->replace([
			'content' => 'This Article Has Seo Contents',
			'oneTag' => ['title' => 'SeoNeu']
		]);
		$this->assertEquals(['oneTag' => ['title' => 'SeoNeu']], $request->getSaveOneValues());
		
		$request->persist($article);
		$article->load('oneTag');
		$this->assertEquals('SeoNeu', $article->oneTag->title);
		$this->assertEquals($tagId, $article->oneTag->id);
	}
}
