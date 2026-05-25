<?php

namespace App\Http\Requests;

use Illuminate\{
    Foundation\Http\FormRequest,
    Http\Exceptions\HttpResponseException,
    Contracts\Validation\Validator
};

class SubscribeRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeEmail('email');
    }

    public function rules(): array
    {
        return ValidationRules::emailUnique('email', 'subscribers', null, true);
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => __('Email field is required.'),
            'email.unique'   => __('This email has already been taken.')
        ];
    }


    /**
     * Returning json response.
     *
     * @return array
     */

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(array('errors' => $validator->getMessageBag()->toArray())));
    }

}
