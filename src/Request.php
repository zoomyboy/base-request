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
        if (property_exists($this, 'right')) {
            return auth()->user()->hasRight($this->right);
        }

        if (property_exists($this, 'scope')) {
            return auth()->user()->tokenCan($this->scope);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    public function persist($model = null)
    {
        $fill = (method_exists($this, 'modifyFillables')) ? $this->modifyFillables($this->input()) : $this->input();

        $model = $model ?: new $this->model;
        $handler = new Handler($model, $fill);
        $model = $handler->handle();

        if (method_exists($this, 'afterPersist')) {
            $this->afterPersist($model);
        }

        return $model;
    }

    public function validateResolved()
    {
        $this->validate();
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

    protected function addCustomValidationRules(&$validator)
    {
        if (!method_exists($this, 'customRules')) {
            return;
        }

        if ($this->customRules() === null) {
            return;
        }

        foreach ($this->customRules() as $field => $message) {
            $validator->after(function ($v) use ($field, $message) {
                $v->errors()->add($field, $message);
            });
        }
    }
}
