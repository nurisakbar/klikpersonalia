@extends('layouts.guest')

@section('title', 'Lupa Password - Aplikasi Payroll KlikMedis')

@section('content')
<p class="login-box-msg">Lupa password? Masukkan email Anda untuk reset password</p>

@if (session('status'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-check"></i> Success!</h5>
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
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

    <div class="row">
        <div class="col-8">
            <a href="{{ route('login') }}" class="text-center">
                <i class="fas fa-arrow-left"></i> Kembali ke Login
            </a>
        </div>
        <!-- /.col -->
        <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane"></i> Kirim Link
            </button>
        </div>
        <!-- /.col -->
    </div>
</form>
@endsection
