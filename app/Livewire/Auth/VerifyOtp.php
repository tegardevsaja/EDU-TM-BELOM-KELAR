<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\PasswordOtp;
use Illuminate\Support\Facades\Hash;

class VerifyOtp extends Component
{
    public $email;
    public $otp = '';

    protected $rules = [
        'otp' => 'required|digits:6',
    ];

    protected $messages = [
        'otp.required' => 'Kode OTP harus diisi.',
        'otp.digits' => 'Kode OTP harus 6 digit.',
    ];

    public function mount($email)
    {
        $this->email = $email;
    }

    public function verify()
    {
        $this->validate();

        $record = PasswordOtp::where('email', $this->email)
                    ->latest()
                    ->first();

        if (!$record) {
            session()->flash('error', 'Kode OTP tidak ditemukan. Silakan minta OTP baru.');
            return;
        }

        if ($record->isExpired()) {
            session()->flash('error', 'Kode OTP sudah kedaluwarsa. Silakan minta OTP baru.');
            return;
        }

        // Verifikasi OTP dengan Hash::check
        if (!Hash::check($this->otp, $record->otp_hash)) {
            session()->flash('error', 'Kode OTP salah. Silakan coba lagi.');
            return;
        }

        $record->delete();

        // Redirect ke form reset password
        return redirect()->route('reset.password.form', ['email' => $this->email])
            ->with('success', 'OTP berhasil diverifikasi. Silakan reset password Anda.');
    }

        public function resendOtp()
    {
        return redirect()->route('request.otp')
            ->with('email', $this->email)
            ->with('info', 'Silakan minta OTP baru.');
    }


    public function render()
    {
        return view('livewire.auth.verify-otp')
            ->layout('components.layouts.auth.otp');
    }
}