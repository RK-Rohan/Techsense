@extends('layouts.app')
@section('title', __( 'lang_v1.all_sales'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang( 'sale.sells')
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
        @if(!empty($sources))
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('sell_list_filter_source',  __('lang_v1.sources') . ':') !!}

                    {!! Form::select('sell_list_filter_source', $sources, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
                </div>
            </div>
        @endif
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'lang_v1.all_sales')])
        @can('direct_sell.access')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action([\App\Http\Controllers\SellController::class, 'create'])}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endcan
        @if(auth()->user()->can('direct_sell.view') ||  auth()->user()->can('view_own_sell_only') ||  auth()->user()->can('view_commission_agent_sell'))
        @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
         @endphp
            <table class="table table-bordered table-striped ajax_view" id="sell_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('messages.date')</th>
                        <th>Time</th>
                        <th>@lang('sale.invoice_no')</th>
                        <th>Business Name</th>
                        <th>@lang('sale.customer_name')</th>
                        <th>{{ $custom_labels['sell']['custom_field_2'] ?? 'Contact To' }}</th>
                        <th>{{ $custom_labels['sell']['custom_field_1'] ?? '' }}</th>
                        {{-- <th>@lang('sale.location')</th> --}}
                        <th>@lang('sale.payment_status')</th>
                        <th>{{ $custom_labels['sell']['custom_field_5'] ?? 'Investor Name' }}</th>
                        {{-- <th>@lang('lang_v1.payment_method')</th> --}}
                        <th>@lang('sale.total_amount')</th>
                        <th>@lang('sale.total_paid')</th>
                        <th>@lang('lang_v1.sell_due')</th>
                        <th>{{ $custom_labels['sell']['custom_field_4'] ?? __('lang_v1.custom_field', ['number' => 4]) }}</th>
                        <th>{{ $custom_labels['sell']['custom_field_6'] ?? __('lang_v1.custom_field', ['number' => 6]) }}</th>
                        <th>Tracking Number</th>
                        {{-- <th>@lang('lang_v1.sell_return_due')</th> --}}
                        <th>@lang('lang_v1.shipping_status')</th>
                        <th>Delivery Date</th>
                        <th>Due Countdown</th>
                        <th>@lang('lang_v1.total_items')</th>
                        <th>@lang('lang_v1.types_of_service')</th>
                        <th>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1' )}}</th>
                        <th>{{ $custom_labels['sell']['custom_field_3'] ?? ''}}</th>
                        <th>@lang('lang_v1.added_by')</th>
                        <th>@lang('sale.sell_note')</th>
                        {{-- <th>@lang('sale.staff_note')</th> --}}
                        {{-- <th>@lang('sale.shipping_details')</th> --}}
                        <th>@lang('restaurant.table')</th>
                        <th>@lang('restaurant.service_staff')</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="bg-gray font-17 footer-total text-center">
                        <td colspan="8"><strong>@lang('sale.total'):</strong></td>
                        <td class="footer_payment_status_count"></td>
                        <td></td>
                        <td class="footer_sale_total"></td>
                        <td class="footer_total_paid"></td>
                        <td class="footer_total_remaining"></td>
                        <td colspan="7"></td>
                        <td class="service_type_count"></td>
                        <td colspan="6"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<!-- <section class="invoice print_section" id="receipt_section">
</section> -->

@stop

@section('javascript')
<script type="text/javascript">
//Remembers the All-sales filters for the browser tab, so returning from an
//edit/view page keeps whatever was selected. Cleared when the tab closes.
var sell_filter_storage_key = 'sell_list_filters';
var sell_filter_fields = [
    'sell_list_filter_location_id',
    'sell_list_filter_customer_id',
    'sell_list_filter_payment_status',
    'created_by',
    'sales_cmsn_agnt',
    'service_staffs',
    'shipping_status',
    'sell_list_filter_source',
];

