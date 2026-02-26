<?php

namespace App\Http\Requests\Api;

use App\Enums\CategoriaServico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePrecoReferencialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descricao' => ['required', 'string', 'max:255'],
            'categoria_servico' => ['required', new Enum(CategoriaServico::class)],
            'unidade_medida' => ['required', 'string', 'max:50'],
            'preco_minimo' => ['required', 'numeric', 'min:0.01'],
            'preco_mediano' => ['required', 'numeric', 'gte:preco_minimo'],
            'preco_maximo' => ['required', 'numeric', 'gte:preco_mediano'],
            'fonte' => ['required', 'string', 'max:255'],
            'data_referencia' => ['required', 'date', 'before_or_equal:today'],
            'vigencia_ate' => ['nullable', 'date', 'after:data_referencia'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
