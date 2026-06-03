<?php

namespace App\Http\Requests;

use App\Models\Setting;
use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SellerRequest extends FormRequest
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
    public function rules()
    {

        $id = Auth::check() ? ',' . Auth::user()->id : '';
        $userId = Auth::check() ? (int) Auth::user()->id : null;
        $setting = Setting::first();
        $password = Auth::check() ? '' : 'required|';
        $passwordRules = ValidationRules::strongPassword(! Auth::check());
        $passwordRules[] = 'confirmed';

        return [
            'g-recaptcha-response' => $setting->recaptcha == 1 ?  $password : '',
            'first_name' => $password . '|max:255',
            'last_name'  => 'required|max:255',
            'phone'      => ValidationRules::phoneUnique('phone', 'users', $userId)['phone'],
            'email'      => Auth::guard('admin')->check() ? 'required|email' : 'required|email|unique:users,email' . $id,
            'password'   => $passwordRules,
            'password_confirmation'   => [Auth::check() ? 'nullable' : 'required', 'string', 'required_with:password'],
            "shop_name" => "required|unique:sellers,user_id," . $id,
            "shop_address" => "required|string"
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'g-recaptcha-response.required' => __('Please verify that you are not a robot.'),
            'first_name.required' => __('First Name is required.'),
            'last_name.required' => __('Last Name field is required.'),
            'phone.required' => __('Phone Number is required.'),
            'phone.digits' => __('Phone number must contain exactly 10 digits.'),
            'phone.unique' => __('This phone number already exists.'),
            'email.required' => __('Email field is required.'),
            'email.email'   => __('The email must be a valid email address.'),
            'password.required'    => __('Password field is required.'),
            'password.mixed' => __('Password must contain uppercase and lowercase letters.'),
            'password.numbers' => __('Password must contain at least one number.'),
            'password.symbols' => __('Password must contain at least one symbol.'),
            'password.uncompromised' => __('This password has appeared in a data breach. Please choose a different password.'),
            "shop_name.required" => "Shop name field is required",
            "shop_name.unique" => "Shop name already exists",
            "shop_address.required" => "Shop address field is required",
        ];
    }
}
