<?php

namespace Zoomyboy\BaseRequest\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class OneTag extends Model {
	public $fillable = ['title'];

	public function taggable() {
		return $this->morphTo();
	}
}
