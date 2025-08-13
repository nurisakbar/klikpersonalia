@extends('layouts.guest')

@section('title', 'Reset Password - Payroll KlikMedis')

@section('content')
<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="/" class="h1">Payroll KlikMedis</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Reset your password</p>

            {{-- Error messages akan ditampilkan melalui SweetAlert dari guest layout --}}

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email" value="{{ old('email', $request->email) }}" required autofocus>
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
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           placeholder="New password" required>
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

                <div class="input-group mb-3">
                    <input type="password" name="password_confirmation" class="form-control" 
                           placeholder="Confirm new password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-8">
                        <a href="{{ route('login') }}" class="text-center">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <div class="social-auth-links text-center mt-2 mb-3">
                <p>- OR -</p>
                <a href="{{ route('company.register.form') }}" class="btn btn-block btn-success">
                    <i class="fas fa-building mr-2"></i> Register New Company
                </a>
            </div>

            <p class="mb-0 text-center">
                <a href="/" class="text-center">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </p>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
<!-- /.login-box -->
@endsection

@push('js')
<script>
$(function () {
    // Auto-hide alerts after 10 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 10000);

    // Focus on email field when page loads
    $('input[name="email"]').focus();

    // Add loading state to submit button
    $('form').on('submit', function() {
        $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        $('button[type="submit"]').prop('disabled', true);
    });
});
</script>
@endpush
