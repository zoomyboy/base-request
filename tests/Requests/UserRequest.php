<?php

namespace Zoomyboy\BaseRequest\Tests\Requests;

use Zoomyboy\BaseRequest\Request as BaseRequest;

class UserRequest extends BaseRequest {
	public $model = '\Zoomyboy\BaseRequest\Tests\Models\User';
}
