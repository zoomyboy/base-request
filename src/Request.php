<?php

namespace Zoomyboy\BaseRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
		$this->createBelongsTo($model);
		$model->save();
		$this->createSaveOne($model);
	}

	public function edit($model) {
		$model->fill($this->getFillInput());
		$this->createBelongsTo($model);
		$model->save();
		$this->createSaveOne($model);
	}

	//---------------------------- Method determination -----------------------------
	//*******************************************************************************
	/**
	 * Determines if the given method of the Saving model is a Relation
	 * with the save option, so that you can save the related model
	 *
	 * @param string $method The method name
	 *
	 * @return bool
	 */
	private function isBelongsToMethod($method) {
		$model = new $this->model();
		$relation = $model->{$method}();

		return $relation instanceOf BelongsTo;
	}

	public function getBelongsToValues() {
		return array_filter($this->input(), function($item, $index) {
			return $this->isMethod($index) && $this->isBelongsToMethod($index);
		}, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 * Determines if the given method of the input model is a Relation
	 * with the save option, so that you can save the related model
	 *
	 * @param string $method The method name
	 *
	 * @return bool
	 */
	private function isSaveOneMethod($method) {
		$model = new $this->model();
		$relation = $model->{$method}();

		return $relation instanceOf MorphOne;
	}

	public function getSaveOneValues() {
		return array_filter($this->input(), function($item, $index) {
			return $this->isMethod($index) && $this->isSaveOneMethod($index);
		}, ARRAY_FILTER_USE_BOTH);
	}

	public function isMethod($method) {
		$model = new $this->model();

		return method_exists($model, $method);
	}

	/**
	 * Filters the given input array and return only the fillable Values
	 * Those Values can be inserted directly in the input model, because they are fillable
	 *
	 * @return array
	 */
	protected function getFillInput() {
		$model = new $this->model();

		return array_filter($this->input(), function($item, $index) use($model) {
			if ($this->isMethod($index)) {
				return false;
			}
			if (is_array($model->fillable) && !in_array($index, $model->fillable)) {
				return false;
			}
			if(is_array($model->guarded) && in_array($index, $model->guarded)) {
				return false;
			}

			return true;
		}, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 * Associates models that have already been saved to the database
	 * with the input model.
	 *
	 * Usually, the input value is just a numeric value representing the
	 * Id of the related (belongs-to) model
	 * This method should be called before the input model is saved,
	 * because the ID of the saved input model is not relevant.
	 *
	 * @param Model $model An (probably previously filled) instance of the input model
	 */
	public function createBelongsTo($model) {
		foreach($this->getBelongsToValues() as $method => $value) {
			$associatedModelName = '\\'.get_class($model->{$method}()->getRelated());

			if (is_numeric($value)
			  && !is_null($associatedModelName::find($value))) {
				$model->{$method}()->associate($associatedModelName::find($value));
			}
		}
	}

	/**
	 * Create save one relations.
	 * This should be called after saving the input model, because we have to know the Id 
	 * of the input model to save the related one
	 *
 	 * @param Model $model An (probably previously filled) instance of the input model
	 */
	public function createSaveOne($model) {
		foreach($this->getSaveOneValues() as $method => $value) {
			if (!is_array($value)) {continue;}

			$associatedModelName = '\\'.get_class($model->{$method}()->getRelated());

			if (array_key_exists('id', $value)) {
				//We have the Id of the related model, so we just update it
				$associatedModelName::find($value['id'])->update($value);
			} else {
				//Id doesnt exists on the related Model, so we should create that from scratch
				$model->{$method}()->save(new $associatedModelName($value));
			}
		}
	}


}
