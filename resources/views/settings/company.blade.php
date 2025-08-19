@extends('layouts.app')

@section('title', 'Pengaturan Perusahaan - Aplikasi Payroll KlikMedis')
@section('page-title', 'Pengaturan Perusahaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Pengaturan Perusahaan</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('settings.update-company') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Company Logo -->
                            <div class="col-md-3">
                                <div class="text-center mb-4">
                                    <div class="company-logo-container">
                                        @if($company->logo)
                                            <img src="{{ Storage::url($company->logo) }}" 
                                                 alt="Company Logo" 
                                                 class="img-fluid company-logo mb-3"
                                                 style="max-width: 200px; max-height: 200px;">
                                        @else
                                            <div class="company-logo-placeholder mb-3">
                                                <i class="fas fa-building fa-5x text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="logo" class="form-label">Logo Perusahaan</label>
                                        <input type="file" 
                                               class="form-control @error('logo') is-invalid @enderror" 
                                               id="logo" 
                                               name="logo" 
                                               accept="image/*">
                                        <small class="form-text text-muted">
                                            Ukuran rekomendasi: 200x200px. Ukuran maksimal: 2MB.
                                        </small>
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Company Information -->
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Nama Perusahaan *</label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name', $company->name) }}" 
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email Perusahaan *</label>
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email', $company->email) }}" 
                                                   required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Telepon Perusahaan *</label>
                                            <input type="text" 
                                                   class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" 
                                                   name="phone" 
                                                   value="{{ old('phone', $company->phone) }}" 
                                                   required>
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="website" class="form-label">Website Perusahaan</label>
                                            <input type="url" 
                                                   class="form-control @error('website') is-invalid @enderror" 
                                                   id="website" 
                                                   name="website" 
                                                   value="{{ old('website', $company->website) }}">
                                            @error('website')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="address" class="form-label">Alamat Perusahaan *</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              required>{{ old('address', $company->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city" class="form-label">Kota *</label>
                                            <input type="text" 
                                                   class="form-control @error('city') is-invalid @enderror" 
                                                   id="city" 
                                                   name="city" 
                                                   value="{{ old('city', $company->city) }}" 
                                                   required>
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                                <label for="province" class="form-label">Provinsi *</label>
                                            <input type="text" 
                                                   class="form-control @error('province') is-invalid @enderror" 
                                                   id="province" 
                                                   name="province" 
                                                   value="{{ old('province', $company->province) }}" 
                                                   required>
                                            @error('province')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="postal_code" class="form-label">Kode Pos *</label>
                                            <input type="text" 
                                                   class="form-control @error('postal_code') is-invalid @enderror" 
                                                   id="postal_code" 
                                                   name="postal_code" 
                                                   value="{{ old('postal_code', $company->postal_code) }}" 
                                                   required>
                                            @error('postal_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="country" class="form-label">Negara *</label>
                                            <input type="text" 
                                                   class="form-control @error('country') is-invalid @enderror" 
                                                   id="country" 
                                                   name="country" 
                                                   value="{{ old('country', $company->country) }}" 
                                                   required>
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax_number" class="form-label">Nomor NPWP</label>
                                            <input type="text" 
                                                   class="form-control @error('tax_number') is-invalid @enderror" 
                                                   id="tax_number" 
                                                   name="tax_number" 
                                                   value="{{ old('tax_number', $company->tax_number) }}">
                                            @error('tax_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="business_number" class="form-label">Nomor SIUP/NIB</label>
                                    <input type="text" 
                                           class="form-control @error('business_number') is-invalid @enderror" 
                                           id="business_number" 
                                           name="business_number" 
                                           value="{{ old('business_number', $company->business_number) }}">
                                    @error('business_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Ringkasan Informasi Perusahaan
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Plan Langganan:</strong></td>
                                                        <td>
                                                            <span class="badge badge-{{ $company->subscription_plan === 'premium' ? 'success' : 'warning' }}">
                                                                {{ ucfirst($company->subscription_plan) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Maksimal Karyawan:</strong></td>
                                                        <td>{{ $company->max_employees }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Status Perusahaan:</strong></td>
                                                        <td>
                                                            <span class="badge badge-{{ $company->status === 'active' ? 'success' : 'danger' }}">
                                                                {{ ucfirst($company->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Dibuat:</strong></td>
                                                        <td>{{ $company->created_at->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Diperbarui:</strong></td>
                                                        <td>{{ $company->updated_at->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total Pengguna:</strong></td>
                                                        <td>{{ $company->users()->count() }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Perbarui Profil Perusahaan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.company-logo-container {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 20px;
    background-color: #f8f9fa;
}

.company-logo-placeholder {
    width: 200px;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e9ecef;
    border-radius: 8px;
    margin: 0 auto;
}

.company-logo {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script>
// Preview logo before upload
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.querySelector('.company-logo-container');
            container.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid company-logo mb-3" style="max-width: 200px; max-height: 200px;">`;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush 