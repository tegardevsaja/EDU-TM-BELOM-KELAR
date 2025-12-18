<x-mail::message>
# Reset Password Akun Kamu

Halo,

Kami menerima permintaan untuk *reset password* akun kamu di **{{ config('app.name') }}**.
Jika kamu tidak merasa melakukan permintaan ini, kamu bisa abaikan email ini.

<x-mail::panel>
Kode OTP rahasia kamu:

<span style="font-size: 28px; font-weight: 700; letter-spacing: 6px; display: inline-block; margin: 8px 0;">
    {{ $otp }}
</span>

<span style="font-size: 12px; color: #6b7280;">Jangan berikan kode ini kepada siapa pun, termasuk pihak yang mengaku dari {{ config('app.name') }}.</span>
</x-mail::panel>

- Kode hanya berlaku selama **10 menit**.
- Setelah digunakan, kode ini tidak bisa dipakai kembali.

Jika kamu merasa ini adalah kamu, lanjutkan proses reset password di halaman aplikasi lalu masukkan kode di atas.

Terima kasih telah menggunakan {{ config('app.name') }},
<br>
{{ config('app.name') }}
</x-mail::message>
