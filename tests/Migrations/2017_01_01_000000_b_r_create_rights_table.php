<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BRCreateRightsTable extends Migration {
	public function up() {
		Schema::create('rights', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::dropIfExists('rights');
	}
}
