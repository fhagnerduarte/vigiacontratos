<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'fornecedores';

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'representante_legal',
        'email',
        'telefone',
        'endereco',
        'cidade',
        'uf',
        'cep',
        'observacoes',
    ];
}
