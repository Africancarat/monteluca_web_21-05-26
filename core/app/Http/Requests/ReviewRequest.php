<?php

namespace App\Http\Requests;

use Illuminate\{
    Foundation\Http\FormRequest,
    Http\Exceptions\HttpResponseException,
    Contracts\Validation\Validator
};


class ReviewRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->trimStrings(['subject', 'occasion', 'ring_size_ordered', 'metal_type_ordered']);
        $this->stripHtml('review');
    }

    public function rules(): array
    {
        return array_merge(
            ['rating' => ['required', 'integer', 'min:1', 'max:5']],
            ['review' => ['required', 'string', 'max:5000']],
            ['subject' => ['required', 'string', 'max:220']],
            ['occasion' => ['nullable', 'string', 'max:60']],
            ['ring_size_ordered' => ['nullable', 'string', 'max:48']],
            ['metal_type_ordered' => ['nullable', 'string', 'max:80']],
            ['review_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096']],
        );
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'rating.required'   =>  __('Rating field is required.'),
            'review.required'   =>  __('Review field is required.')
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
