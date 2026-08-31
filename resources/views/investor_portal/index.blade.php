@extends('layouts.investor')
@section('title', 'Investor Dashboard')

@section('content')
<section class="content">

    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-body">
                    <h4 class="text-center company-name">{{ Session::get('business.name') }}</h4>
                    <div class="dashboard-banner">Investor Dashboard</div>

                    <div class="row profile-block">
                        <div class="col-sm-6">
                            <div class="profile-line"><span class="profile-label">Investor Name:</span> {{ $investor->name }}</div>
                            <div class="profile-line"><span class="profile-label">Phone:</span> {{ $investor->phone }}</div>
                            <div class="profile-line"><span class="profile-label">Address:</span> {{ $investor->address }}</div>
                        </div>
                        <div class="col-sm-6 text-right">
                            <div class="profile-line"><span class="profile-label">Emergency Contact</span></div>
                            <div class="profile-line"><span class="profile-label">Name:</span> {{ $investor->emergency_contact_name }}</div>
                            <div class="profile-line"><span class="profile-label">Number:</span> {{ $investor->emergency_contact_number }}</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered summary-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Total Invested<br>Amount</th>
                                    <th class="text-center">Total Principal Paid<br>Amount</th>
                                    <th class="text-center">Total Paid<br>with Profit</th>
                                    <th class="text-center">Total Profit</th>
                                    <th class="text-center">Total Invest Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center summary-value cell-invested">@format_currency($summary['total_investment'])</td>
                                    <td class="text-center summary-value cell-principal">@format_currency($summary['total_principal_paid'])</td>
                                    <td class="text-center summary-value cell-paid">@format_currency($summary['total_paid_with_profit'])</td>
                                    <td class="text-center summary-value cell-profit">@format_currency($summary['total_profit'])</td>
                                    <td class="text-center summary-value cell-due">@format_currency($summary['total_due'])</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-body">
                    <h4 class="text-center list-heading">Invest Details List</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="investor_portal_table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>SL No</th>
                                    <th>Received Date</th>
                                    <th>Investor</th>
                                    <th>Invoice No</th>
                                    <th>Amount</th>
                                    <th>Received Account</th>
                                    <th>Return Amount</th>
                                    <th>Return Date</th>
                                    <th>Payment Status</th>
                                    <th>Remarks</th>
                                    <th>Loan Duration</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('css')
<style>
    .company-name { font-weight: 700; margin: 0 0 10px; }
    .dashboard-banner {
        background: #8ed17f; color: #1a1a1a; font-size: 20px; font-weight: 700;
        text-align: center; padding: 8px; margin-bottom: 20px;
    }
    .profile-block { margin-bottom: 20px; }
    .profile-line { font-size: 13px; margin-bottom: 4px; }
    .profile-label { font-weight: 700; }
    .summary-table > thead > tr > th { vertical-align: middle; font-size: 13px; }
    .summary-value { font-size: 18px; font-weight: 600; height: 60px; vertical-align: middle !important; }
    .cell-invested  { background: #dbe7f4; }
    .cell-principal { background: #f4dee6; }
    .cell-paid      { background: #e2efdb; }
    .cell-profit    { background: #fdf2d8; }
    .cell-due       { background: #f9dcdc; }
    .list-heading { font-weight: 700; margin: 0 0 15px; }
    #investor_portal_table { font-size: 12px; }
    @media print {
        .main-header, .main-footer, .dataTables_filter, .dataTables_length,
        .dataTables_paginate, .dataTables_info, .dt-buttons { display: none !important; }
        .content-wrapper { margin: 0 !important; }
        .box { border: none !important; box-shadow: none !important; }
    }
</style>
@endsection

@section('javascript')
<script>
$(document).ready(function(){
    var money = $.fn.dataTable.render.number(',', '.', 2);

    $('#investor_portal_table').DataTable({
        processing: true,
        serverSide: false,
        ajax: { url: '{{ url("/investor-portal/data") }}' },
        order: [[1, 'desc']],
        columns: [
            { data: 'id' },
            { data: 'received_date' },
            { data: 'investor_name', defaultContent: '' },
            { data: 'invoice_no', defaultContent: '' },
            { data: 'amount', render: money },
            { data: 'received_account_name', defaultContent: '' },
            { data: 'return_amount', render: function(d){ return d ? money.display(d) : ''; } },
            { data: 'return_date', defaultContent: '' },
            { data: 'status', render: function(d){
                var cls  = { paid: 'label-success', partial: 'label-warning', due: 'label-danger' };
                var text = { paid: 'Paid', partial: 'Partial', due: 'Due' };
                return '<span class="label ' + (cls[d] || 'label-default') + '">' + (text[d] || d) + '</span>';
            }},
            { data: 'remarks', defaultContent: '' },
            { data: 'loan_duration_days', render: function(d){
                return (d === null || d === undefined) ? '' : d + ' day(s)';
            }}
        ]
    });
});
</script>
@endsection
