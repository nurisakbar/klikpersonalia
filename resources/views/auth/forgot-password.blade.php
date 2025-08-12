@extends('layouts.guest')

@section('title', 'Forgot Password - Payroll KlikMedis')

@section('content')
<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="/" class="h1">Payroll KlikMedis</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">You forgot your password? Here you can easily retrieve a new password.</p>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email" value="{{ old('email') }}" required autofocus>
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
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Send Link
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
