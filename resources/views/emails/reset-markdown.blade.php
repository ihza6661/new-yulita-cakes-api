<x-mail::message>
    # Reset Password Notification

    Halo, {{ $user->name }}!

    Kami menerima permintaan reset password untuk akun Anda di {{ config('app.name') }}.

    Klik tombol di bawah ini untuk mereset password Anda:

    <x-mail::button :url="$resetLink">
        Reset Password
    </x-mail::button>

    Tautan ini akan kadaluarsa dalam {{ config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60) }}
    menit.

    Jika Anda tidak merasa meminta reset password, abaikan saja email ini.

    Terima kasih,<br>
    {{ config('app.name') }}

    <x-slot:subcopy>
        Jika Anda kesulitan mengklik tombol "Reset Password", salin dan tempel URL berikut ke browser Anda:
        [{{ $resetLink }}]({{ $resetLink }})
    </x-slot:subcopy>
</x-mail::message>
