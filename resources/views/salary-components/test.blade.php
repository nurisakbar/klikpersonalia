@extends('layouts.app')

@section('title', 'Test Salary Components')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Test Salary Components</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Simple DataTable Test</h3>
            </div>
            <div class="card-body">
                <table id="test-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Document ready');
    
    var table = $('#test-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("salary-components.data") }}',
            type: 'GET',
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.log('DataTable error:', error);
                console.log('Response:', xhr.responseText);
            }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'type', name: 'type'},
            {data: 'is_active', name: 'is_active'}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
    
    console.log('DataTable initialized');
});
</script>
@endpush
