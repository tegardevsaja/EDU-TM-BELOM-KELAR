<?php

namespace App\Http\Controllers;

use App\Models\PasswordOtp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $otp = rand(100000, 999999);

        PasswordOtp::create([
            'email' => $request->email,
            'otp_hash' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        return back()->with('success', 'Kode OTP telah dikirim!');
    }
}
