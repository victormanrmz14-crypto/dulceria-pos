<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')->id;

        return [
            'nombre'   => ['required', 'string', 'max:150'],
            'apellido' => ['nullable', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username,' . $userId],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'rol'      => ['required', 'in:admin,cajero'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre es obligatorio.',
            'username.required'  => 'El usuario es obligatorio.',
            'username.unique'    => 'Ese nombre de usuario ya existe.',
            'email.required'     => 'El email es obligatorio.',
            'email.unique'       => 'Ese email ya está registrado.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol.required'       => 'El rol es obligatorio.',
            'rol.in'             => 'El rol debe ser admin o cajero.',
        ];
    }
}
