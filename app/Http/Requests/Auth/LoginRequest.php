<?php

namespace App\Http\Requests\Auth;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 900);

            $user = User::where('email', $this->email)->first();
            if ($user) {
                LoginLog::create([
                    'user_id' => $user->id,
                    'tenant_id' => app('tenant')?->id,
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'success' => false,
                ]);
            }

            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas.',
            ]);
        }

        $user = Auth::user();

        LoginLog::create([
            'user_id' => $user->id,
            'tenant_id' => app('tenant')?->id,
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'success' => true,
        ]);

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = (int) ceil($seconds / 60);

        throw ValidationException::withMessages([
            'email' => "Muitas tentativas de login. Tente novamente em {$minutes} " . ($minutes === 1 ? 'minuto' : 'minutos') . '.',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email'))) . '|' . $this->ip();
    }
}