function __store_sell_filters() {
    try {
        var state = {};
        $.each(sell_filter_fields, function(i, id) {
            var $el = $('#' + id);
            if ($el.length) {
                state[id] = $el.val();
            }
        });

        state.date_range = $('#sell_list_filter_date_range').val() || '';
        state.only_subscriptions = $('#only_subscriptions').is(':checked') ? 1 : 0;

        sessionStorage.setItem(sell_filter_storage_key, JSON.stringify(state));
    } catch (e) {
        //Storage unavailable (private mode/quota) - filters simply won't persist.
    }
}

function __restore_sell_filters() {
    var state = null;
    try {
        state = JSON.parse(sessionStorage.getItem(sell_filter_storage_key));
    } catch (e) {
        return;
    }

    if (!state) {
        return;
    }

    $.each(sell_filter_fields, function(i, id) {
        var $el = $('#' + id);
        if ($el.length && typeof state[id] !== 'undefined' && state[id] !== null) {
            //Set silently: the table has not been created yet at restore time.
            $el.val(state[id]).trigger('change.select2');
        }
    });

    //The ajax payload reads the picker's dates, so sync the widget too - not
    //just the visible text - or a restored range would be ignored.
    if (state.date_range) {
        var $range = $('#sell_list_filter_date_range');
        $range.val(state.date_range);

        var picker = $range.data('daterangepicker');
        var parts = state.date_range.split(' ~ ');
        if (picker && parts.length === 2) {
            picker.setStartDate(moment(parts[0], moment_date_format));
            picker.setEndDate(moment(parts[1], moment_date_format));
            $range.val(state.date_range);
        }
    }

    if (state.only_subscriptions) {
        $('#only_subscriptions').iCheck('check');
    }
}

