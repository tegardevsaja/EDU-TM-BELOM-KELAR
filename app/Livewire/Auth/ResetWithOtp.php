<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetWithOtp extends Component
{
    public $email;
    public $password = '';
    public $password_confirmation = '';

    protected function rules()
    {
        return [
            'password' => [
                'required',
                'string',
                'confirmed',
                PasswordRule::min(8)
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    protected $messages = [
        'password.required' => 'Password baru harus diisi.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'password.*' => 'Password harus minimal 8 karakter, mengandung angka dan simbol, dan sama dengan konfirmasi.',
    ];

    public function mount($email)
    {
        // Validasi email dari parameter route
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            abort(404, 'Email tidak valid');
        }

        $this->email = $email;
    }

    public function resetPassword()
    {
        $this->validate();

        // Cari user berdasarkan email
        $user = User::where('email', $this->email)->first();

        if (!$user) {
            session()->flash('error', 'User dengan email ini tidak ditemukan.');
            return;
        }

        // Update password
        $user->password = Hash::make($this->password);
        $user->save();

        // Redirect ke login dengan pesan sukses
        return redirect()->route('login')
            ->with('success', 'Password berhasil direset! Silakan login dengan password baru Anda.');
    }

    public function render()
    {
        return view('livewire.auth.reset-with-otp')
            ->layout('components.layouts.app');
    }
}