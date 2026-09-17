{{--
    Renders the line items of a sell quotation as purchase entry rows.

    The quoted quantity carries over; cost is left to the purchase row's own
    default, since what a product sells for is not what it costs to buy.
--}}
@foreach($sell_lines as $sell_line)
	@include('purchase.partials.purchase_entry_row', [
		'variations' => [$sell_line->variations],
		'product' => $sell_line->product,
		'row_count' => $row_count,
		'variation_id' => $sell_line->variation_id,
		'taxes' => $taxes,
		'currency_details' => $currency_details,
		'hide_tax' => $hide_tax,
		'sub_units' => $sub_units_array[$sell_line->id],
		'quotation_line' => $sell_line,
	])
	@php
		$row_count++;
	@endphp
@endforeach
