@extends('auth.layout')

@section('page_title', 'Create account')
@section('kicker', 'Get started')
@section('heading', 'Create your account')
@section('intro', 'Set up your Stockroom workspace and get a clearer view of your inventory.')

@section('content')
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="field">
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Jamie Rivera" autocomplete="name" required autofocus>
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" required>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Enter your password again" autocomplete="new-password" required>
        </div>
        <button class="submit-button" type="submit">Create account
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </form>
    <p class="form-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
