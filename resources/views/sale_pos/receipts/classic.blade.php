<!-- business information here -->
<!--Businees and Customer Information -->
<div class="row" style="color: #000000 !important;">
	<!-- business information here -->
	<div class="col-xs-12 text-center">
		<table class="table">
			<tbody>
				<tr>
					<th width="20%" style="border: none;"><img style="max-height: 120px; width: auto;" src="{{$receipt_details->logo}}" class="img img-responsive center-block"></th>
					<th width="60%" style="border: none;">
						<h2 class="text-center mt-0">
							<!-- Shop & Location Name  -->
							@if(!empty($receipt_details->display_name))
							{{$receipt_details->display_name}}
							@endif
						</h2>

						<h5 class="text-center">
							@if(!empty($receipt_details->address))
							<p class="text-center">
								{!! $receipt_details->address !!}<br>
								{!! $receipt_details->contact !!}, {{ $receipt_details->website }}<br>
							</p>
							<!-- <small class="text-center">
								{!! $receipt_details->address !!}
							</small>
							<small class="text-center">
								@endif
								@if(!empty($receipt_details->contact))
								<br />{!! $receipt_details->contact !!}
								@endif
								@if(!empty($receipt_details->contact) && !empty($receipt_details->website))
								,
								@endif
								@if(!empty($receipt_details->website))
								{{ $receipt_details->website }}
								@endif
								@if(!empty($receipt_details->location_custom_fields))
								<br>{{ $receipt_details->location_custom_fields }}
								@endif
							</small> -->
						</h5>
					</th>
					<th width="20%" style="border: none;"></th>

			</tbody>

		</table>

		<!-- Title of receipt -->
		@if(!empty($receipt_details->invoice_heading))
		<h3 class="text-center">
			{!! $receipt_details->invoice_heading !!}
		</h3>
		@endif
	</div>

	<div class="col-xs-12 text-center">
		<!-- Invoice  number, Date  -->
		<p style="width: 100% !important" class="word-wrap">
			<span class="pull-left text-left word-wrap">
				@if(!empty($receipt_details->invoice_no_prefix))
				<b>{!! $receipt_details->invoice_no_prefix !!}</b>
				@endif
				@if($receipt_details->invoice_status == 'quotation')

				@else
				{{$receipt_details->invoice_no}}</br>
				@endif
			</span>

			<span class="pull-right text-left">
				<b>{{$receipt_details->date_label}}:</b> {{$receipt_details->invoice_date}}</br>
			</span>
		</p>
		<table class="table table-responsive table-sm" width="100%">
			<tbody>
				<tr>
					<td width="50%" class="text-left word-wrap">@if(!empty($receipt_details->customer_info))
						<b>Billing Address,</b> <br>
						Name: {!! $receipt_details->customer_name !!} <br>
						Phone: {!! $receipt_details->customer_mobile !!} <br>
						Address: {!! $receipt_details->address_line_1 !!} <br>
						@endif
					</td>
					<td width="50%" class="text-left word-wrap"><strong>@lang('lang_v1.shipping_address'),</strong><br>
						Delivery To: {!! $receipt_details->delivered_to !!}</br>
						Address: {!! $receipt_details->shipping_address !!}</br>
						Delivery Type: {!! $receipt_details->shipping_status !!}
						@if(!empty($receipt_details->shipping_custom_field_1_label))
						<br><strong>{!!$receipt_details->shipping_custom_field_1_label!!} :</strong> {!!$receipt_details->shipping_custom_field_1_value ?? ''!!}
						@endif
					</td>
				</tr>

			</tbody>
		</table>
		<p style="width: 100% !important" class="word-wrap">
			<span class="pull-left text-left word-wrap">
				@if(!empty($receipt_details->sales_person_label))
				<b>{{ $receipt_details->sales_person_label }}</b> {{ $receipt_details->sales_person }}
				@endif
			</span>
		</p>
	</div>
</div>
<!--End Businees and Customer Information -->

<!--Product Table Information -->
<div class="row" style="color: #000000 !important;">
	@includeIf('sale_pos.receipts.partial.common_repair_invoice')
