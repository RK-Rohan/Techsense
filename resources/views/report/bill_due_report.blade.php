@extends('layouts.app')
@section('title', __('Bill Due Report'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Bill Due Report')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <div class="row no-print">
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('business.business_locations') . ':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'id' => 'location_id', 'style' => 'width:100%']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('customer_id', __('contact.customer') . ':') !!}
                        {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2', 'id' => 'customer_id', 'style' => 'width:100%']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('created_by', __('report.user') . ':') !!}
                        {!! Form::select('created_by', $created_by, null, ['class' => 'form-control select2', 'id' => 'created_by', 'style' => 'width:100%']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date_range', 'readonly']) !!}
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="bill_due_report_table">
                    <thead>
                        <tr>
                            <th>@lang('messages.date')</th>
                            <th>@lang('sale.invoice_no')</th>
                            <th>@lang('contact.customer')</th>
                            <th>@lang('sale.total_amount')</th>
                            <th>@lang('purchase.paid')</th>
                            <th>@lang('lang_v1.payment_due')</th>
                            <th>@lang('lang_v1.payment_status')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr class="bg-gray font-17">
                            <td colspan="3"><strong>@lang('sale.total'):</strong></td>
                            <td><span id="footer_final_total"></span></td>
                            <td><span id="footer_total_paid"></span></td>
                            <td><span id="footer_total_due"></span></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
@stop

@section('javascript')
<script>
$(document).ready(function () {
    $('#date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            bill_due_report_table.ajax.reload();
        }
    );

    $('#location_id, #customer_id, #created_by').change(function () {
        bill_due_report_table.ajax.reload();
    });

    bill_due_report_table = $('#bill_due_report_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ action("ReportController@getBillDueReport") }}',
            data: function (d) {
                if ($('#date_range').val()) {
                    var date_range = $('#date_range').val().split(' ~ ');
                    d.start_date = date_range[0];
                    d.end_date = date_range[1];
                }
                d.location_id = $('#location_id').val();
                d.customer_id = $('#customer_id').val();
                d.created_by = $('#created_by').val();
            }
        },
        columns: [
            { data: 'transaction_date', name: 'transactions.transaction_date' },
            { data: 'invoice_no', name: 'transactions.invoice_no' },
            { data: 'customer_name', name: 'contacts.name' },
            { data: 'final_total', name: 'transactions.final_total' },
            { data: 'total_paid', name: 'total_paid' },
            { data: 'total_due', name: 'total_due' },
            { data: 'payment_status', name: 'transactions.payment_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        fnDrawCallback: function (oSettings) {
            __currency_convert_recursively($('#bill_due_report_table'));
        },
        footerCallback: function (row, data, start, end, display) {
            var final_total = 0;
            var total_paid = 0;
            var total_due = 0;

            for (var r in data) {
                final_total += parseFloat($(data[r].final_total).data('orig-value'));
                total_paid += parseFloat($(data[r].total_paid).data('orig-value'));
                total_due += parseFloat($(data[r].total_due).data('orig-value'));
            }

            $('#footer_final_total').html(__currency_trans_from_en(final_total, true));
            $('#footer_total_paid').html(__currency_trans_from_en(total_paid, true));
            $('#footer_total_due').html(__currency_trans_from_en(total_due, true));
        },
    });
});
</script>
@endsection
