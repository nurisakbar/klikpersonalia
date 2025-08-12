@extends('layouts.guest')

@section('title', 'Login - Aplikasi Payroll KlikMedis')

@section('content')
<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="/" class="h1">Payroll KlikMedis</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Sign in to start your session</p>

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

            <!-- Demo Credentials Card -->
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h6><i class="icon fas fa-info"></i> Demo Credentials</h6>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Admin:</strong><br>
                        Email: admin@klikmedis.com<br>
                        Password: password
                    </div>
                    <div class="col-md-6">
                        <strong>Demo:</strong><br>
                        Email: demo@klikmedis.com<br>
                        Password: password
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}">
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
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Password" required>
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
                                Remember Me
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Sign In
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

            @if (Route::has('password.request'))
            <p class="mb-1">
                <a href="{{ route('password.request') }}" class="text-center">
                    <i class="fas fa-key"></i> I forgot my password
                </a>
            </p>
            @endif

            <p class="mb-0">
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
