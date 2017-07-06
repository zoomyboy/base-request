<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BRCreateUsersTable extends Migration {
	public function up() {
		Schema::create('testusers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			
			$table->timestamps();
		});
	}
	public function down()
	{
		Schema::dropIfExists('testusers');
	}
}