</div>

<div class="row" style="color: #000000 !important;">
	<div class="col-xs-12">
		<br />
		@php
		$p_width = 45;
		@endphp
		@if(!empty($receipt_details->item_discount_label))
		@php
		$p_width -= 10;
		@endphp
		@endif
		@if(!empty($receipt_details->discounted_unit_price_label))
		@php
		$p_width -= 10;
		@endphp
		@endif
		<table class="table table-responsive table-slim table-bordered">
			<thead>
				<tr>
					<th class="text-center" width="5%">SL.</th>
					<th class="text-center" width="15%">Code/SKU</th>
					<th class="text-center" width="{{$p_width}}%">{{$receipt_details->table_product_label}}</th>

					<th class="text-right" width="15%">{{$receipt_details->table_qty_label}}</th>
					<th class="text-right" width="15%">{{$receipt_details->table_unit_price_label}}</th>
					@if(!empty($receipt_details->discounted_unit_price_label))
					<th class="text-right" width="10%">{{$receipt_details->discounted_unit_price_label}}</th>
					@endif
					@if(!empty($receipt_details->item_discount_label))
					<th class="text-right" width="10%">{{$receipt_details->item_discount_label}}</th>
					@endif
					<th class="text-right" width="15%">{{$receipt_details->table_subtotal_label}}</th>
				</tr>
			</thead>
			<tbody>
				@forelse($receipt_details->lines as $line)
				<tr>
					<td>{{ $loop->iteration }}</td>
					<td>{{$line['sub_sku']}}</td>
					<td>
						@if(!empty($line['image']))
						<img src="{{$line['image']}}" alt="Image" width="50" style="float: left; margin-right: 8px;">
						@endif
						{{$line['name']}} <br>
						@if(!empty($line['brand'])) {{'Brand: '.$line['brand']}} @endif
						<!-- {{$line['product_variation']}} {{$line['variation']}} -->
						<!-- @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif -->
						<!-- @if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif -->
						@if(!empty($line['product_description']))
						<small>
							{!!$line['product_description']!!}
						</small>
						@endif
						@if(!empty($line['sell_line_note']))
						<br>
						<small>
							{!!$line['sell_line_note']!!}
						</small>
						@endif
						@if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}: {{$line['lot_number']}} @endif
						@if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}: {{$line['product_expiry']}} @endif

						@if(!empty($line['warranty_name'])) <br><small>{{$line['warranty_name']}} </small>@endif @if(!empty($line['warranty_exp_date'])) <small>- {{@format_date($line['warranty_exp_date'])}} </small>@endif
						@if(!empty($line['warranty_description'])) <small> {{$line['warranty_description'] ?? ''}}</small>@endif

						@if($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
						<br><small>
							1 {{$line['units']}} = {{$line['base_unit_multiplier']}} {{$line['base_unit_name']}} <br>
							{{$line['base_unit_price']}} x {{$line['orig_quantity']}} = {{$line['line_total']}}
						</small>
						@endif
					</td>

					<td class="text-right">
						{{$line['quantity']}} {{$line['units']}}

						@if($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
						<br><small>
							{{$line['quantity']}} x {{$line['base_unit_multiplier']}} = {{$line['orig_quantity']}} {{$line['base_unit_name']}}
						</small>
						@endif
					</td>
					<td class="text-right">{{$line['unit_price_before_discount']}}</td>
					@if(!empty($receipt_details->discounted_unit_price_label))
					<td class="text-right">
						{{$line['unit_price_inc_tax']}}
					</td>
					@endif
					@if(!empty($receipt_details->item_discount_label))
					<td class="text-right">
						{{$line['total_line_discount'] ?? '0.00'}}

						@if(!empty($line['line_discount_percent']))
						({{$line['line_discount_percent']}}%)
						@endif
					</td>
					@endif
					<td class="text-right">{{$line['line_total']}}</td>
				</tr>
				@if(!empty($line['modifiers']))
				@foreach($line['modifiers'] as $modifier)
				<tr>
					<td>
						{{$modifier['name']}} {{$modifier['variation']}}
						@if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}}@endif
						@if(!empty($modifier['sell_line_note']))({!!$modifier['sell_line_note']!!}) @endif
					</td>
					<td class="text-right">{{$modifier['quantity']}} {{$modifier['units']}} </td>
					<td class="text-right">{{$modifier['unit_price_inc_tax']}}</td>
					@if(!empty($receipt_details->discounted_unit_price_label))
					<td class="text-right">{{$modifier['unit_price_exc_tax']}}</td>
					@endif
					@if(!empty($receipt_details->item_discount_label))
					<td class="text-right">0.00</td>
					@endif
					<td class="text-right">{{$modifier['line_total']}}</td>
				</tr>
				@endforeach
				@endif
				@empty
				<tr>
					<td colspan="4">&nbsp;</td>
					@if(!empty($receipt_details->discounted_unit_price_label))
					<td></td>
					@endif
					@if(!empty($receipt_details->item_discount_label))
					<td></td>
					@endif
				</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>
