<div class="btn-group" role="group">
    <a href="{{ route('employee-salary-components.show', $component) }}" 
       class="btn btn-info btn-sm" 
       title="Lihat Detail">
        <i class="fas fa-eye"></i>
    </a>
    
    <a href="{{ route('employee-salary-components.edit', $component) }}" 
       class="btn btn-warning btn-sm" 
       title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    
    <button type="button" 
            class="btn btn-{{ $component->is_active ? 'secondary' : 'success' }} btn-sm toggle-status-btn" 
            data-id="{{ $component->id }}"
            title="{{ $component->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
        <i class="fas fa-{{ $component->is_active ? 'pause' : 'play' }}"></i>
    </button>
    
    <button type="button" 
            class="btn btn-danger btn-sm delete-btn" 
            data-id="{{ $component->id }}"
            data-name="{{ $component->employee->name }} - {{ $component->salaryComponent->name }}"
            title="Hapus">
        <i class="fas fa-trash"></i>
    </button>
</div>

<script>
$(document).ready(function() {
    // Toggle status
    $('.toggle-status-btn').on('click', function() {
        const id = $(this).data('id');
        const btn = $(this);
        
        $.post('{{ route("employee-salary-components.toggle-status", ":id") }}'.replace(':id', id))
            .done(function(response) {
                if (response.success) {
                    // Update button appearance
                    if (btn.hasClass('btn-secondary')) {
                        btn.removeClass('btn-secondary').addClass('btn-success');
                        btn.find('i').removeClass('fa-pause').addClass('fa-play');
                        btn.attr('title', 'Aktifkan');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-secondary');
                        btn.find('i').removeClass('fa-play').addClass('fa-pause');
                        btn.attr('title', 'Nonaktifkan');
                    }
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload DataTable
                    $('#employee-components-table').DataTable().ajax.reload();
                }
            })
            .fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal mengubah status komponen gaji.'
                });
            });
    });
    
    // Delete
    $('.delete-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Apakah Anda yakin ingin menghapus komponen gaji "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("employee-salary-components.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Komponen gaji berhasil dihapus.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Reload DataTable
                        $('#employee-components-table').DataTable().ajax.reload();
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Gagal menghapus komponen gaji.'
                        });
                    }
                });
            }
        });
    });
});
</script>
