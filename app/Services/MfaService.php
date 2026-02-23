<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class MfaService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function generateQrCodeDataUri(Model $user, string $secret): string
    {
        $appName = config('app.name', 'VigiaContratos');
        $email = $user->email;

        $otpauthUrl = $this->google2fa->getQRCodeUrl(
            $appName,
            $email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(250),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($otpauthUrl);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code, 1);
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
        }

        return $codes;
    }

    public function enableMfa(Model $user, string $secret, string $code): bool|array
    {
        if (!$this->verifyCode($secret, $code)) {
            return false;
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'mfa_secret' => $secret,
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => json_encode($recoveryCodes),
        ]);

        return $recoveryCodes;
    }

    public function disableMfa(Model $user): void
    {
        $user->update([
            'mfa_secret' => null,
            'mfa_enabled_at' => null,
            'mfa_recovery_codes' => null,
        ]);
    }

    public function useRecoveryCode(Model $user, string $code): bool
    {
        $codes = json_decode($user->mfa_recovery_codes, true);

        if (!is_array($codes)) {
            return false;
        }

        $code = Str::upper(trim($code));
        $index = array_search($code, $codes);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $codes = array_values($codes);

        $user->update([
            'mfa_recovery_codes' => json_encode($codes),
        ]);

        return true;
    }

    public function getRemainingRecoveryCodes(Model $user): int
    {
        $codes = json_decode($user->mfa_recovery_codes, true);

        return is_array($codes) ? count($codes) : 0;
    }
}
