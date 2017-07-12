<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BRCreateArticle2sTable extends Migration {
	public function up() {
		Schema::create('article2s', function (Blueprint $table) {
			$table->increments('id');
			$table->string('content');
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::dropIfExists('article2s');
	}
}
