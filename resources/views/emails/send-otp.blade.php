<x-mail::message>
# Kode OTP Reset Password

Halo!

Gunakan kode berikut untuk reset password kamu:

# **{{ $otp }}**

Kode hanya berlaku selama 10 menit.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
