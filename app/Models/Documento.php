<?php

namespace App\Models;

use App\Enums\TipoDocumentoContratual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'documentos';

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'tipo_documento',
        'nome_original',
        'nome_arquivo',
        'descricao',
        'caminho',
        'tamanho',
        'mime_type',
        'hash_integridade',
        'integridade_comprometida',
        'versao',
        'is_versao_atual',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'tipo_documento' => TipoDocumentoContratual::class,
            'is_versao_atual' => 'boolean',
            'integridade_comprometida' => 'boolean',
            'versao' => 'integer',
            'tamanho' => 'integer',
        ];
    }

    // Scopes

    public function scopeVersaoAtual(Builder $query): Builder
    {
        return $query->where('is_versao_atual', true);
    }

    // Relacionamentos

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function logsAcesso(): HasMany
    {
        return $this->hasMany(LogAcessoDocumento::class);
    }

    public function logsIntegridade(): HasMany
    {
        return $this->hasMany(LogIntegridadeDocumento::class);
    }
}
