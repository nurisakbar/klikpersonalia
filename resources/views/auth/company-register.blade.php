@extends('layouts.guest')

@section('title', 'Register Company - Payroll KlikMedis')

@section('content')
<div class="register-box">
    <div class="register-logo">
        <a href="/"><b>Payroll</b> KlikMedis</a>
    </div>

    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Register a new company membership</p>

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

            <form action="{{ route('company.register') }}" method="POST" id="registrationForm">
                @csrf
                
                <!-- Step 1: Company Information -->
                <div class="step" id="step1">
                    <h6 class="text-center mb-3">
                        <i class="fas fa-building"></i> Company Information
                    </h6>
                    
                    <div class="input-group mb-3">
                        <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                               placeholder="Company name" value="{{ old('company_name') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-building"></span>
                            </div>
                        </div>
                        @error('company_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="email" name="company_email" id="company_email" class="form-control @error('company_email') is-invalid @enderror" 
                               placeholder="Company email" value="{{ old('company_email') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('company_email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" name="company_phone" id="company_phone" class="form-control @error('company_phone') is-invalid @enderror" 
                               placeholder="Phone number" value="{{ old('company_phone') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-phone"></span>
                            </div>
                        </div>
                        @error('company_phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <textarea name="company_address" id="company_address" class="form-control @error('company_address') is-invalid @enderror" 
                                  placeholder="Company address" rows="2" required>{{ old('company_address') }}</textarea>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-map-marker-alt"></span>
                            </div>
                        </div>
                        @error('company_address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="input-group mb-3">
                                <input type="text" name="company_city" id="company_city" class="form-control @error('company_city') is-invalid @enderror" 
                                       placeholder="City" value="{{ old('company_city') }}" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-city"></span>
                                    </div>
                                </div>
                                @error('company_city')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group mb-3">
                                <input type="text" name="company_province" id="company_province" class="form-control @error('company_province') is-invalid @enderror" 
                                       placeholder="Province" value="{{ old('company_province') }}" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-map"></span>
                                    </div>
                                </div>
                                @error('company_province')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" name="company_postal_code" id="company_postal_code" class="form-control @error('company_postal_code') is-invalid @enderror" 
                               placeholder="Postal code" value="{{ old('company_postal_code') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-mail-bulk"></span>
                            </div>
                        </div>
                        @error('company_postal_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="button" class="btn btn-primary btn-block" onclick="nextStep()">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Step 2: Owner Information -->
                <div class="step" id="step2" style="display: none;">
                    <h6 class="text-center mb-3">
                        <i class="fas fa-user"></i> Owner Information
                    </h6>
                    
                    <div class="input-group mb-3">
                        <input type="text" name="owner_name" id="owner_name" class="form-control @error('owner_name') is-invalid @enderror" 
                               placeholder="Full name" value="{{ old('owner_name') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        @error('owner_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="email" name="owner_email" id="owner_email" class="form-control @error('owner_email') is-invalid @enderror" 
                               placeholder="Email" value="{{ old('owner_email') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('owner_email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" name="owner_phone" id="owner_phone" class="form-control @error('owner_phone') is-invalid @enderror" 
                               placeholder="Phone number" value="{{ old('owner_phone') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-phone"></span>
                            </div>
                        </div>
                        @error('owner_phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" name="owner_position" id="owner_position" class="form-control @error('owner_position') is-invalid @enderror" 
                               placeholder="Position" value="{{ old('owner_position') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-briefcase"></span>
                            </div>
                        </div>
                        @error('owner_position')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
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

                    <div class="input-group mb-3">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                               placeholder="Retype password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" name="terms_accepted" id="terms_accepted" class="@error('terms_accepted') is-invalid @enderror" required>
                                <label for="terms_accepted">
                                    I agree to the <a href="#">terms</a>
                                </label>
                                @error('terms_accepted')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </div>
                        <!-- /.col -->
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-secondary btn-block" onclick="prevStep()">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="social-auth-links text-center mt-2 mb-3">
                <p>- OR -</p>
                <a href="{{ route('login') }}" class="btn btn-block btn-success">
                    <i class="fas fa-sign-in-alt mr-2"></i> I already have a membership
                </a>
            </div>
        </div>
        <!-- /.form-box -->
    </div><!-- /.card -->
</div>
<!-- /.register-box -->
@endsection

@push('js')
<script>
let currentStep = 1;

function nextStep() {
    if (validateStep1()) {
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        currentStep = 2;
    }
}

function prevStep() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
    currentStep = 1;
}

function validateStep1() {
    const requiredFields = [
        'company_name', 'company_email', 'company_phone', 'company_address',
        'company_city', 'company_province', 'company_postal_code'
    ];
    
    let isValid = true;
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field);
        if (!element.value.trim()) {
            element.classList.add('is-invalid');
            isValid = false;
        } else {
            element.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Add loading state to submit button
$('form').on('submit', function() {
    $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    $('button[type="submit"]').prop('disabled', true);
});

// Auto-hide alerts after 10 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 10000);
</script>
@endpush 