<!--End Product Table Information -->

<div class="row" style="color: #000000 !important;">
	<div class="col-md-12">
		<hr />
	</div>
	<div class="col-xs-8">
		@if(!empty($receipt_details->total_in_words))
		<strong class="text-capitalize">({{$receipt_details->total_in_words}})</strong><br>
		@endif

		<table class="table table-slim">

			@if(!empty($receipt_details->payments))
			@foreach($receipt_details->payments as $payment)
			<tr>
				<td>{{$payment['method']}}</td>
				<td class="text-right">{{$payment['amount']}}</td>
				<td class="text-right">{{$payment['date']}}</td>
			</tr>
			@endforeach
			@endif

			<!-- Total Paid-->
			@if(!empty($receipt_details->total_paid))
			<tr>
				<th>
					{!! $receipt_details->total_paid_label !!}
				</th>
				<td class="text-right">
					{{$receipt_details->total_paid}}
				</td>
			</tr>
			@endif

			<!-- Total Due-->
			@if(!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
			<tr>
				<th>
					{!! $receipt_details->total_due_label !!}
				</th>
				<td class="text-right">
					{{$receipt_details->total_due}}
				</td>
			</tr>
			@endif

			@if(!empty($receipt_details->all_due))
			<tr>
				<th>
					{!! $receipt_details->all_bal_label !!}
				</th>
				<td class="text-right">
					{{$receipt_details->all_due}}
				</td>
			</tr>
			@endif
		</table>
		@if($receipt_details->invoice_status == 'quotation')
		<p><strong>Terms and Conditions:</strong></p>
		<table class="table table-slim">
			@foreach($tc_description as $tc)
			<tr>
				<td>{{ $loop->iteration.'. ' }} </td>
				<td>{{ $tc->description }}</td>
			</tr>
			@endforeach
		</table>
		@endif

	</div>

	<div class="col-xs-4">
		<div class="table-responsive">

			<table class="table table-slim">
				<tbody>
					@if(!empty($receipt_details->total_quantity_label))
					<tr>
						<th style="width:70%">
							{!! $receipt_details->total_quantity_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->total_quantity}}
						</td>
					</tr>
					@endif

					@if(!empty($receipt_details->total_items_label))
					<tr>
						<th style="width:70%">
							{!! $receipt_details->total_items_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->total_items}}
						</td>
					</tr>
					@endif
					<tr>
						<th style="width:40%">
							{!! $receipt_details->subtotal_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->subtotal}}
						</td>
					</tr>
					@if(!empty($receipt_details->total_exempt_uf))
					<tr>
						<th style="width:40%">
							@lang('lang_v1.exempt')
						</th>
						<td class="text-right">
							{{$receipt_details->total_exempt}}
						</td>
					</tr>
					@endif
					<!-- Shipping Charges -->
					@if(!empty($receipt_details->shipping_charges))
					<tr>
						<th style="width:40%">
							{!! $receipt_details->shipping_charges_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->shipping_charges}}
						</td>
					</tr>
					@endif

					@if(!empty($receipt_details->packing_charge))
					<tr>
						<th style="width:40%">
							{!! $receipt_details->packing_charge_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->packing_charge}}
						</td>
					</tr>
					@endif

					<!-- Discount -->
					@if( !empty($receipt_details->discount) )
					<tr>
						<th>
							{!! $receipt_details->discount_label !!}
						</th>

						<td class="text-right">
							(-) {{$receipt_details->discount}}
						</td>
					</tr>
					@endif

					@if( !empty($receipt_details->total_line_discount) )
					<tr>
						<th>
							{!! $receipt_details->line_discount_label !!}
						</th>

						<td class="text-right">
							(-) {{$receipt_details->total_line_discount}}
						</td>
					</tr>
					@endif

					@if( !empty($receipt_details->additional_expenses) )
					@foreach($receipt_details->additional_expenses as $key => $val)
					<tr>
						<td>
							{{$key}}:
						</td>

						<td class="text-right">
							(+) {{$val}}
						</td>
					</tr>
					@endforeach
					@endif

					@if( !empty($receipt_details->reward_point_label) )
					<tr>
						<th>
							{!! $receipt_details->reward_point_label !!}
						</th>

						<td class="text-right">
							(-) {{$receipt_details->reward_point_amount}}
						</td>
					</tr>
					@endif

					<!-- Tax -->
					@if( !empty($receipt_details->tax) )
					<tr>
						<th>Total VAT:</th>
						<td class="text-right">
							(+) {{$receipt_details->tax}}
						</td>
					</tr>
					@endif

					@if( $receipt_details->round_off_amount > 0)
					<tr>
						<th>
							{!! $receipt_details->round_off_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->round_off}}
						</td>
					</tr>
					@endif

					<!-- Total -->
					<tr>
						<th>
							{!! $receipt_details->total_label !!}
						</th>
						<td class="text-right">
							{{$receipt_details->total}}

						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="border-bottom col-md-12">
		@if(empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label) )
		<!-- tax -->
		@if(!empty($receipt_details->taxes))
		<table class="table table-slim table-bordered">
			<tr>
				<th colspan="2" class="text-center">{{$receipt_details->tax_summary_label}}</th>
			</tr>
			@foreach($receipt_details->taxes as $key => $val)
			<tr>
				<td class="text-center"><b>{{$key}}</b></td>
				<td class="text-center">{{$val}}</td>
			</tr>
			@endforeach
		</table>
		@endif
		@endif
	</div>
