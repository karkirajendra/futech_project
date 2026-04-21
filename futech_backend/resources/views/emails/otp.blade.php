<x-mail::message>
# Your OTP Code

Your OTP code for **{{ $purpose }}** is:

## {{ $otp }}

This code will expire in **5 minutes**.

**Do not share this code with anyone.**

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
