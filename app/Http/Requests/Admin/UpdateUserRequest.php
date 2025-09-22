<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // protegido no middleware de permissão
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'  => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', Rule::unique('users','email')->ignore($userId)],
            // senha é opcional no modal; se vier preenchida, precisa confirmar
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome.',
            'email.required'=> 'Informe o e-mail.',
            'email.unique'  => 'Este e-mail já está em uso.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ];
    }
}
