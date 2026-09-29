@extends('auth.layout')

@section('page_title', 'Forgot password')
@section('kicker', 'Account recovery')
@section('heading', 'Reset your password')
@section('intro', 'Enter the email address linked to your account. We’ll send a secure reset link if it matches.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required autofocus>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <button class="submit-button" type="submit">Send reset link
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4V6Zm0 1 8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </form>
    <p class="form-switch"><a href="{{ route('login') }}">Back to log in</a></p>
@endsection