<br>
<br>
<br>
<br>
<br>
<br>
	<div class="border-bottom col-md-12">
		<table class="table" width="100%">
			<tr>
				<td width="60%" style="border-top: none;">
					<strong>{{ Auth::user()->first_name .' '. Auth::user()->last_name}}</strong>
					</br>
					</br>
					<strong style="text-decoration: overline;">Authorized Signature</strong></br>
					Designation: {{ Auth::user()->custom_field_1 }}</br>
					Mobile: {{ Auth::user()->contact_number }}</br>
				</td>
				<td width="40%" style="border-top: none;">
					</br>
					</br>
					<strong style="text-decoration: overline;">Receiver's Signature</strong></br>
					Name:</br>
					Designation: </br>
					Mobile: </br>
				</td>
			</tr>
		</table>
	</div>

	@if(!empty($receipt_details->additional_notes))
	<div class="col-xs-12">
		<p>{!! nl2br($receipt_details->additional_notes) !!}</p>
	</div>
	@endif

</div>
<div class="row" style="color: #000000 !important;">
	@if(!empty($receipt_details->footer_text))
	<div class="@if($receipt_details->show_barcode || $receipt_details->show_qr_code) col-xs-8 @else col-xs-12 @endif">
		{!! $receipt_details->footer_text !!}
	</div>
	@endif
	@if($receipt_details->show_barcode || $receipt_details->show_qr_code)
	<div class="@if(!empty($receipt_details->footer_text)) col-xs-4 @else col-xs-12 @endif text-center">
		@if($receipt_details->show_barcode)
		{{-- Barcode --}}
		<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
		@endif

		@if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
		<img class="center-block mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">
		@endif
	</div>
	@endif

</div>