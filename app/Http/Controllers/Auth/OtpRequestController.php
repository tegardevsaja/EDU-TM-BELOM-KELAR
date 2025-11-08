<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordOtpMail;
use Carbon\Carbon;

class OtpRequestController extends Controller
{
    public function showForm()
    {
        return view('request-otp');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Cek user
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar.']);
        }

        // Generate OTP
        $otp = sprintf('%06d', random_int(100000, 999999));

        try {
            DB::beginTransaction();

            DB::table('password_otps')->where('email', $request->email)->delete();

            DB::table('password_otps')->insert([
                'email' => $request->email,
                'otp_hash' => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            try {
                Mail::to($request->email)->send(new PasswordOtpMail($otp));
            } catch (\Exception $e) {
            }

            return redirect()
                ->route('verify.otp', ['email' => $request->email])
                ->with('debug_otp', $otp)
                ->with('success', 'Kode OTP telah dikirim.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}