@extends('layouts.app')
@section('title', 'Agent Transactions')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Agent Transactions</h1>

</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'All Agent Transactions'])


    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="agent_transaction_table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Agent Name</th>
                    <th>Customer Name</th>
                    <th>Sales Invoice</th>
                    <th>CNF Qty</th>
                    <th>CNF Rate</th>
                    <th>CNF Cost</th>
                    <th>Tracking No.</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
        </table>
    </div>

    @endcomponent

</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
    $(document).ready(function() {
        var agent_transaction_table = $('#agent_transaction_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/agent_transaction',
            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'agent.name',
                    name: 'agent.name'
                },
                {
                    data: 'customer.name',
                    name: 'customer.name'
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no'
                },
                {
                    data: 'cnf_qty',
                    name: 'cnf_qty'
                },
                {
                    data: 'cnf_rate', // CNF Rate column
                    name: 'cnf_rate'
                },
                {
                    data: 'cnf_amount', // CNF Cost column
                    name: 'cnf_amount'
                },
                {
                    data: 'tracking_no', // Tracking No. column
                    name: 'tracking_no'
                },
                {
                    data: 'transaction.payment_status',
                    name: 'payment_status',
                    render: function(data, type, row) {
                        var labelClass, labelText;

                        switch (data) {
                            case 'paid':
                                labelClass = 'bg-light-green';
                                labelText = 'Paid';
                                break;
                            case 'partial':
                                labelClass = 'bg-yellow'; // You can choose any class for partial
                                labelText = 'Partial';
                                break;
                            case 'due':
                            default:
                                labelClass = 'bg-red';
                                labelText = 'Due';
                                break;
                        }

                        return '<span class="label ' + labelClass + '">' + labelText + '</span>';
                    }
                }

            ]
        });




    });
</script>
@endsection