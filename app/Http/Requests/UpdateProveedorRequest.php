<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'   => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:255', 'unique:proveedores,email,' . $this->proveedor->id],
            'telefono' => ['nullable', 'string', 'max:20'],
            'notas'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar 150 caracteres.',
            'email.required'  => 'El correo electrónico es obligatorio.',
            'email.email'     => 'El correo electrónico no tiene un formato válido.',
            'email.unique'    => 'Ya existe otro proveedor registrado con ese correo.',
            'telefono.max'    => 'El teléfono no puede superar 20 caracteres.',
        ];
    }
}
