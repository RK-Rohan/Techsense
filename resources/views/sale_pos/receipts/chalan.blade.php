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
		<h3 class="text-center">Delivery Chalan</h3>
		@endif
	</div>

	<div class="col-xs-12 text-center">
		<!-- Invoice  number, Date  -->
		<p style="width: 100% !important" class="word-wrap">
			<span class="pull-left text-left word-wrap">
				@if(!empty($receipt_details->invoice_no_prefix))
				<b>Chalan No.</b>
				@endif
				@if($receipt_details->invoice_status == 'quotation')

				@else
				{{'CH-'.$receipt_details->invoice_no}}</br>
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
	</div>
</div>
<!--End Businees and Customer Information -->

<!--Product Table Information -->


<div class="row color-555">
	<div class="col-xs-12">

		<table class="table table-bordered">
			<thead>
				<tr style="background-color: #357ca5 !important; color: white !important; font-size: 20px !important" class="table-no-side-cell-border table-no-top-cell-border text-center">
					<td style="background-color: #357ca5 !important; color: white !important; width: 5% !important">#</td>

					<td style="background-color: #357ca5 !important; color: white !important; width: 20% !important">
						Code/SKU
					</td>

					<td style="background-color: #357ca5 !important; color: white !important; width: 55% !important">
						{{$receipt_details->table_product_label}}
					</td>

					<td style="background-color: #357ca5 !important; color: white !important; width: 20% !important;">
						{{$receipt_details->table_qty_label}}
					</td>
				</tr>
			</thead>
			<tbody>
				@foreach($receipt_details->lines as $line)
				<tr>
					<td class="text-center">
						{{$loop->iteration}}
					</td>
					<td>{{$line['sub_sku']}}</td>
					<td style="word-break: break-all;">
						{{$line['name']}} {{$line['product_variation']}} {{$line['variation']}}
						@if(!empty($line['sell_line_note']))({!!$line['sell_line_note']!!}) @endif
						@if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}: {{$line['lot_number']}} @endif
						@if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}: {{$line['product_expiry']}} @endif
					</td>
					<td class="text-right">
						{{$line['quantity']}} {{$line['units']}}
					</td>
				</tr>
				@if(!empty($line['modifiers']))
				@foreach($line['modifiers'] as $modifier)
				<tr>
					<td class="text-center">
						&nbsp;
					</td>
					<td>
						{{$modifier['name']}} {{$modifier['variation']}}
						@if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif
						@if(!empty($modifier['sell_line_note']))({!!$modifier['sell_line_note']!!}) @endif
					</td>
					<td class="text-right">
						{{$modifier['quantity']}} {{$modifier['units']}}
					</td>
				</tr>
				@endforeach
				@endif
				@endforeach

				@php
				$lines = count($receipt_details->lines);
				@endphp

				@for ($i = $lines; $i < 1; $i++) <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					</tr>
					@endfor

			</tbody>
		</table>
	</div>
</div>
<!--End Product Table Information -->

<div class="row" style="color: #000000 !important;">
	<div class="col-md-12">
		<hr />
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
	<div class="border-bottom col-md-12">
		<table class="table" width="100%">
			<tr>
				<td width="60%" style="border-top: none;">
					<strong>{{ Auth::user()->first_name .' '. Auth::user()->last_name}}</strong>
					</br>
					</br>
					</br>
					<strong style="text-decoration: overline;">Authorized Signature</strong></br>
					Designation: {{ Auth::user()->custom_field_1 }}</br>
					Mobile: {{ Auth::user()->contact_number }}</br>
				</td>
				<td width="40%" style="border-top: none;">
					<strong>Goods Received in Good Condition</strong>
					</br>
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

<footer style="position: fixed; bottom: 0cm; left: 0px; right: 0px; height: 50px; ">
	Copyright &copy; <?php echo date("Y"); ?>
</footer>