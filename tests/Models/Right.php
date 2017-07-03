<?php

namespace Zoomyboy\BaseRequest\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Right extends Model {
	public $fillable = ['title'];

	public function users() {
		return $this->belongsToMany(User::class);
	}
}
