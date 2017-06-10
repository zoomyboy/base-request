<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateCommentsTable extends Migration {
	public function up() {
		Schema::create('comments', function (Blueprint $table) {
			$table->increments('id');
			$table->string('content');
			$table->integer('post_id')->unsigned();
			
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::dropIfExists('comments');
	}
}
