<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestOtp extends Component
{
    public $email = '';

    protected $rules = [
        'email' => 'required|email'
    ];

    protected $messages = [
        'email.required' => 'Email harus diisi.',
        'email.email' => 'Format email tidak valid.',
    ];

    public function mount()
    {
        $this->email = session('email', '');
    }

    public function sendOtp()
    {
        $this->validate();

        // Cek user
        $user = User::where('email', $this->email)->first();
        
        if (!$user) {
            $this->addError('email', 'Email tidak terdaftar.');
            return;
        }

        // Generate OTP
        $otp = sprintf('%06d', random_int(100000, 999999));

        // GUNAKAN DB TRANSACTION
        try {
            DB::beginTransaction();

            // Delete old OTP
            DB::table('password_otps')->where('email', $this->email)->delete();

            // Insert new OTP - GUNAKAN DB::table LANGSUNG
            DB::table('password_otps')->insert([
                'email' => $this->email,
                'otp_hash' => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Verify insert berhasil
            $check = DB::table('password_otps')
                ->where('email', $this->email)
                ->latest('id')
                ->first();

            if (!$check) {
                throw new \Exception('Failed to insert OTP to database');
            }

            DB::commit();

            // Kirim email
            try {
                Mail::to($this->email)->send(new PasswordOtpMail($otp));
            } catch (\Exception $mailError) {
                // Email gagal, tapi OTP sudah di database
            }

            // Redirect dengan debug OTP
            return redirect()
                ->route('verify.otp', ['email' => $this->email])
                ->with('debug_otp', $otp);

        } catch (\Exception $e) {
            DB::rollBack();
            
            session()->flash('error', 'Gagal membuat OTP: ' . $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.auth.request-otp')
            ->layout('components.layouts.app');
    }
}