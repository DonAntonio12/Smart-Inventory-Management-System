@extends('auth.layout')

@section('page_title', 'Choose a new password')
@section('kicker', 'Account recovery')
@section('heading', 'Choose a new password')
@section('intro', 'Create a new password for your Stockroom account. It must be at least 8 characters.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', request('email')) }}" placeholder="you@company.com" autocomplete="email" required autofocus>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="password">New password</label>
            <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" required>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Enter your password again" autocomplete="new-password" required>
        </div>
        <button class="submit-button" type="submit">Update password
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </form>
@endsection
