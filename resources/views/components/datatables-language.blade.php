{{-- DataTables Language Component --}}
<script>
// Test jQuery availability
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ === 'undefined') {
        console.error('jQuery is not available in DataTables Language component');
        // Try to wait a bit more for jQuery to load
        setTimeout(function() {
            if (typeof $ === 'undefined') {
                console.error('jQuery still not available after timeout');
            } else {
                console.log('jQuery became available after timeout');
            }
        }, 1000);
    } else {
        console.log('jQuery is available in DataTables Language component, version:', $.fn.jquery);
    }
});

window.DataTablesLanguage = {
    "sProcessing": "Memproses...",
    "sLengthMenu": "Tampilkan _MENU_ entri",
    "sZeroRecords": "Tidak ditemukan data yang sesuai",
    "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
    "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
    "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
    "sInfoPostFix": "",
    "sSearch": "Cari:",
    "oPaginate": {
        "sFirst": "Pertama",
        "sPrevious": "Sebelumnya",
        "sNext": "Selanjutnya",
        "sLast": "Terakhir"
    }
};
</script>
