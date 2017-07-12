<?php

namespace Zoomyboy\BaseRequest\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model {
	public $fillable = ['title'];

	public function tag() {
		return $this->hasOne(Tag2::class);
	}
}
