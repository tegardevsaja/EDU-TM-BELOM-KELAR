<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Features;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Enums\UserRole;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login()
    {
        
        $this->validate();

        $this->ensureIsNotRateLimited();

        $user = $this->validateCredentials();

        // 2FA disabled: skip two-factor challenge entirely

        Auth::login($user, $this->remember);

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $userRole = auth()->user()->role;

if ($userRole === UserRole::Master) {
    return redirect()->route('master.dashboard')->with('success', 'Berhasil login');
} elseif ($userRole === UserRole::Admin) {
    return redirect()->route('admin.dashboard')->with('success', 'Berhasil login');
} elseif ($userRole === UserRole::Guru) {
    return redirect()->route('guru.dashboard')->with('success', 'Berhasil login');
}

        // $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Validate the user's credentials.
     */
  protected function validateCredentials(): User
{
    // Cari user berdasarkan email dulu
    $user = Auth::getProvider()->retrieveByCredentials(['email' => $this->email]);

    if (! $user) {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'Akun dengan email ini tidak ditemukan.',
        ]);
    }

    // Kalau email ada tapi password salah
    if (! Auth::getProvider()->validateCredentials($user, ['password' => $this->password])) {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'password' => 'Password yang kamu masukkan salah.',
        ]);
    }

    return $user;
}


    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
