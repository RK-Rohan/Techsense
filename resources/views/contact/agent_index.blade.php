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

    @slot('tool')
    <div class="box-tools">
        <button type="button" class="btn btn-block btn-primary btn-modal" data-toggle="modal" data-target="#addAgentModal">
            <i class="fa fa-plus"></i> @lang('messages.add')</button>
    </div>
    @endslot


    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="agent_table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Agent Name</th>
                    <th>Contact ID</th>
                    <th>Phone Number</th>
                    <th>Total Due</th>
                </tr>
            </thead>
        </table>
    </div>

    @endcomponent

</section>
<!-- /.content -->

<div class="modal fade" id="addAgentModal" tabindex="-1" role="dialog" aria-labelledby="addAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAgentModalLabel">Add New Agent</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addAgentForm">
                    <div class="form-group">
                        {!! Form::label('agent_name', 'Agent Name' . ':*') !!}
                        {!! Form::text('agent_name', null, ['class' => 'form-control', 'required', 'id' => 'agent_name']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('agent_phone_number', 'Agent Phone Number' . ':*') !!}
                        {!! Form::text('agent_phone_number', null, ['class' => 'form-control', 'required', 'id' => 'agent_phone_number']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('agent_address', 'Agent Address' . ':') !!}
                        {!! Form::textarea('agent_address', null, ['class' => 'form-control', 'rows' => 3, 'id' => 'agent_address']) !!}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAgentButton">Save Agent</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function() {
        var agent_table = $('#agent_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/contacts/agents',
            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'contact_id',
                    name: 'contact_id'
                },
                {
                    data: 'mobile',
                    name: 'mobile'
                },
                {
                    data: 'total_due',
                    name: 'total_due'
                }
            ]
        });

        $('#saveAgentButton').on('click', function(e) {
            e.preventDefault(); // Prevent the default form submission

            var agentName = $('#agent_name').val();
            var agentPhoneNumber = $('#agent_phone_number').val();
            var agentAddress = $('#agent_address').val();

            if (agentName && agentPhoneNumber) {
                $.ajax({
                    url: '/contacts/agents/store',
                    type: 'POST',
                    data: {
                        agent_name: agentName,
                        agent_phone_number: agentPhoneNumber,
                        agent_address: agentAddress
                    },
                    success: function(response) {
                        $('#addAgentModal').modal('hide');
                        $('#addAgentForm')[0].reset();
                        agent_table.ajax.reload();
                        toastr.success(response.message);

                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        alert('Failed to save the agent. Please try again.');
                    }
                });

            } else {
                alert('Please fill in all the required fields.');
            }
        });


    });
</script>
@endpush