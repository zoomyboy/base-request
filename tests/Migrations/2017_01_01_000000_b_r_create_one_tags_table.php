<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BRCreateOneTagsTable extends Migration {
	public function up() {
		Schema::create('one_tags', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('taggable_type');
			$table->integer('taggable_id')->unsigned();
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::dropIfExists('one_tags');
	}
}
