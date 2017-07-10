<?php

namespace Zoomyboy\BaseRequest\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model {
	public $fillable = ['content'];

	public function oneTag() {
		return $this->morphOne(\Zoomyboy\BaseRequest\Tests\Models\OneTag::class, 'taggable');
	}
}
