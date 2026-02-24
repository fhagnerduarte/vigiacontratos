<?php

namespace App\Models;

use App\Casts\EncryptedWithFallback;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    use HasFactory, SoftDeletes;

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

    protected function casts(): array
    {
        return [
            'email' => EncryptedWithFallback::class,
            'telefone' => EncryptedWithFallback::class,
            'representante_legal' => EncryptedWithFallback::class,
        ];
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }
}
