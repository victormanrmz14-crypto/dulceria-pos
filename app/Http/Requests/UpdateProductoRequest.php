<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'        => ['required', 'string', 'max:255'],
            'categoria_id'  => ['required', 'exists:categorias,id'],
            'marca_id'      => ['required', 'exists:marcas,id'],
            'precio'        => ['required', 'numeric', 'min:0'],
            'stock'         => ['required', 'numeric', 'min:0'],
            'stock_minimo'  => ['required', 'numeric', 'min:0'],
            'unidad_medida' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'       => 'El nombre del producto es obligatorio.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'categoria_id.exists'   => 'La categoría seleccionada no es válida.',
            'marca_id.required'     => 'Debes seleccionar una marca.',
            'marca_id.exists'       => 'La marca seleccionada no es válida.',
            'precio.required'       => 'El precio es obligatorio.',
            'precio.numeric'        => 'El precio debe ser un número válido.',
            'precio.min'            => 'El precio no puede ser negativo.',
            'stock.required'        => 'El stock es obligatorio.',
            'stock.numeric'         => 'El stock debe ser un número válido.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'unidad_medida.required'=> 'La unidad de medida es obligatoria.',
        ];
    }
}