<?php

namespace Zoomyboy\BaseRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
	public function rules() {
		return [];
	}

	public function persist($model = null) {
		if ($model == null) {
			$this->add();
		} else {
			$this->edit($model);
		}
	}

	public function add() {
		$model = new $this->model;
		$model->fill($this->getFillInput());
		$this->createAssociations($model);
		$model->save();
	}

	public function edit($model) {
		$model->fill($this->getFillInput());
		$this->createAssociations($model);
		$model->save();
	}

	protected function getFillInput() {
		$model = new $this->model();

		return array_filter($this->input(), function($item, $index) use($model) {
			return (is_array($model->fillable) && in_array($index, $model->fillable))
				|| !method_exists($model, $index);
		}, ARRAY_FILTER_USE_BOTH);
	}

	public function createAssociations($model) {
		foreach($this->getAssociatedMethods() as $method => $value) {
			$associatedModelName = '\App\\'.ucfirst($method);
			if (is_null($associatedModelName::find($value))) {
				continue;
			}

			if($model->{$method}() instanceof BelongsTo ) {
				$model->{$method}()->associate($associatedModelName::find($value));
			}
		}
	}

	public function getAssociatedMethods() {
		$model = new $this->model();

		return array_filter($this->input(), function($item, $index) use($model) {
			return !(is_array($model->fillable) && in_array($index, $model->fillable))
				&& method_exists($model, $index);
		}, ARRAY_FILTER_USE_BOTH);
	}
}
