<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class ForgotPassword extends Component
{
    public string $email = '';

    public function sendOtp()
    {
         $this->validate(
        [
            'email' => ['required', 'email', 'exists:users,email'],
        ],
        [
            'email.exists' => 'Email ini tidak ditemukan atau belum terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]
    );

        $user = User::where('email', $this->email)->first();

        $otp = rand(100000, 999999);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new SendOtpMail($otp));

        session()->flash('status', 'OTP sudah dikirim ke email kamu.');
        return redirect()->route('verify.otp', ['email' => $this->email]);
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('components.layouts.auth.forgot');

    }
}
