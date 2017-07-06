<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BRCreateRightUserTable extends Migration {
	public function up() {
		Schema::create('right_user', function (Blueprint $table) {
			$table->increments('right_id');
			$table->string('user_id');
			$table->index(['right_id', 'user_id']);
		});
	}
	public function down()
	{
		Schema::dropIfExists('right_user');
	}
}
