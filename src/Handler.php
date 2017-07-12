<?php

namespace Zoomyboy\BaseRequest;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Handler {
	
	public $model;
	public $input;
		
	public function __construct($model, $input) {
		$this->model = $model;
		$this->input = $input;
	}

	public function handle() {
		$this->beforeSave();
		$this->model->save();
		$this->afterSave();

		return $this->model;
	}

	public function beforeSave() {
		$this->model->fill($this->getFillInput());
		$this->createBelongsTo();
	}

	public function afterSave() {
		$this->createBelongsToMany();
		$this->createSaveOne();

		return $this->model;
	}

	//*******************************************************************************
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
		$relation = $this->model->{$method}();

		return $relation instanceOf BelongsTo;
	}

	public function getBelongsToValues() {
		return array_filter($this->input, function($item, $index) {
			return $this->methodExists($index) && $this->isBelongsToMethod($index);
		}, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 * Determines if the given method of the Saving model is a Relation
	 * with the sync option, so that you can attach/detach the related models
	 *
	 * @param string $method The method name
	 *
	 * @return bool
	 */
	private function isBelongsToManyMethod($method) {
		$relation = $this->model->{$method}();

		return $relation instanceOf BelongsToMany;
	}

	public function getBelongsToManyValues() {
		return array_filter($this->input, function($item, $index) {
			return $this->methodExists($index) && $this->isBelongsToManyMethod($index);
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
		$relation = $this->model->{$method}();

		return $relation instanceOf MorphOne;
	}

	public function getSaveOneValues() {
		return array_filter($this->input, function($item, $index) {
			return $this->methodExists($index) && $this->isSaveOneMethod($index);
		}, ARRAY_FILTER_USE_BOTH);
	}

	public function methodExists($method) {
		return method_exists($this->model, $method);
	}

	//*******************************************************************************
	//---------------------------- Getters of the input -----------------------------
	//*******************************************************************************
	/**
	 * Filters the given input array and return only the fillable Values
	 * Those Values can be inserted directly in the input model, because they are fillable
	 *
	 * @return array
	 */
	public function getFillInput() {
		$fill = array_filter($this->input, function($item, $index) {
			if ($this->methodExists($index)) {
				return false;
			}
			if (is_array($this->model->fillable) && !in_array($index, $this->model->fillable)) {
				return false;
			}
			if(is_array($this->model->guarded) && in_array($index, $this->model->guarded)) {
				return false;
			}

			return true;
		}, ARRAY_FILTER_USE_BOTH);

		return $fill;
	}

	//*******************************************************************************
	//------------------------------ Create relations -------------------------------
	//*******************************************************************************
	/**
	 * Associates models that have already been saved to the database
	 * with the input model.
	 *
	 * Usually, the input value is just a numeric value representing the
	 * Id of the related (belongs-to) model
	 * This method should be called before the input model is saved,
	 * because the ID of the saved input model is not relevant.
	 */
	public function createBelongsTo() {
		foreach($this->getBelongsToValues() as $method => $value) {
			$associatedModelName = '\\'.get_class($this->model->{$method}()->getRelated());

			if (is_numeric($value)
			  && !is_null($associatedModelName::find($value))) {
				$this->model->{$method}()->associate($associatedModelName::find($value));
			}
			if (is_null($value)) {
				$this->model->{$method}()->dissociate();
			}
		}
	}

	/**
	 * Syncs models that have already been saved to the database
	 * with the input model.
	 *
	 * Usually, the input value is an array of all Related IDs you want so sync. All other
	 * related Models that aren't present in this array will be detacheed.
	 * This method should be called after the input model is saved, because it stores
	 * the ID of the input model in the pivot table.
	 */
	public function createBelongsToMany() {
		foreach($this->getBelongsToManyValues() as $method => $value) {
			$associatedModelName = '\\'.get_class($this->model->{$method}()->getRelated());

			if (is_array($value)) {
				$this->model->{$method}()->sync($value);
			}
		}
	}

	/**
	 * Create save one relations.
	 * This should be called after saving the input model, because we have to know the Id 
	 * of the input model to save the related one
	 */
	public function createSaveOne() {
		foreach($this->getSaveOneValues() as $method => $value) {
			if (!is_array($value)) {continue;}

			$associatedModelName = '\\'.get_class($this->model->{$method}()->getRelated());

			if(is_null($this->model->{$method})) {
				//Related Model doesnt exist yet - so we save a new one
				$relatedHandler = new self(new $associatedModelName, $value);
				$relatedHandler->beforeSave();
				$this->model->{$method}()->save($relatedHandler->model);
				$relatedHandler->afterSave();
			} else {
				//Related model exists - so we update it
				$relatedHandler = new self($this->model->{$method}, $value);
				$relatedHandler->beforeSave();
				$this->model->{$method}()->update($relatedHandler->getFillInput());
				$relatedHandler->afterSave();
				$this->model->{$method}->update($value);
			}
		}
	}
}
