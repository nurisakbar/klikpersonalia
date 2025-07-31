@extends('layouts.guest')

@section('title', 'Daftar Perusahaan - Payroll KlikMedis')

@section('content')
<div class="register-box">
    <div class="register-logo">
        <a href="{{ route('dashboard') }}">
            <img src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/AdminLTELogo.png" alt="Payroll KlikMedis" class="brand-image img-circle elevation-3" style="opacity: .8">
            <b>Payroll</b> KlikMedis
        </a>
    </div>

    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Daftar Perusahaan Baru</p>

            @if ($errors->any())
                <div class="alert alert-danger">
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
                    <h5 class="text-center mb-3">
                        <i class="fas fa-building"></i> Informasi Perusahaan
                    </h5>
                    
                    <div class="form-group">
                        <label for="company_name">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" required>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_email">Email Perusahaan <span class="text-danger">*</span></label>
                        <input type="email" name="company_email" id="company_email" class="form-control @error('company_email') is-invalid @enderror" value="{{ old('company_email') }}" required>
                        <small class="form-text text-muted" id="company_email_feedback"></small>
                        @error('company_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_phone">Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="company_phone" id="company_phone" class="form-control @error('company_phone') is-invalid @enderror" value="{{ old('company_phone') }}" required>
                        @error('company_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_address">Alamat <span class="text-danger">*</span></label>
                        <textarea name="company_address" id="company_address" class="form-control @error('company_address') is-invalid @enderror" rows="3" required>{{ old('company_address') }}</textarea>
                        @error('company_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_city">Kota <span class="text-danger">*</span></label>
                                <input type="text" name="company_city" id="company_city" class="form-control @error('company_city') is-invalid @enderror" value="{{ old('company_city') }}" required>
                                @error('company_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_province">Provinsi <span class="text-danger">*</span></label>
                                <input type="text" name="company_province" id="company_province" class="form-control @error('company_province') is-invalid @enderror" value="{{ old('company_province') }}" required>
                                @error('company_province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company_postal_code">Kode Pos <span class="text-danger">*</span></label>
                        <input type="text" name="company_postal_code" id="company_postal_code" class="form-control @error('company_postal_code') is-invalid @enderror" value="{{ old('company_postal_code') }}" required>
                        @error('company_postal_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_website">Website (Opsional)</label>
                        <input type="url" name="company_website" id="company_website" class="form-control @error('company_website') is-invalid @enderror" value="{{ old('company_website') }}">
                        @error('company_website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_tax_number">NPWP (Opsional)</label>
                                <input type="text" name="company_tax_number" id="company_tax_number" class="form-control @error('company_tax_number') is-invalid @enderror" value="{{ old('company_tax_number') }}">
                                @error('company_tax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_business_number">SIUP/NIB (Opsional)</label>
                                <input type="text" name="company_business_number" id="company_business_number" class="form-control @error('company_business_number') is-invalid @enderror" value="{{ old('company_business_number') }}">
                                @error('company_business_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary btn-block" onclick="nextStep()">
                        Selanjutnya <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Step 2: Owner Information -->
                <div class="step" id="step2" style="display: none;">
                    <h5 class="text-center mb-3">
                        <i class="fas fa-user"></i> Informasi Pemilik
                    </h5>
                    
                    <div class="form-group">
                        <label for="owner_name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="owner_name" id="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name') }}" required>
                        @error('owner_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="owner_email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="owner_email" id="owner_email" class="form-control @error('owner_email') is-invalid @enderror" value="{{ old('owner_email') }}" required>
                        <small class="form-text text-muted" id="owner_email_feedback"></small>
                        @error('owner_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="owner_phone">Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="owner_phone" id="owner_phone" class="form-control @error('owner_phone') is-invalid @enderror" value="{{ old('owner_phone') }}" required>
                        @error('owner_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="owner_position">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="owner_position" id="owner_position" class="form-control @error('owner_position') is-invalid @enderror" value="{{ old('owner_position') }}" required>
                        @error('owner_position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                        <small class="form-text text-muted">Minimal 8 karakter</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="terms_accepted" id="terms_accepted" class="custom-control-input @error('terms_accepted') is-invalid @enderror" required>
                            <label class="custom-control-label" for="terms_accepted">
                                Saya menyetujui <a href="#" target="_blank">Syarat dan Ketentuan</a> serta <a href="#" target="_blank">Kebijakan Privasi</a>
                            </label>
                            @error('terms_accepted')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-secondary btn-block" onclick="prevStep()">
                                <i class="fas fa-arrow-left"></i> Sebelumnya
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> Daftar
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-center">
                    Sudah punya akun? Login di sini
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
.register-box {
    width: 500px;
    margin: 7% auto;
}

.register-card-body {
    padding: 20px;
}

.step {
    transition: all 0.3s ease;
}

.form-group {
    margin-bottom: 1rem;
}

.custom-control-label {
    font-size: 0.875rem;
}

.custom-control-label a {
    color: #007bff;
    text-decoration: none;
}

.custom-control-label a:hover {
    text-decoration: underline;
}
</style>
@endpush

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

// Email availability check
document.getElementById('company_email').addEventListener('blur', function() {
    const email = this.value;
    if (email) {
        fetch('/check-company-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            const feedback = document.getElementById('company_email_feedback');
            if (data.available) {
                feedback.className = 'form-text text-success';
                feedback.textContent = data.message;
            } else {
                feedback.className = 'form-text text-danger';
                feedback.textContent = data.message;
            }
        });
    }
});

document.getElementById('owner_email').addEventListener('blur', function() {
    const email = this.value;
    if (email) {
        fetch('/check-owner-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            const feedback = document.getElementById('owner_email_feedback');
            if (data.available) {
                feedback.className = 'form-text text-success';
                feedback.textContent = data.message;
            } else {
                feedback.className = 'form-text text-danger';
                feedback.textContent = data.message;
            }
        });
    }
});
</script>
@endpush 