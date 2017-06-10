<?php

namespace Zoomyboy\BaseRequest\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model {
	public $fillable = ['content'];

	public function post() {
		return $this->belongsTo(Post::class);
	}
}
