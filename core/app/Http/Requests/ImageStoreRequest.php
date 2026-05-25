<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ImageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check() || Auth::guard('web')->check();
    }

    public function rules(): array
    {
        return ValidationRules::image('photo', true, 2048);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'photo.required' => __('Image field is required.'),
            'photo.mimes'    => __('Image type must be jpg,jpeg,png,svg.')
        ];
    }



}
