<x-guest-layout bodyClass="login-page" boxClass="login-box" title="Verify Email">
    <p class="login-box-msg">Thanks for signing up! Before getting started, please verify your email address by clicking the link we just emailed to you.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-3" role="alert">
            A new verification link has been sent to the email address you provided during registration.
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-muted font-weight-bold">Log Out</button>
        </form>
    </div>
</x-guest-layout>

