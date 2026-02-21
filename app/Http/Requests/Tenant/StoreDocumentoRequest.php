<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TipoDocumentoContratual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('documento.criar');
    }

    public function rules(): array
    {
        return [
            'arquivo' => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20MB (RN-022, RN-119)
            'tipo_documento' => ['required', 'string', new Enum(TipoDocumentoContratual::class)],
            'descricao' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'arquivo.required' => 'O arquivo e obrigatorio.',
            'arquivo.file' => 'O upload deve ser um arquivo valido.',
            'arquivo.mimes' => 'Apenas arquivos PDF sao aceitos (RN-021).',
            'arquivo.max' => 'O arquivo nao pode exceder 20MB (RN-119).',
            'tipo_documento.required' => 'O tipo de documento e obrigatorio (RN-040).',
            'tipo_documento.Illuminate\Validation\Rules\Enum' => 'Tipo de documento invalido.',
            'descricao.max' => 'A descricao nao pode exceder 255 caracteres.',
        ];
    }
}
