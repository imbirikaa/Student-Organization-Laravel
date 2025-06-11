<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:20',
            'birth_date' => 'required|date|before:today',
            'nickname' => 'required|string|unique:users,nickname',
            'password' => 'required|string|min:6|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'university_id' => 'nullable|exists:universities,id',
        ];
    }
}
