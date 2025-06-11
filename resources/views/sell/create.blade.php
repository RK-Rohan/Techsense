@extends('layouts.app')

@php
if (!empty($status) && $status == 'quotation') {
$title = __('lang_v1.add_quotation');
} else if (!empty($status) && $status == 'draft') {
$title = __('lang_v1.add_draft');
} else {
$title = __('sale.add_sale');
}

if($sale_type == 'sales_order') {
$title = __('lang_v1.sales_order');
}
@endphp

@section('title', $title)

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>{{$title}}</h1>
</section>
<!-- Main content -->
<section class="content no-print">
	<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
	@if(!empty($pos_settings['allow_overselling']))
	<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
	<input type="hidden" id="reward_point_enabled">
	@endif
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
	<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
	{!! Form::open(['url' => action([\App\Http\Controllers\SellPosController::class, 'store']), 'method' => 'post', 'id' => 'add_sell_form', 'files' => true ]) !!}
	@if(!empty($sale_type))
	<input type="hidden" id="sale_type" name="type" value="{{$sale_type}}">
	@endif
	<div class="row">
		<div class="col-md-12 col-sm-12">
			@component('components.widget', ['class' => 'box-solid'])
			{!! Form::hidden('location_id', !empty($default_location) ? $default_location->id : null , ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_payment_accounts' => !empty($default_location) ? $default_location->default_payment_accounts : '']); !!}

			@if(!empty($price_groups))
			@if(count($price_groups) > 1)
			<div class="col-sm-4">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-money-bill-alt"></i>
						</span>
						@php
						reset($price_groups);
						$selected_price_group = !empty($default_price_group_id) && array_key_exists($default_price_group_id, $price_groups) ? $default_price_group_id : null;
						@endphp
						{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
						{!! Form::select('price_group', $price_groups, $selected_price_group, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
						<span class="input-group-addon">
							@show_tooltip(__('lang_v1.price_group_help_text'))
						</span>
					</div>
				</div>
			</div>

			@else
			@php
			reset($price_groups);
			@endphp
			{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
			@endif
			@endif

			{!! Form::hidden('default_price_group', null, ['id' => 'default_price_group']) !!}

			@if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
			<div class="col-md-4 col-sm-6">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
						</span>
						{!! Form::select('types_of_service_id', $types_of_service, null, ['class' => 'form-control', 'id' => 'types_of_service_id', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.select_types_of_service')]); !!}

						{!! Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']) !!}

						<span class="input-group-addon">
							@show_tooltip(__('lang_v1.types_of_service_help'))
						</span>
					</div>
					<small>
						<p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p>
					</small>
				</div>
			</div>
			<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
			@endif


			<div class="clearfix"></div>
			<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
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
					<small class="text-danger hide contact_due_text"><strong>@lang('account.customer_due'):</strong> <span></span></small>
				</div>
				<small class="customer_address_on_select">
					<strong>
						@lang('lang_v1.billing_address'):
					</strong>
					<div id="billing_address_div">
						{!! $walk_in_customer['contact_address'] ?? '' !!}
					</div>
					<br>
					<strong>
						@lang('lang_v1.shipping_address'):
					</strong>
					<div id="shipping_address_div">
						{{$walk_in_customer['supplier_business_name'] ?? ''}},<br>
						{{$walk_in_customer['name'] ?? ''}},<br>
						{{$walk_in_customer['shipping_address'] ?? ''}}
					</div>
				</small>
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

			@if(!empty($status))
			<input type="hidden" name="status" id="status" value="{{$status}}">

			@if(in_array($status, ['draft', 'quotation']))
			<input type="hidden" id="disable_qty_alert">
			@endif
			@else
			<div class="col-sm-4">
				<div class="form-group">
					{!! Form::label('status', __('sale.status') . ':*') !!}
					{!! Form::select('status', $statuses, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
				</div>
			</div>
			@endif

			@if($status == 'sales_order')
			@can('edit_invoice_number')
			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('invoice_no', $sale_type == 'sales_order' ? __('restaurant.order_no') : __('sale.invoice_no') . ':') !!}
					{!! Form::text('invoice_no', null, ['class' => 'form-control', 'placeholder' => $sale_type == 'sales_order' ? __('restaurant.order_no') : __('sale.invoice_no')]); !!}
					<p class="help-block">@lang('lang_v1.keep_blank_to_autogenerate')</p>
				</div>
			</div>
			@endcan
			@endif

			@php
			$custom_field_1_label = !empty($custom_labels['sell']['custom_field_1']) ? $custom_labels['sell']['custom_field_1'] : '';

			$is_custom_field_1_required = !empty($custom_labels['sell']['is_custom_field_1_required']) && $custom_labels['sell']['is_custom_field_1_required'] == 1 ? true : false;

			$custom_field_2_label = !empty($custom_labels['sell']['custom_field_2']) ? $custom_labels['sell']['custom_field_2'] : '';

			$is_custom_field_2_required = !empty($custom_labels['sell']['is_custom_field_2_required']) && $custom_labels['sell']['is_custom_field_2_required'] == 1 ? true : false;

			$custom_field_3_label = !empty($custom_labels['sell']['custom_field_3']) ? $custom_labels['sell']['custom_field_3'] : '';

			$is_custom_field_3_required = !empty($custom_labels['sell']['is_custom_field_3_required']) && $custom_labels['sell']['is_custom_field_3_required'] == 1 ? true : false;

			$custom_field_4_label = !empty($custom_labels['sell']['custom_field_4']) ? $custom_labels['sell']['custom_field_4'] : '';

			$is_custom_field_4_required = !empty($custom_labels['sell']['is_custom_field_4_required']) && $custom_labels['sell']['is_custom_field_4_required'] == 1 ? true : false;
			@endphp


			@if(!empty($custom_field_1_label))
			@php
			$label_1 = $custom_field_1_label . ':';
			if($is_custom_field_1_required) {
			$label_1 .= '*';
			}
			@endphp

			<div class="col-md-4" id="custom_field_div_1">
				<div class="form-group">
					{!! Form::label('custom_field_1', $label_1 ) !!}
					{!! Form::text('custom_field_1', null, ['class' => 'form-control','placeholder' => $custom_field_1_label, 'required' => $is_custom_field_1_required]); !!}
				</div>
			</div>
			@endif


			@if(!empty($custom_field_2_label))
			@php
			$label_2 = $custom_field_2_label . ':';
			if($is_custom_field_2_required) {
			$label_2 .= '*';
			}
			@endphp

			<div class="col-md-4" id="custom_field_div_2">
				<div class="form-group">
					{!! Form::label('custom_field_2', $label_2 ) !!}
					{!! Form::text('custom_field_2', null, ['class' => 'form-control','placeholder' => $custom_field_2_label, 'required' => $is_custom_field_2_required]); !!}
				</div>
			</div>
			@endif
			@if(!empty($custom_field_3_label))
			@php
			$label_3 = $custom_field_3_label . ':';
			if($is_custom_field_3_required) {
			$label_3 .= '*';
			}
			@endphp

			<div class="col-md-4" id="custom_field_div_3">
				<div class="form-group">
					{!! Form::label('custom_field_3', $label_3 ) !!}
					{!! Form::text('custom_field_3', null, ['class' => 'form-control','placeholder' => $custom_field_3_label, 'required' => $is_custom_field_3_required]); !!}
				</div>
			</div>
			@endif
			@if(!empty($custom_field_4_label))
			@php
			$label_4 = $custom_field_4_label . ':';
			if($is_custom_field_4_required) {
			$label_4 .= '*';
			}
			@endphp

			<div class="col-md-4" id="custom_field_div_4">
				<div class="form-group">
					{!! Form::label('custom_field_4', $label_4 ) !!}
					{!! Form::text('custom_field_4', null, ['class' => 'form-control','placeholder' => $custom_field_4_label, 'required' => $is_custom_field_4_required]); !!}
				</div>
			</div>
			@endif

			@if($status == 'sales_order')
			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('upload_document', __('purchase.attach_document') . ':') !!}
					{!! Form::file('sell_document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
					<p class="help-block">
						@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
						@includeIf('components.document_help_text')
					</p>
				</div>
			</div>
			@endif
			<div class="clearfix"></div>

			@if($status == 'sales_order')
			@if((!empty($pos_settings['enable_sales_order']) && $sale_type != 'sales_order') || $is_order_request_enabled)
			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('sales_order_ids', __('lang_v1.sales_order').':') !!}
					{!! Form::select('sales_order_ids[]', [], null, ['class' => 'form-control select2', 'multiple', 'id' => 'sales_order_ids']); !!}
				</div>
			</div>
			<div class="clearfix"></div>
			@endif
			@endif


			<div class="payment_by_div col-sm-4 hide">
				<div class="form-group">
					{!! Form::label('supplier_payment_by', 'Supplier Payment By' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						<select name="supplier_payment_by" id="supplier_payment_by" class="form-control">
							<option value="">Please Select</option>
							<option value="by_client">By Client</option>
							<option value="by_company">By Company</option>
						</select>
					</div>
				</div>
			</div>

			<div class="supplier_div col-sm-4 hide">
				<div class="form-group">
					{!! Form::label('supplier_id', 'Supplier Name' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-user"></i>
						</span>
						{!! Form::select('supplier_id', [], null, ['class' => 'form-control', 'id' => 'supplier_id', 'placeholder' => 'Enter Supplier name', 'required']); !!}
						<span class="input-group-btn">
							<button type="button" class="btn btn-default bg-white btn-flat add_new_supplier" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
						</span>
					</div>
				</div>

			</div>

			<div class="supplier_amount_div col-sm-4 hide ">
				<div class="form-group">
					{!! Form::label('supplier_amount', 'Supplier Amount' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-money-bill-alt"></i>
						</span>
						<input type="number" name="supplier_amount" id="supplier_amount" class="form-control" value="0.00">
					</div>
				</div>
			</div>

			<div class="agent_div col-md-4 hide">
				<div class="form-group">
					{!! Form::label('agent_id', 'Agent Name' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-user"></i>
						</span>
						{!! Form::select('agent_id', $agents->pluck('name', 'id'), null, [
						'class' => 'form-control',
						'placeholder' => __('messages.please_select'),
						'required',
						'id' => 'agent_id'
						]) !!}
						<span class="input-group-btn">
							<button type="button" class="btn btn-default bg-white btn-flat add_new_agent" data-toggle="modal" data-target="#addAgentModal">
								<i class="fa fa-plus-circle text-primary fa-lg"></i>
							</button>
						</span>
					</div>
				</div>
			</div>


			<div class="tracking_div col-md-4 hide">
				<div class="form-group">
					{!! Form::label('tracking_no', 'Tracking No' . ':') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						<input type="text" name="tracking_no" id="tracking_no" class="form-control">
					</div>
				</div>
			</div>

			<div class="carton_no_div col-md-4 hide">
				<div class="form-group">
					{!! Form::label('no_of_carton', 'Number of Carton' . ':') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						<input type="number" name="no_of_carton" id="no_of_carton" class="form-control">
					</div>
				</div>
			</div>

			@endcomponent




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

								<th class="text-center delivery_time">
									Delivery Time
								</th>

								@if(!empty($pos_settings['inline_service_staff']))
								<th class="text-center">
									@lang('restaurant.service_staff')
								</th>
								@endif
								<th class="@if(!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif">
									@lang('sale.unit_price')
								</th>
								<!-- <th class="@if(!auth()->user()->can('edit_product_discount_from_sale_screen')) hide @endif">
									@lang('receipt.discount')
								</th> -->
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




			@component('components.widget', ['class' => 'box-solid pre_sales_box'])
			<div class="clearfix"></div>

			<div class="col-md-4 @if($sale_type == 'sales_order') hide @endif">
				<div class="form-group">
					{!! Form::label('cnf_rate', 'CNF Rate(KG)' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						<input type="number" name="cnf_rate" id="cnf_rate" class="form-control" value="0.00">
					</div>
				</div>
			</div>
			<div class="col-md-4 @if($sale_type == 'sales_order') hide @endif">
				<div class="form-group">
					{!! Form::label('cnf_cost', 'CNF Cost' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-money-bill-alt"></i>
						</span>
						<input type="number" name="cnf_cost" id="cnf_cost" class="form-control" value="0.00">
					</div>
				</div>
			</div>
			<div class="col-md-4 @if($sale_type == 'sales_order') hide @endif">
				<div class="form-group">
					{!! Form::label('sale_profit', 'Sell Profit' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-money-bill-alt"></i>
						</span>
						<input type="number" name="sale_profit" id="sale_profit" class="form-control" value="0.00">
					</div>
				</div>
			</div>

			@endcomponent

			@component('components.widget', ['class' => 'box-solid delivery_date_box'])
			<div class="col-sm-4">
				<div class="form-group">
					{!! Form::label('delivery_date', 'Delivery Date' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</span>
						{!! Form::text('delivery_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']); !!}
					</div>
				</div>
			</div>
			<div class="col-sm-4 received_date_div">
				<div class="form-group">
					{!! Form::label('received_date', 'Received Date' . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</span>
						{!! Form::text('received_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']); !!}
					</div>
				</div>
			</div>
			@endcomponent


			@component('components.widget', ['class' => 'box-solid'])
			<div class="col-md-4  @if($sale_type == 'sales_order') hide @endif">
				<div class="form-group">
					{!! Form::label('discount_type', __('sale.discount_type') . ':*' ) !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!! Form::select('discount_type', ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], 'percentage' , ['class' => 'form-control','placeholder' => __('messages.please_select'), 'required', 'data-default' => 'percentage']); !!}
					</div>
				</div>
			</div>
			@php
			$max_discount = !is_null(auth()->user()->max_sales_discount_percent) ? auth()->user()->max_sales_discount_percent : '';

			//if sale discount is more than user max discount change it to max discount
			$sales_discount = $business_details->default_sales_discount;
			if($max_discount != '' && $sales_discount > $max_discount) $sales_discount = $max_discount;

			$default_sales_tax = $business_details->default_sales_tax;

			if($sale_type == 'sales_order') {
			$sales_discount = 0;
			$default_sales_tax = null;
			}
			@endphp
			<div class="col-md-4 @if($sale_type == 'sales_order') hide @endif">
				<div class="form-group">
					{!! Form::label('discount_amount', __('sale.discount_amount') . ':*' ) !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!! Form::text('discount_amount', @num_format($sales_discount), ['class' => 'form-control input_number', 'data-default' => $sales_discount, 'data-max-discount' => $max_discount, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount != '' ? @num_format($max_discount) : '']) ]); !!}
					</div>
				</div>
			</div>
			<div class="col-md-4 @if($sale_type == 'sales_order') hide @endif"><br>
				<b>@lang( 'sale.discount_amount' ):</b>(-)
				<span class="display_currency" id="total_discount">0</span>
			</div>
			<div class="clearfix"></div>

			<div class="clearfix"></div>
			<div class="col-md-4  @if($sale_type == 'sales_order') hide @endif">
				<div class="form-group">
					{!! Form::label('tax_rate_id', __('sale.order_tax') . ':*' ) !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!! Form::select('tax_rate_id', $taxes['tax_rates'], $default_sales_tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control', 'data-default'=> $default_sales_tax], $taxes['attributes']); !!}

						<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" value="@if(empty($edit)) {{@num_format($business_details->tax_calculation_amount)}} @else {{@num_format($transaction->tax?->amount)}} @endif" data-default="{{$business_details->tax_calculation_amount}}">
					</div>
				</div>
			</div>
			<div class="col-md-4 col-md-offset-4  @if($sale_type == 'sales_order') hide @endif">
				<b>@lang( 'sale.order_tax' ):</b>(+)
				<span class="display_currency" id="order_tax">0</span>
			</div>

			<div class="col-md-12">
				<div class="form-group">
					{!! Form::label('sell_note',__('sale.sell_note')) !!}
					{!! Form::textarea('sale_note', null, ['class' => 'form-control', 'rows' => 3]); !!}
				</div>
			</div>
			<input type="hidden" name="is_direct_sale" value="1">
			@endcomponent
			@component('components.widget', ['class' => 'box-solid'])
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_details', __('sale.shipping_details')) !!}
					{!! Form::textarea('shipping_details',null, ['class' => 'form-control','placeholder' => __('sale.shipping_details') ,'rows' => '3', 'cols'=>'30']); !!}
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_address', __('lang_v1.shipping_address')) !!}
					{!! Form::textarea('shipping_address',null, ['class' => 'form-control','placeholder' => __('lang_v1.shipping_address') ,'rows' => '3', 'cols'=>'30']); !!}
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					{!!Form::label('shipping_charges', __('sale.shipping_charges'))!!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!!Form::text('shipping_charges',@num_format(0.00),['class'=>'form-control input_number','placeholder'=> __('sale.shipping_charges')]);!!}
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_status', __('lang_v1.shipping_status')) !!}
					{!! Form::select('shipping_status',$shipping_statuses, null, ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('delivered_to', __('lang_v1.delivered_to') . ':' ) !!}
					{!! Form::text('delivered_to', null, ['class' => 'form-control','placeholder' => __('lang_v1.delivered_to')]); !!}
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('delivery_person', __('lang_v1.delivery_person') . ':' ) !!}
					{!! Form::select('delivery_person', $users, null, ['class' => 'form-control select2','placeholder' => __('messages.please_select')]); !!}
				</div>
			</div>

			
			@php
			$shipping_custom_label_1 = !empty($custom_labels['shipping']['custom_field_1']) ? $custom_labels['shipping']['custom_field_1'] : '';

			$is_shipping_custom_field_1_required = !empty($custom_labels['shipping']['is_custom_field_1_required']) && $custom_labels['shipping']['is_custom_field_1_required'] == 1 ? true : false;

			$shipping_custom_label_2 = !empty($custom_labels['shipping']['custom_field_2']) ? $custom_labels['shipping']['custom_field_2'] : '';

			$is_shipping_custom_field_2_required = !empty($custom_labels['shipping']['is_custom_field_2_required']) && $custom_labels['shipping']['is_custom_field_2_required'] == 1 ? true : false;

			$shipping_custom_label_3 = !empty($custom_labels['shipping']['custom_field_3']) ? $custom_labels['shipping']['custom_field_3'] : '';

			$is_shipping_custom_field_3_required = !empty($custom_labels['shipping']['is_custom_field_3_required']) && $custom_labels['shipping']['is_custom_field_3_required'] == 1 ? true : false;

			$shipping_custom_label_4 = !empty($custom_labels['shipping']['custom_field_4']) ? $custom_labels['shipping']['custom_field_4'] : '';

			$is_shipping_custom_field_4_required = !empty($custom_labels['shipping']['is_custom_field_4_required']) && $custom_labels['shipping']['is_custom_field_4_required'] == 1 ? true : false;

			$shipping_custom_label_5 = !empty($custom_labels['shipping']['custom_field_5']) ? $custom_labels['shipping']['custom_field_5'] : '';

			$is_shipping_custom_field_5_required = !empty($custom_labels['shipping']['is_custom_field_5_required']) && $custom_labels['shipping']['is_custom_field_5_required'] == 1 ? true : false;
			@endphp

			@if(!empty($shipping_custom_label_1))
			@php
			$label_1 = $shipping_custom_label_1 . ':';
			if($is_shipping_custom_field_1_required) {
			$label_1 .= '*';
			}
			@endphp

			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_custom_field_1', $label_1 ) !!}
					{!! Form::text('shipping_custom_field_1', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_1']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_1'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_1, 'required' => $is_shipping_custom_field_1_required]); !!}
				</div>
			</div>
			@endif
			@if(!empty($shipping_custom_label_2))
			@php
			$label_2 = $shipping_custom_label_2 . ':';
			if($is_shipping_custom_field_2_required) {
			$label_2 .= '*';
			}
			@endphp

			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_custom_field_2', $label_2 ) !!}
					{!! Form::text('shipping_custom_field_2', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_2']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_2'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_2, 'required' => $is_shipping_custom_field_2_required]); !!}
				</div>
			</div>
			@endif
			@if(!empty($shipping_custom_label_3))
			@php
			$label_3 = $shipping_custom_label_3 . ':';
			if($is_shipping_custom_field_3_required) {
			$label_3 .= '*';
			}
			@endphp

			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_custom_field_3', $label_3 ) !!}
					{!! Form::text('shipping_custom_field_3', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_3']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_3'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_3, 'required' => $is_shipping_custom_field_3_required]); !!}
				</div>
			</div>
			@endif
			@if(!empty($shipping_custom_label_4))
			@php
			$label_4 = $shipping_custom_label_4 . ':';
			if($is_shipping_custom_field_4_required) {
			$label_4 .= '*';
			}
			@endphp

			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_custom_field_4', $label_4 ) !!}
					{!! Form::text('shipping_custom_field_4', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_4']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_4'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_4, 'required' => $is_shipping_custom_field_4_required]); !!}
				</div>
			</div>
			@endif
			@if(!empty($shipping_custom_label_5))
			@php
			$label_5 = $shipping_custom_label_5 . ':';
			if($is_shipping_custom_field_5_required) {
			$label_5 .= '*';
			}
			@endphp

			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_custom_field_5', $label_5 ) !!}
					{!! Form::text('shipping_custom_field_5', !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_5']) ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_5'] : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_5, 'required' => $is_shipping_custom_field_5_required]); !!}
				</div>
			</div>
			@endif
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('shipping_documents', __('lang_v1.shipping_documents') . ':') !!}
					{!! Form::file('shipping_documents[]', ['id' => 'shipping_documents', 'multiple', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
					<p class="help-block">
						@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
						@includeIf('components.document_help_text')
					</p>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12 text-center">
				<button type="button" class="btn btn-primary btn-sm" id="toggle_additional_expense"> <i class="fas fa-plus"></i> @lang('lang_v1.add_additional_expenses') <i class="fas fa-chevron-down"></i></button>
			</div>

			<div class="col-md-8 col-md-offset-4" id="additional_expenses_div" style="display: none;">
				<table class="table table-condensed">
					<thead>
						<tr>
							<th>@lang('lang_v1.additional_expense_name')</th>
							<th>@lang('sale.amount')</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								{!! Form::text('additional_expense_key_1', null, ['class' => 'form-control', 'id' => 'additional_expense_key_1']); !!}
							</td>
							<td>
								{!! Form::text('additional_expense_value_1', 0, ['class' => 'form-control input_number', 'id' => 'additional_expense_value_1']); !!}
							</td>
						</tr>
						<tr>
							<td>
								{!! Form::text('additional_expense_key_2', null, ['class' => 'form-control', 'id' => 'additional_expense_key_2']); !!}
							</td>
							<td>
								{!! Form::text('additional_expense_value_2', 0, ['class' => 'form-control input_number', 'id' => 'additional_expense_value_2']); !!}
							</td>
						</tr>
						<tr>
							<td>
								{!! Form::text('additional_expense_key_3', null, ['class' => 'form-control', 'id' => 'additional_expense_key_3']); !!}
							</td>
							<td>
								{!! Form::text('additional_expense_value_3', 0, ['class' => 'form-control input_number', 'id' => 'additional_expense_value_3']); !!}
							</td>
						</tr>
						<tr>
							<td>
								{!! Form::text('additional_expense_key_4', null, ['class' => 'form-control', 'id' => 'additional_expense_key_4']); !!}
							</td>
							<td>
								{!! Form::text('additional_expense_value_4', 0, ['class' => 'form-control input_number', 'id' => 'additional_expense_value_4']); !!}
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="col-md-4 col-md-offset-8">
				@if(!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0)
				<small id="round_off"><br>(@lang('lang_v1.round_off'): <span id="round_off_text">0</span>)</small>
				<br />
				<input type="hidden" name="round_off_amount" id="round_off_amount" value=0>
				@endif
				<div><b>@lang('sale.total_payable'): </b>
					<input type="hidden" name="final_total" id="final_total_input">
					<span id="total_payable">0</span>
				</div>
			</div>
			@endcomponent


			@component('components.widget', ['class' => 'box-solid term_condition_div'])
			<div class="row col-md-8">
				<table class="table table-bordered " id="term_condition_table">
					<thead class="btn-default table-light">
						<tr>
							<th>Terms and Conditions</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><button type="button" name="add" id="add" class="btn btn-success"><i class='fa fa-plus'></i>Add Line</button></td>
						</tr>
					</tbody>

				</table>
			</div>
			@endcomponent

		</div>
	</div>
	@if(!empty($common_settings['is_enabled_export']) && $sale_type != 'sales_order')
	@component('components.widget', ['class' => 'box-solid', 'title' => __('lang_v1.export')])
	<div class="col-md-12 mb-12">
		<div class="form-check">
			<input type="checkbox" name="is_export" class="form-check-input" id="is_export" @if(!empty($walk_in_customer['is_export'])) checked @endif>
			<label class="form-check-label" for="is_export">@lang('lang_v1.is_export')</label>
		</div>
	</div>
	@php
	$i = 1;
	@endphp
	@for($i; $i <= 6 ; $i++) <div class="col-md-4 export_div" @if(empty($walk_in_customer['is_export'])) style="display: none;" @endif>
		<div class="form-group">
			{!! Form::label('export_custom_field_'.$i, __('lang_v1.export_custom_field'.$i).':') !!}
			{!! Form::text('export_custom_fields_info['.'export_custom_field_'.$i.']', !empty($walk_in_customer['export_custom_field_'.$i]) ? $walk_in_customer['export_custom_field_'.$i] : null, ['class' => 'form-control','placeholder' => __('lang_v1.export_custom_field'.$i), 'id' => 'export_custom_field_'.$i]); !!}
		</div>
		</div>
		@endfor
		@endcomponent
		@endif
		@php
		$is_enabled_download_pdf = config('constants.enable_download_pdf');
		$payment_body_id = 'payment_rows_div';
		if ($is_enabled_download_pdf) {
		$payment_body_id = '';
		}
		@endphp
		@if((empty($status) || (!in_array($status, ['quotation', 'draft'])) || $is_enabled_download_pdf) && $sale_type != 'sales_order')
		@can('sell.payments')
		@component('components.widget', ['class' => 'box-solid', 'id' => $payment_body_id, 'title' => __('purchase.add_payment')])

		@if($is_enabled_download_pdf)
		<div class="well row">
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label("prefer_payment_method" , __('lang_v1.prefer_payment_method') . ':') !!}
					@show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-money-bill-alt"></i>
						</span>
						{!! Form::select("prefer_payment_method", $payment_types, 'cash', ['class' => 'form-control','style' => 'width:100%;']); !!}
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label("prefer_payment_account" , __('lang_v1.prefer_payment_account') . ':') !!}
					@show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-money-bill-alt"></i>
						</span>
						{!! Form::select("prefer_payment_account", $accounts, null, ['class' => 'form-control','style' => 'width:100%;']); !!}
					</div>
				</div>
			</div>
		</div>
		@endif

		@if(empty($status) || !in_array($status, ['quotation', 'draft']))
		<div class="payment_row" @if($is_enabled_download_pdf) id="payment_rows_div" @endif>
			<div class="row">
				<div class="col-md-12 mb-12">
					<strong>@lang('lang_v1.advance_balance'):</strong> <span id="advance_balance_text"></span>
					{!! Form::hidden('advance_balance', null, ['id' => 'advance_balance', 'data-error-msg' => __('lang_v1.required_advance_balance_not_available')]); !!}
				</div>
			</div>
			@include('sale_pos.partials.payment_row_form', ['row_index' => 0, 'show_date' => true, 'show_denomination' => true])
		</div>
		<div class="payment_row">
			<div class="row">
				<div class="col-md-12">
					<hr>
					<strong>
						@lang('lang_v1.change_return'):
					</strong>
					<br />
					<span class="lead text-bold change_return_span">0</span>
					{!! Form::hidden("change_return", $change_return['amount'], ['class' => 'form-control change_return input_number', 'required', 'id' => "change_return"]); !!}
					<!-- <span class="lead text-bold total_quantity">0</span> -->
					@if(!empty($change_return['id']))
					<input type="hidden" name="change_return_id" value="{{$change_return['id']}}">
					@endif
				</div>
			</div>
			<div class="row hide payment_row" id="change_return_payment_data">
				<div class="col-md-4">
					<div class="form-group">
						{!! Form::label("change_return_method" , __('lang_v1.change_return_payment_method') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fas fa-money-bill-alt"></i>
							</span>
							@php
							$_payment_method = empty($change_return['method']) && array_key_exists('cash', $payment_types) ? 'cash' : $change_return['method'];

							$_payment_types = $payment_types;
							if(isset($_payment_types['advance'])) {
							unset($_payment_types['advance']);
							}
							@endphp
							{!! Form::select("payment[change_return][method]", $_payment_types, $_payment_method, ['class' => 'form-control col-md-12 payment_types_dropdown', 'id' => 'change_return_method', 'style' => 'width:100%;']); !!}
						</div>
					</div>
				</div>
				@if(!empty($accounts))
				<div class="col-md-4">
					<div class="form-group">
						{!! Form::label("change_return_account" , __('lang_v1.change_return_payment_account') . ':') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fas fa-money-bill-alt"></i>
							</span>
							{!! Form::select("payment[change_return][account_id]", $accounts, !empty($change_return['account_id']) ? $change_return['account_id'] : '' , ['class' => 'form-control select2', 'id' => 'change_return_account', 'style' => 'width:100%;']); !!}
						</div>
					</div>
				</div>
				@endif
				@include('sale_pos.partials.payment_type_details', ['payment_line' => $change_return, 'row_index' => 'change_return'])
			</div>
			<hr>
			<div class="row">
				<div class="col-sm-12">
					<div class="pull-right"><strong>@lang('lang_v1.balance'):</strong> <span class="balance_due">0.00</span></div>
				</div>
			</div>
		</div>
		@endif
		@endcomponent
		@endcan
		@endif

		<div class="row">
			{!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']); !!}
			<div class="col-sm-12 text-center">
				<button type="button" id="submit-sell" class="btn btn-primary btn-big">@lang('messages.save')</button>
				<button type="button" id="save-and-print" class="btn btn-success btn-big">@lang('lang_v1.save_and_print')</button>
			</div>
		</div>

		@if(empty($pos_settings['disable_recurring_invoice']))
		@include('sale_pos.partials.recurring_invoice_modal')
		@endif

		{!! Form::close() !!}
</section>

<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<!-- Modal -->
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
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@include('sale_pos.partials.configure_search_modal')

@stop

@section('javascript')
<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

<!-- Call restaurant module if defined -->
@if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
@endif
<script src="{{ asset('js/sell_create.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {

		$('#add').click(function() {
			$('#term_condition_table').append('<tr id="row' +
				'" class="dynamic-added">' +
				'<td><select class="form-control" id="tc_line[]" name="tc_line[]">' +
				'@foreach($terms_condition as $tc)<option value="{{$tc->id }}">{{$tc->description }}</option>@endforeach</select></td>' +
				'<td><button type="button" name="remove"' +
				'class="btn btn-danger btn_remove">X</button></td>' +
				'</tr>');
		});

		$(document).on('click', '.btn_remove', function() {
			$(this).closest('tr').remove();
		});

	});
</script>
<script type="text/javascript">
	$(document).ready(function() {
		$('#status').change(function() {
			if ($(this).val() == 'final') {
				$('#payment_rows_div').removeClass('hide');
			} else {
				$('#payment_rows_div').addClass('hide');
			}
		});
		$('.paid_on').datetimepicker({
			format: moment_date_format + ' ' + moment_time_format,
			ignoreReadonly: true,
		});

		$('#shipping_documents').fileinput({
			showUpload: false,
			showPreview: false,
			browseLabel: LANG.file_browse_label,
			removeLabel: LANG.remove,
		});

		$(document).on('change', '#prefer_payment_method', function(e) {
			var default_accounts = $('select#select_location_id').length ?
				$('select#select_location_id')
				.find(':selected')
				.data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
			var payment_type = $(this).val();
			if (payment_type) {
				var default_account = default_accounts && default_accounts[payment_type]['account'] ?
					default_accounts[payment_type]['account'] : '';
				var account_dropdown = $('select#prefer_payment_account');
				if (account_dropdown.length && default_accounts) {
					account_dropdown.val(default_account);
					account_dropdown.change();
				}
			}
		});

		function setPreferredPaymentMethodDropdown() {
			var payment_settings = $('#location_id').data('default_payment_accounts');
			payment_settings = payment_settings ? payment_settings : [];
			enabled_payment_types = [];
			for (var key in payment_settings) {
				if (payment_settings[key] && payment_settings[key]['is_enabled']) {
					enabled_payment_types.push(key);
				}
			}
			if (enabled_payment_types.length) {
				$("#prefer_payment_method > option").each(function() {
					if (enabled_payment_types.indexOf($(this).val()) != -1) {
						$(this).removeClass('hide');
					} else {
						$(this).addClass('hide');
					}
				});
			}
		}

		setPreferredPaymentMethodDropdown();

		$('#is_export').on('change', function() {
			if ($(this).is(':checked')) {
				$('div.export_div').show();
			} else {
				$('div.export_div').hide();
			}
		});

		if ($('.payment_types_dropdown').length) {
			$('.payment_types_dropdown').change();
		}

	});
</script>
@endsection