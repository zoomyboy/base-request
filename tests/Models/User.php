<?php

namespace Zoomyboy\BaseRequest\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
	protected $table = 'testusers';

	public $fillable = ['name'];

	public function rights() {
		return $this->belongsToMany(Right::class);
	}
}
