<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Cast que encripta na escrita e tenta descriptografar na leitura.
 * Se a descriptografia falhar (dados legados em plaintext), retorna o valor original.
 *
 * Necessario para tabelas append-only (login_logs, admin_login_logs) onde
 * registros antigos nao podem ser atualizados para formato criptografado,
 * e para periodo de transicao em tabelas regulares.
 */
class EncryptedWithFallback implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            // Dado legado em plaintext — retorna como esta
            return $value;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString($value);
    }
}
