@extends('layouts.guest')

@section('title', 'Login - Aplikasi Payroll KlikMedis')

@section('content')
<p class="login-box-msg">Masuk ke aplikasi Payroll KlikMedis</p>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="input-group mb-3">
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}" required autofocus>
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-envelope"></span>
            </div>
        </div>
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required>
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock"></span>
            </div>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="row">
        <div class="col-8">
            <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">
                    Ingat Saya
                </label>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </div>
        <!-- /.col -->
    </div>
</form>

@if (Route::has('password.request'))
<p class="mb-1">
    <a href="{{ route('password.request') }}" class="text-center">
        <i class="fas fa-key"></i> Lupa Password?
    </a>
</p>
@endif

@if (Route::has('register'))
<p class="mb-0">
    <a href="{{ route('company.register.form') }}" class="text-center">
        <i class="fas fa-building"></i> Daftar Perusahaan Baru
    </a>
</p>
@endif
@endsection
