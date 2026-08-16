@extends('layouts.app')
@section('title', __('lang_v1.sell_payment_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('lang_v1.sell_payment_report')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
           @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => '#', 'method' => 'get', 'id' => 'sell_payment_report_form' ]) !!}
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
                        {!! Form::label('customer_group_filter', __('lang_v1.customer_group').':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-users"></i>
                            </span>
                            {!! Form::select('customer_group_filter', $customer_groups, null, ['class' => 'form-control select2']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">

                        {!! Form::label('spr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'spr_date_filter', 'readonly']); !!}
                    </div>
                </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.payment_method') . ' ' . __('sale.total')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="spr_method_summary_table">
                        <thead>
                            <tr id="spr_method_summary_header"></tr>
                        </thead>
                        <tbody>
                            <tr id="spr_method_summary_row"></tr>
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
                    id="sell_payment_report_table">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>@lang('purchase.ref_no')</th>
                                <th>@lang('lang_v1.paid_on')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('contact.customer')</th>
                                <th>@lang('lang_v1.customer_group')</th>
                                <th>@lang('lang_v1.payment_method')</th>
                                <th>@lang('sale.sale')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 footer-total text-center">
                                <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                                <td><span class="display_currency" id="footer_total_amount" data-currency_symbol ="true"></span></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    <script>
    $(document).ready(function() {
        function spr_load_method_summary() {
            var start = '';
            var end = '';
            if ($('input#spr_date_filter').val()) {
                start = $('input#spr_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                end = $('input#spr_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }

            $.ajax({
                url: '{{ action("App\\Http\\Controllers\\ReportController@sellPaymentMethodWiseSummary") }}',
                dataType: 'json',
                data: {
                    supplier_id: $('select#customer_id').val(),
                    location_id: $('select#location_id').val(),
                    customer_group_id: $('select#customer_group_filter').val(),
                    start_date: start,
                    end_date: end
                },
                success: function(result) {
                    var $header = $('#spr_method_summary_header');
                    var $row = $('#spr_method_summary_row');
                    $header.empty();
                    $row.empty();

                    $.each(result.summary, function(i, item) {
                        $header.append('<th class="text-center">' + item.label + '</th>');
                        $row.append('<td class="text-center display_currency" data-currency_symbol="true">' + item.amount + '</td>');
                    });

                    $header.append('<th class="text-center"><strong>@lang("sale.total")</strong></th>');
                    $row.append('<td class="text-center display_currency" data-currency_symbol="true"><strong>' + result.grand_total + '</strong></td>');

                    __currency_convert_recursively($('#spr_method_summary_table'));
                }
            });
        }

        spr_load_method_summary();

        $('#sell_payment_report_form #location_id, #sell_payment_report_form #customer_id, #sell_payment_report_form #customer_group_filter').change(function() {
            spr_load_method_summary();
        });

        if ($('#spr_date_filter').length == 1) {
            $('#spr_date_filter').on('apply.daterangepicker cancel.daterangepicker', function() {
                spr_load_method_summary();
            });
        }
    });
    </script>
@endsection