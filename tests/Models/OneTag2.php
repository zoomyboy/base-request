<?php

namespace Zoomyboy\BaseRequest\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class OneTag2 extends Model {
	public $fillable = ['title'];

	public function taggable() {
		return $this->morphTo();
	}

	public function priority() {
		return $this->belongsTo(Priority::class);
	}
}
