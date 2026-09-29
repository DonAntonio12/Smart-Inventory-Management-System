@extends('auth.layout')

@section('page_title', 'Log in')
@section('kicker', 'Welcome back')
@section('heading', 'Sign in to Stockroom')
@section('intro', 'Pick up where your team left off. Your inventory overview is one sign-in away.')

@section('content')
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required autofocus>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-options">
            <label class="remember" for="remember"><input id="remember" name="remember" type="checkbox" value="1"> Remember me</label>
            <a class="text-link" href="{{ route('password.request') }}">Forgot password?</a>
        </div>
        <button class="submit-button" type="submit">Log in
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </form>
    <p class="form-switch">New to Stockroom? <a href="{{ route('register') }}">Create an account</a></p>
@endsection
