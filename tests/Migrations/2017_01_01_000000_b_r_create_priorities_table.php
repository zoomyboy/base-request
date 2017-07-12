<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Zoomyboy\BaseRequest\Tests\Models\Priority;

class BRCreatePrioritiesTable extends Migration {
	public function up() {
		Schema::create('priorities', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->timestamps();
		});

		Priority::create(['title' => 'XS']);
		Priority::create(['title' => 'S']);
		Priority::create(['title' => 'M']);
		Priority::create(['title' => 'L']);
		Priority::create(['title' => 'XL']);
	}
	public function down()
	{
		Schema::dropIfExists('priorities');
	}
}
