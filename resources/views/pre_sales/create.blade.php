@extends('layouts.app')
@section('title', 'Add Pre Sales')

@section('content')
<section class="content-header no-print">
    <h1>Add Pre Sales</h1>
</section>

<section class="content no-print">
    @if(count($business_locations) > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::select('select_location_id', $business_locations, $default_location->id ?? null, ['class' => 'form-control input-sm',
                    'id' => 'select_location_id',
                    'required', 'autofocus'], $bl_attributes); !!}
                    <span class="input-group-addon">
                        @show_tooltip(__('tooltip.sale_location'))
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif
    @php
    $custom_labels = json_decode(session('business.custom_labels'), true);
    $common_settings = session()->get('business.common_settings');
    @endphp
    {!! Form::open(['url' => action([\App\Http\Controllers\PreSalesOrderController::class, 'store']), 'method' => 'post', 'id' => 'pre_add_sell_form', 'files' => true ]) !!}
    {!! Form::hidden('location_id', !empty($default_location) ? $default_location->id : null , ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_payment_accounts' => !empty($default_location) ? $default_location->default_payment_accounts : '']); !!}
    <input type="hidden" name="is_direct_sale" value="1">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            @component('components.widget', ['class' => 'box-solid'])

            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('contact_id', __('contact.customer') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::select('contact_id',
                        [], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required']); !!}
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('transaction_date', __('sale.sale_date') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        {!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']); !!}
                    </div>
                </div>
            </div>


            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('status', __('sale.status') . ':*') !!}
                    {!! Form::select('status', $statuses, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
                </div>
            </div>
            @endcomponent
        </div>
    </div>

    @component('components.widget', ['class' => 'box-solid'])
    <div class="col-sm-10 col-sm-offset-1">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-btn">
                    <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
                </div>
                {!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
                'disabled' => is_null($default_location)? true : false,
                'autofocus' => is_null($default_location)? false : true,
                ]); !!}
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action([\App\Http\Controllers\ProductController::class, 'quickAdd'])}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                </span>
            </div>
        </div>
    </div>

    <div class="row col-sm-12 pos_product_div" style="min-height: 0">

        <input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

        <!-- Keeps count of product rows -->
        <input type="hidden" id="product_row_count" value="0">
        @php
        $hide_tax = '';
        if( session()->get('business.enable_inline_tax') == 0){
        $hide_tax = 'hide';
        }
        @endphp
        <div class="table-responsive">
            <table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
                <thead>
                    <tr>
                        <th class="text-center">
                            @lang('sale.product')
                        </th>
                        <th class="text-center">
                            @lang('sale.qty')
                        </th>



                        <th class="@if(!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif">
                            @lang('sale.unit_price')
                        </th>

                        <th class="text-center {{$hide_tax}}">
                            @lang('sale.tax')
                        </th>
                        <th class="text-center {{$hide_tax}}">
                            @lang('sale.price_inc_tax')
                        </th>
                        @if(!empty($common_settings['enable_product_warranty']))
                        <th>@lang('lang_v1.warranty')</th>
                        @endif
                        <th class="text-center">
                            @lang('sale.subtotal')
                        </th>
                        <th class="text-center"><i class="fas fa-times" aria-hidden="true"></i></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="table-responsive">
            <table class="table table-condensed table-bordered table-striped">
                <tr>
                    <td>
                        <div class="pull-right">
                            <b>@lang('sale.item'):</b>
                            <span class="total_quantity">0</span>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <b>@lang('sale.total'): </b>
                            <span class="price_total">0</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endcomponent
    <div class="row">
			{!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']); !!}
			<div class="col-sm-12 text-center">
				<button type="button" id="submit-sell" class="btn btn-primary btn-big">@lang('messages.save')</button>
				<button type="button" id="save-and-print" class="btn btn-success btn-big">@lang('lang_v1.save_and_print')</button>
			</div>
		</div>
    {!! Form::close() !!}
</section>


<!-- quick Customer modal -->
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>


<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@include('sale_pos.partials.configure_search_modal')


@stop



@section('javascript')
<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>

<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#transaction_date').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });
        $('#status').change(function() {
            if ($(this).val() == 'final') {
                $('#payment_rows_div').removeClass('hide');
            } else {
                $('#payment_rows_div').addClass('hide');
            }
        });
    });
</script>

@endsection