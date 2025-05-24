@extends('layouts.app')
@section('title', 'Agents List')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Agent List</h1>

</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'All Agent List'])


    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="agent_table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Agent Name</th>
                    <th>Phone Number</th>
                    <th>Total Due</th>
                </tr>
            </thead>
        </table>
    </div>

    @endcomponent

</section>
<!-- /.content -->

@endsection

@push('js')
<script>
    $(document).ready(function() {
        var agent_table = $('#agent_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/agents',
            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'agent_name',
                    name: 'agent_name'
                },
                {
                    data: 'agent_phone_number',
                    name: 'agent_phone_number'
                },
                {
                    data: 'total_due',
                    name: 'total_due'
                }
            ]
        });


    });
</script>
@endpush