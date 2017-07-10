<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateArticlesTable extends Migration {
	public function up() {
		Schema::create('articles', function (Blueprint $table) {
			$table->increments('id');
			$table->string('content');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::dropIfExists('articles');
	}
}
