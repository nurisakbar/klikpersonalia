<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aplikasi Payroll KlikMedis')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    @stack('css')
</head>
<body class="hold-transition login-page">
    @yield('content')

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    
    <!-- Global SweetAlert Helper -->
    <script>
        // Global SweetAlert Helper Functions
        window.SwalHelper = {
            // Success Alert
            success: function(title, text = '', timer = 3000) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'success',
                    timer: timer,
                    showConfirmButton: false,
                    toast: false,
                    position: 'center'
                });
            },
            
            // Error Alert
            error: function(title, text = '', showConfirmButton = true) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'error',
                    showConfirmButton: showConfirmButton,
                    toast: false,
                    position: 'center'
                });
            },
            
            // Warning Alert
            warning: function(title, text = '', showConfirmButton = true) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showConfirmButton: showConfirmButton,
                    toast: false,
                    position: 'center'
                });
            },
            
            // Info Alert
            info: function(title, text = '', showConfirmButton = true) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'info',
                    showConfirmButton: showConfirmButton,
                    toast: false,
                    position: 'center'
                });
            }
        };

        // Auto-show alerts from Laravel session
        @if(session('success'))
            SwalHelper.success('Berhasil!', '{{ session("success") }}');
        @endif

        @if(session('status'))
            @if(session('status') == 'verification-link-sent')
                SwalHelper.success('Berhasil!', 'Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.');
            @else
                SwalHelper.success('Berhasil!', '{{ session("status") }}');
            @endif
        @endif

        @if(session('error'))
            SwalHelper.error('Error!', '{{ session("error") }}');
        @endif

        @if($errors->any())
            SwalHelper.error('Error!', '{!! implode("\\n", $errors->all()) !!}');
        @endif
    </script>
    
    @stack('js')
</body>
</html>
