<x-mail::message>
# Hai, {{$user->name}}

Kami baru saja menerima permintaan penyetelan ulang kata sandi untuk {{$user->email}}.

Kode ini hanya bisa digunakan satu kali. Jika anda tidak meminita kode, abaikan email ini. Jangan pernah membagikan kode ini kepada orang lain.

**Kode OTP:**

**{{ $code_otp }}**

Terimakasih,<br>
{{ config('app.name') }}
</x-mail::message>