$(document).ready( function(){
    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            __store_sell_filters();
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        __store_sell_filters();
        sell_table.ajax.reload();
    });

    //Restore before the table is built so the first request already filters.
    __restore_sell_filters();

    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        "ajax": {
            "url": "/sells",
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                d.is_direct_sale = 1;

                d.location_id = $('#sell_list_filter_location_id').val();
                d.customer_id = $('#sell_list_filter_customer_id').val();
                d.payment_status = $('#sell_list_filter_payment_status').val();
                d.created_by = $('#created_by').val();
                d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                d.service_staffs = $('#service_staffs').val();

                if($('#shipping_status').length) {
                    d.shipping_status = $('#shipping_status').val();
                }

                if($('#sell_list_filter_source').length) {
                    d.source = $('#sell_list_filter_source').val();
                }

                if($('#only_subscriptions').is(':checked')) {
                    d.only_subscriptions = 1;
                }

                d = __datatable_ajax_callback(d);
            }
        },
        scrollY:        "75vh",
        scrollX:        true,
        scrollCollapse: true,
        columns: [
            { data: 'action', name: 'action', orderable: false, "searchable": false},
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'transaction_time', name: 'transaction_date', searchable: false },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'company_name', name: 'contacts.supplier_business_name'},
            { data: 'contact_name', name: 'contacts.name'},
            { data: 'custom_field_2', name: 'transactions.custom_field_2'},
            { data: 'custom_field_1', name: 'transactions.custom_field_1', visible: {{ empty($custom_labels['sell']['custom_field_1']) ? 'false' : 'true' }} },
            // { data: 'business_location', name: 'bl.name'},
            { data: 'payment_status', name: 'payment_status'},
            { data: 'custom_field_5', name: 'transactions.custom_field_5', visible: {{ empty($custom_labels['sell']['custom_field_5']) ? 'true' : 'true' }} },
            // { data: 'payment_methods', orderable: false, "searchable": false},
            { data: 'final_total', name: 'final_total'},
            { data: 'total_paid', name: 'total_paid', "searchable": false},
            { data: 'total_remaining', name: 'total_remaining'},
            { data: 'custom_field_4', name: 'transactions.custom_field_4', visible: {{ empty($custom_labels['sell']['custom_field_4']) ? 'false' : 'true' }} },
            { data: 'custom_field_6', name: 'transactions.custom_field_6', visible: {{ empty($custom_labels['sell']['custom_field_6']) ? 'false' : 'true' }} },
            { data: 'shipping_custom_field_1', name: 'transactions.shipping_custom_field_1', "searchable": true},
            // { data: 'return_due', orderable: false, "searchable": false},
            { data: 'shipping_status', name: 'shipping_status'},
            { data: 'delivery_date', name: 'delivery_date'},
            { 
                data: 'due_countdown', 
                name: 'due_countdown',
                searchable: false,
                render: function(data, type, row) {
                    // Parse delivery date
                    if (!row.delivery_date) {
                        return '0 days';
                    }
                    var deliveryDate = moment(row.delivery_date, 'DD-MM-YYYY hh:mm A');
                    if (!deliveryDate.isValid()) {
                        return '0 days';
                    }

                    // If fully paid, freeze countdown at days between delivery date and payment received date
                    var statusRaw = row.payment_status_value || row.payment_status;
                    if (statusRaw && ('' + statusRaw).toLowerCase() === 'paid') {
                        if (!row.payment_received_date) {
                            return '0 days';
                        }
                        // Parse payment received date (same format as delivery_date: DD-MM-YYYY hh:mm A)
                        var paidOn = moment(row.payment_received_date, 'DD-MM-YYYY hh:mm A');
                        if (!paidOn.isValid()) {
                            return '0 days';
                        }
                        // Calculate days difference: payment date - delivery date
                        var deliveryMidnight = deliveryDate.clone().startOf('day');
                        var paidMidnight = paidOn.clone().startOf('day');
                        var diffPaid = paidMidnight.diff(deliveryMidnight, 'days');
                        if (diffPaid < 0) diffPaid = 0;
                        return diffPaid + ' days';
                    }

                    // If unpaid/partial, show days since delivery up to today; no future countdown
                    var today = moment();
                    if (deliveryDate.isAfter(today, 'day')) {
                        return '0 days';
                    }
                    var diff = today.startOf('day').diff(deliveryDate.startOf('day'), 'days');
                    return diff + ' days';
                }
            },
            { data: 'total_items', name: 'total_items', "searchable": false},
            { data: 'types_of_service_name', name: 'tos.name', @if(empty($is_types_service_enabled)) visible: false @endif},
            { data: 'service_custom_field_1', name: 'service_custom_field_1', @if(empty($is_types_service_enabled)) visible: false @endif},
            { data: 'custom_field_3', name: 'transactions.custom_field_3', visible: {{ empty($custom_labels['sell']['custom_field_3']) ? 'false' : 'true' }} },
            { data: 'added_by', name: 'u.first_name'},
            { data: 'additional_notes', name: 'additional_notes'},
            // { data: 'staff_note', name: 'staff_note'},
            // { data: 'shipping_details', name: 'shipping_details'},
            { data: 'table_name', name: 'tables.name', @if(empty($is_tables_enabled)) visible: false @endif },
            { data: 'waiter', name: 'ss.first_name', @if(empty($is_service_staff_enabled)) visible: false @endif },
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#sell_table'));
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var footer_sale_total = 0;
            var footer_total_paid = 0;
            var footer_total_remaining = 0;
            var footer_total_sell_return_due = 0;
            for (var r in data){
                footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(data[r].final_total).data('orig-value')) : 0;
                footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(data[r].total_paid).data('orig-value')) : 0;
                footer_total_remaining += $(data[r].total_remaining).data('orig-value') ? parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                footer_total_sell_return_due += $(data[r].return_due).find('.sell_return_due').data('orig-value') ? parseFloat($(data[r].return_due).find('.sell_return_due').data('orig-value')) : 0;
            }

            $('.footer_total_sell_return_due').html(__currency_trans_from_en(footer_total_sell_return_due));
            $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
            $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
            $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));

            $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
            $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
            $('.payment_method_count').html(__count_status(data, 'payment_methods'));
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(8)').attr('class', 'clickable_td');
        }
    });

    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status, #sell_list_filter_source',  function() {
        __store_sell_filters();
        sell_table.ajax.reload();
    });

    $('#only_subscriptions').on('ifChanged', function(event){
        __store_sell_filters();
        sell_table.ajax.reload();
    });
});
</script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
