<x-mail::message>
# Kode OTP Reset Password

Halo,

Untuk membantu proses reset password akunmu di **EDU TM**, kami mengirimkan kode OTP di bawah ini.

<x-mail::panel>
<div style="font-size: 12px; color: #6b7280; margin-bottom: 6px;">Kode OTP kamu:</div>

<div style="font-size: 28px; font-weight: 800; letter-spacing: 6px; display: inline-block; margin: 4px 0 10px; color:#334155;">
    {{ $otp }}
    </div>

<div style="font-size: 12px; color: #6b7280;">Jangan bagikan kode ini kepada siapa pun.</div>
</x-mail::panel>

- Kode berlaku selama **10 menit** sejak email ini diterima.
- Demi keamanan, setelah berhasil login kami sarankan segera mengganti password.

Jika kamu tidak merasa meminta reset password, abaikan email ini — akunmu akan tetap aman.

Terima kasih,
<br>
EDU TM
</x-mail::message>
