<?php

namespace App\Models;

use App\Enums\TipoDocumentoContratual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'documentos';

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'nome_original',
        'path',
        'mime_type',
        'tamanho_bytes',
        'tipo_documento',
        'hash_integridade',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'tipo_documento' => TipoDocumentoContratual::class,
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
