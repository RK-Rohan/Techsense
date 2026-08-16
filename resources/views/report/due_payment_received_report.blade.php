@extends('layouts.app')
@section('title', __('lang_v1.due_payment_received_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('lang_v1.due_payment_received_report')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
           @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => '#', 'method' => 'get', 'id' => 'dprr_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('customer_id', __('contact.customer') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('payment_types', __('lang_v1.payment_method').':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            {!! Form::select('payment_types', $payment_types, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('dprr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'dprr_date_filter', 'readonly']); !!}
                    </div>
                </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.payment_method_wise_summary')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dprr_method_summary_table">
                        <thead>
                            <tr id="dprr_method_summary_header"></tr>
                        </thead>
                        <tbody>
                            <tr id="dprr_method_summary_row"></tr>
                        </tbody>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped"
                    id="dprr_table">
                        <thead>
                            <tr>
                                <th>@lang('purchase.ref_no')</th>
                                <th>@lang('lang_v1.paid_on')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('contact.customer')</th>
                                <th>@lang('lang_v1.payment_method')</th>
                                <th>@lang('sale.sale')</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 footer-total text-center">
                                <td colspan="2"><strong>@lang('sale.total'):</strong></td>
                                <td><span class="display_currency" id="dprr_footer_total_amount" data-currency_symbol="true"></span></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script>
$(document).ready(function() {
    function dprr_get_dates() {
        var start = '';
        var end = '';
        if ($('input#dprr_date_filter').val()) {
            start = $('input#dprr_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
            end = $('input#dprr_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
        }
        return { start_date: start, end_date: end };
    }

    function dprr_load_method_summary() {
        var dates = dprr_get_dates();

        $.ajax({
            url: '{{ action("App\\Http\\Controllers\\ReportController@sellPaymentMethodWiseSummary") }}',
            dataType: 'json',
            data: {
                supplier_id: $('select#customer_id').val(),
                location_id: $('select#location_id').val(),
                start_date: dates.start_date,
                end_date: dates.end_date
            },
            success: function(result) {
                var $header = $('#dprr_method_summary_header');
                var $row = $('#dprr_method_summary_row');
                $header.empty();
                $row.empty();

                $.each(result.summary, function(i, item) {
                    $header.append('<th class="text-center">' + item.label + '</th>');
                    $row.append('<td class="text-center display_currency" data-currency_symbol="true">' + item.amount + '</td>');
                });

                $header.append('<th class="text-center"><strong>@lang("sale.total")</strong></th>');
                $row.append('<td class="text-center display_currency" data-currency_symbol="true"><strong>' + result.grand_total + '</strong></td>');

                __currency_convert_recursively($('#dprr_method_summary_table'));
            }
        });
    }

    dprr_table = $('table#dprr_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        ajax: {
            url: '{{ action("App\\Http\\Controllers\\ReportController@duePaymentReceivedReport") }}',
            data: function(d) {
                d.supplier_id = $('select#customer_id').val();
                d.location_id = $('select#location_id').val();
                d.payment_types = $('select#payment_types').val();
                var dates = dprr_get_dates();
                d.start_date = dates.start_date;
                d.end_date = dates.end_date;
            },
        },
        columns: [
            { data: 'payment_ref_no', name: 'payment_ref_no' },
            { data: 'paid_on', name: 'paid_on' },
            { data: 'amount', name: 'transaction_payments.amount' },
            { data: 'customer', orderable: false, searchable: false },
            { data: 'method', name: 'method' },
            { data: 'invoice_no', name: 't.invoice_no' },
        ],
        fnDrawCallback: function(oSettings) {
            var total_amount = sum_table_col($('#dprr_table'), 'paid-amount');
            $('#dprr_footer_total_amount').text(total_amount);
            __currency_convert_recursively($('#dprr_table'));
        },
    });

    dprr_load_method_summary();

    $('#dprr_form #location_id, #dprr_form #customer_id, #dprr_form #payment_types').change(function() {
        dprr_table.ajax.reload();
        dprr_load_method_summary();
    });

    if ($('#dprr_date_filter').length == 1) {
        $('#dprr_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
            $('#dprr_date_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            dprr_table.ajax.reload();
            dprr_load_method_summary();
        });
        $('#dprr_date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $('#dprr_date_filter').val('');
            dprr_table.ajax.reload();
            dprr_load_method_summary();
        });
    }
});
</script>
@endsection
