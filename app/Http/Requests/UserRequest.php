<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                $rules = [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', Rule::unique('users')],
                    'username' => ['required', 'string', 'max:255', Rule::unique('users')],
                    'phone' => ['required', 'digits_between:11,12', 'starts_with:8', Rule::unique('users')],
                    'password' => ['required', 'string', new Password, 'confirmed'],
                ];
                break;
            case 'PUT':
            case 'PATCH':
                $rules = [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->id)],
                    'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->id)],
                    'phone' => ['required', 'digits_between:11,12', 'starts_with:8', Rule::unique('users')->ignore($this->id)],
                    'password' => ['nullable', 'string', new Password, 'confirmed'],
                    'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
                ];
                break;
            default:
                $rules = [];
                break;
        }

        return $rules;
    }
}
