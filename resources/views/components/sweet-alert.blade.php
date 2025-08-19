<!-- Sweet Alert CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- Sweet Alert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global Sweet Alert Helper
window.SwalHelper = {
    // Clear any existing alerts on initialization
    _clearExistingAlerts: function() {
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
    },
    // Success Toast
    toastSuccess: function(message, duration = 3000) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            background: '#d4edda',
            color: '#155724',
            iconColor: '#28a745'
        });
    },

    // Error Toast
    toastError: function(message, duration = 3000) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            background: '#f8d7da',
            color: '#721c24',
            iconColor: '#dc3545'
        });
    },

    // Warning Toast
    toastWarning: function(message, duration = 3000) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            background: '#fff3cd',
            color: '#856404',
            iconColor: '#ffc107'
        });
    },

    // Info Toast
    toastInfo: function(message, duration = 3000) {
        Swal.fire({
            icon: 'info',
            title: 'Informasi!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            background: '#d1ecf1',
            color: '#0c5460',
            iconColor: '#17a2b8'
        });
    },

    // Success Modal
    success: function(title, message, callback = null) {
        Swal.fire({
            icon: 'success',
            title: title || 'Berhasil!',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    // Error Modal
    error: function(title, message, callback = null) {
        Swal.fire({
            icon: 'error',
            title: title || 'Error!',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    // Warning Modal
    warning: function(title, message, callback = null) {
        Swal.fire({
            icon: 'warning',
            title: title || 'Peringatan!',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc107'
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    // Confirmation Modal
    confirm: function(title, message, callback = null) {
        Swal.fire({
            icon: 'question',
            title: title || 'Konfirmasi',
            text: message,
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    // Delete Confirmation
    confirmDelete: function(title, message, callback = null) {
        Swal.fire({
            icon: 'warning',
            title: title || 'Konfirmasi Hapus',
            text: message || 'Apakah Anda yakin ingin menghapus data ini?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    },

    // Loading Modal
    loading: function(title = 'Memproses...') {
        Swal.fire({
            title: title,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },

    // Close Loading Modal
    closeLoading: function() {
        Swal.close();
    },

    // Form Validation Error
    showValidationErrors: function(errors) {
        let errorMessages = '';
        if (typeof errors === 'object') {
            Object.keys(errors).forEach(key => {
                errorMessages += errors[key].join('<br>') + '<br>';
            });
        } else {
            errorMessages = errors;
        }

        Swal.fire({
            icon: 'error',
            title: 'Validasi Error!',
            html: errorMessages,
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    }
};

// Clear any existing alerts when page loads
$(document).ready(function() {
    SwalHelper._clearExistingAlerts();
});

// Global AJAX Error Handler
$(document).ajaxError(function(event, xhr, settings, error) {
    // Skip if error is already handled manually
    if (settings.errorHandled) {
        return;
    }
    
    // Skip DataTables AJAX requests (they handle their own errors)
    if (settings.url && settings.url.includes('data')) {
        return;
    }
    
    // Skip if it's a form submission (let form handle its own errors)
    if (settings.data && settings.data instanceof FormData) {
        return;
    }
    
    let message = 'Terjadi kesalahan. Silakan coba lagi.';
    
    if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
        SwalHelper.showValidationErrors(xhr.responseJSON.errors);
        return;
    } else if (xhr.status === 401) {
        message = 'Sesi Anda telah berakhir. Silakan login kembali.';
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
    } else if (xhr.status === 403) {
        message = 'Anda tidak memiliki izin untuk melakukan aksi ini.';
    } else if (xhr.status === 404) {
        message = 'Data tidak ditemukan.';
    } else if (xhr.status === 422) {
        message = 'Data yang dimasukkan tidak valid.';
    } else if (xhr.status === 500) {
        message = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
    }

    SwalHelper.toastError(message);
});

// Global Success Handler
$(document).ajaxSuccess(function(event, xhr, settings) {
    if (xhr.responseJSON && xhr.responseJSON.success && xhr.responseJSON.message) {
        SwalHelper.toastSuccess(xhr.responseJSON.message);
    }
});
</script>
