<?php

namespace Zoomyboy\BaseRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Model;

abstract class Request extends FormRequest
{
	public $modelInstance;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
		if(property_exists($this, 'right')) {
			return auth()->user()->hasRight($this->right);
		}

        return true;
    }

	public function getModel() {
		if (is_null($this->modelInstance)) {
			return new $this->model();
		}

		return $this->modelInstance;
	}

	/**
	 * Sets the Model to be inserted or updated directly
	 *
	 * @param Model $model
	 */
	public function setModel(Model $model) {
		$this->modelInstance = $model;
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
			return $this->add();
		} else {
			return $this->edit($model);
		}
	}

	public function add() {
		$model = $this->getModel();
		$this->modelInstance = $this->getModel()->fill($this->getFillInput());
		$this->createBelongsTo();
		$this->getModel()->save();
		$this->createSaveOne();
		$this->createBelongsToMany();

		if (method_exists($this, 'afterPersist')) {
			$this->afterPersist($this->getModel());
		}

		return $this->getModel();
	}

	public function edit($model) {
		$this->setModel($model);
		$this->modelInstance = $this->getModel()->fill($this->getFillInput());
		$this->createBelongsTo();
		$this->getModel()->save();
		$this->createSaveOne();
		$this->createBelongsToMany();

		if (method_exists($this, 'afterPersist')) {
			$this->afterPersist($this->getModel());
		}

		return $this->getModel();
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
	 * Determines if the given method of the Saving model is a Relation
	 * with the sync option, so that you can attach/detach the related models
	 *
	 * @param string $method The method name
	 *
	 * @return bool
	 */
	private function isBelongsToManyMethod($method) {
		$model = new $this->model();
		$relation = $model->{$method}();

		return $relation instanceOf BelongsToMany;
	}

	public function getBelongsToManyValues() {
		return array_filter($this->input(), function($item, $index) {
			return $this->isMethod($index) && $this->isBelongsToManyMethod($index);
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
		$model = new $this->model();

		$fill = array_filter($this->input(), function($item, $index) use($model) {
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

		if (method_exists($this, 'modifyFillables')) {
			$fill = $this->modifyFillables($fill);
		}

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
		$model = $this->getModel();

		foreach($this->getBelongsToValues() as $method => $value) {
			$associatedModelName = '\\'.get_class($model->{$method}()->getRelated());

			if (is_numeric($value)
			  && !is_null($associatedModelName::find($value))) {
				$model->{$method}()->associate($associatedModelName::find($value));
			}
			if (is_null($value)) {
				$model->{$method}()->dissociate();
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
		$model = $this->getModel();

		foreach($this->getBelongsToManyValues() as $method => $value) {
			$associatedModelName = '\\'.get_class($model->{$method}()->getRelated());

			if (is_array($value)) {
				$model->{$method}()->sync($value);
			}
		}
	}

	/**
	 * Create save one relations.
	 * This should be called after saving the input model, because we have to know the Id 
	 * of the input model to save the related one
	 */
	public function createSaveOne() {
		$model = $this->getModel();

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

    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validate()
    {
        $this->prepareForValidation();

        $instance = $this->getValidatorInstance();
		$this->addCustomValidationRules($instance);

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        } elseif (! $instance->passes()) {
            $this->failedValidation($instance);
        }
	}

	protected function addCustomValidationRules(&$validator) {
		if (!method_exists($this, 'customRules')) {
			return;
		}

		if ($this->customRules() === null) {
			return;
		}	

		foreach($this->customRules() as $field => $message) {
			$validator->after(function($v) use ($field, $message) {
				$v->errors()->add($field, $message);
			});
		}
	}
}
