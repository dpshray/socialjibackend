<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'nick_name' => ['required', 'string', 'max:255', Rule::unique('users')->whereNull('deleted_at')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'role_id' => ['required', Rule::exists('roles', 'id')->whereNotIn('name', ['Admin'])],
            'image' => ['required', 'max:1024']
        ];
    }

    public function passedValidation(){
        $this->replace(['role_id' => (int)$this->role_id]);
    }
}
