@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h5 class="fw-bold mb-1" style="color: #1e293b;">Welcome back</h5>
    <p class="text-muted mb-4" style="font-size: 0.85rem;">Sign in to your account to continue</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="name@company.com"
                    required
                    autofocus
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size: 0.78rem; color: #3b82f6; text-decoration: none;">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="input-group mt-1">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Remember Me --}}
        <div class="mb-4">
            <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember"
                    {{ old('remember') ? 'checked' : '' }}
                >
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-sign-in-alt me-2"></i> Sign In
        </button>
    </form>

    @if (Route::has('register'))
        <p class="text-center mt-3 mb-0" style="font-size: 0.835rem; color: #64748b;">
            Don't have an account?
            <a href="{{ route('register') }}" style="color: #3b82f6; text-decoration: none; font-weight: 600;">Create one</a>
        </p>
    @endif
@endsection
