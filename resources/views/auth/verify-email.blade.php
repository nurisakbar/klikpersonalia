@extends('layouts.guest')

@section('title', 'Verifikasi Email - Aplikasi Payroll KlikMedis')

@section('content')
<div class="text-center">
    <h4><i class="fas fa-envelope-open-text"></i> Verifikasi Email Anda</h4>
    <p class="login-box-msg">
        Terima kasih telah mendaftar! Sebelum memulai, dapatkah Anda memverifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda? Jika Anda tidak menerima email tersebut, kami akan dengan senang hati mengirimkan email lain kepada Anda.
    </p>

    {{-- Status messages akan ditampilkan melalui SweetAlert dari guest layout --}}

    <div class="row">
        <div class="col-12">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Kirim Ulang Email Verifikasi
                </button>
            </form>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-block">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
