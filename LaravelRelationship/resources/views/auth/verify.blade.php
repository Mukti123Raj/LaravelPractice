@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-xl py-10">
    <h1 class="text-2xl font-bold mb-4">Verify Your Email Address</h1>

    @if (session('status') === 'verification-link-sent')
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <p class="mb-6">Before proceeding, please check your email for a verification link. If you did not receive the email, you can request another.</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Resend verification email</button>
    </form>
</div>
@endsection


