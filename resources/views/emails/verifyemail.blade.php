<x-mail::message>
# Hai, {{$user->name}}

Terimakasih sudah mendaftarkan diri di **Vocaject**, silahkan tekan tombol berikut untuk melakukan verifikasi email anda.

<x-mail::button :url="route('auth.email.verify', ['id' => $user->id])">
Verifikasi Email
</x-mail::button>

Terimakasih,<br>
{{ config('app.name') }}
</x-mail::message>
