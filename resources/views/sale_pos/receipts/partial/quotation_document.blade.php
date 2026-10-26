@php
    $quotation_logo_path = public_path('uploads/invoice_logos/TBL-Logo-PNG.png');
    $quotation_logo = is_file($quotation_logo_path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($quotation_logo_path)) : ($receipt_details->logo ?? '');
    $quotation_author = Auth::user();
@endphp
@if(!empty($quotation_pdf))
    @include('sale_pos.receipts.partial.quotation_footer')
@endif
<div class="quotation-document">
    <table class="quotation-header">
        <tr>
            <td class="quotation-logo-cell">@if($quotation_logo)<div class="quotation-logo-frame"><img class="quotation-logo" src="{{ $quotation_logo }}" alt="Company logo"></div>@endif</td>
            <td class="quotation-brand">
                <div class="quotation-company">{{ $receipt_details->display_name }}</div>
                <div class="quotation-tagline">Your trusted technology supplier and solution partner.</div>
            </td>
        </tr>
    </table>
    <div class="quotation-title"><span>Quotation</span></div>
    <table class="quotation-meta"><tr>
        <td style="width:60%;"><b>Quotation No:</b> {{ $receipt_details->invoice_no }}</td>
        <td style="width:40%;"><b>Date:</b> {{ $receipt_details->invoice_date ?? $receipt_details->current_datetime }}</td>
    </tr></table>
    <div class="quotation-rule"></div>
    <table class="quotation-customer">
        <tr><td style="width:60%;"><b>To,</b></td><td style="width:40%;"></td></tr>
        <tr>
            <td>{{ $receipt_details->business_name ?: ($receipt_details->customer_name ?? '') }}</td>
            <td>@if(!empty($receipt_details->sell_custom_field_2_value))<b>{{ rtrim($receipt_details->sell_custom_field_2_label ?? 'Contact To', ': ') }}:</b> {{ $receipt_details->sell_custom_field_2_value }}@endif</td>
        </tr>
        <tr><td>{!! nl2br(e($receipt_details->address_line_1 ?? '')) !!}</td><td>@if(!empty($receipt_details->customer_mobile))<b>Mobile:</b> {{ $receipt_details->customer_mobile }}@endif</td></tr>
    </table>
    <div class="quotation-sales">@if(!empty($receipt_details->sales_person))<b>{{ rtrim($receipt_details->sales_person_label ?: 'Sales By', ': ') }}:</b> {{ $receipt_details->sales_person }}@endif</div>
    <table class="quotation-items">
        <thead><tr>
            <th style="width:4%;">SL.</th><th style="width:13%;">Part/Model<br>Number</th>
            <th style="width:37%;">Item Name &amp; Description</th><th style="width:11%;">Delivery<br>Time</th>
            <th style="width:6%;">Unit</th><th style="width:5%;">Qty</th>
            <th style="width:14%;">Unit Price (TK)</th><th style="width:10%;">Total<br>Price(TK)</th>
        </tr></thead>
        <tbody>
        @forelse($receipt_details->lines as $line)
            <tr>
                <td class="quotation-center">{{ $loop->iteration }}</td><td>{{ $line['sub_sku'] ?? '' }}</td>
                <td>
                    <div>{{ $line['name'] }}</div>
                    @if(!empty($line['brand']))<div>Brand: {{ $line['brand'] }}</div>@endif
                    @if(!empty($line['origin']))<div>Origin: {{ $line['origin'] }}</div>@endif
                    @php($formatted_description = sanitizeEditorHtmlForPdf($line['product_description'] ?? ''))
                    @if($formatted_description !== '')<div class="product-description">{!! $formatted_description !!}</div>@endif
                    @if(!empty($line['sell_line_note']))<div class="product-description">{!! sanitizeEditorHtmlForPdf($line['sell_line_note']) !!}</div>@endif
                    @if(!empty($line['warranty_name']))<div>{{ $line['warranty_name'] }}</div>@endif
                </td>
                <td class="quotation-center">{{ $line['delivery_time'] ?? $line['product_custom_fields'] ?? '' }}</td>
                <td class="quotation-center">{{ $line['units'] ?? '' }}</td><td class="quotation-center">{{ $line['quantity'] }}</td>
                <td class="quotation-right">{{ $line['unit_price_inc_tax'] }}</td><td class="quotation-right">{{ $line['line_total'] }}</td>
            </tr>
            @foreach($line['modifiers'] ?? [] as $modifier)
                <tr><td></td><td>{{ $modifier['sub_sku'] ?? '' }}</td><td>{{ $modifier['name'] }} {{ $modifier['variation'] ?? '' }}</td><td></td><td class="quotation-center">{{ $modifier['units'] ?? '' }}</td><td class="quotation-center">{{ $modifier['quantity'] }}</td><td class="quotation-right">{{ $modifier['unit_price_inc_tax'] }}</td><td class="quotation-right">{{ $modifier['line_total'] }}</td></tr>
            @endforeach
        @empty
            <tr><td colspan="8">&nbsp;</td></tr>
        @endforelse
        </tbody>
    </table>
    <table class="quotation-summary"><tr>
        <td style="width:60%;"><b>Amount In Words : {{ $receipt_details->total_in_words ?? '' }} Taka Only</b></td>
        <td style="width:40%;"><table class="quotation-totals">
            <tr><th>{!! $receipt_details->subtotal_label !!}</th><td>{{ $receipt_details->subtotal }}</td></tr>
            @foreach(['shipping_charges', 'packing_charge'] as $charge)
                @if(!empty($receipt_details->$charge))<tr><th>{!! $receipt_details->{$charge . '_label'} ?? $charge !!}</th><td>(+) {{ $receipt_details->$charge }}</td></tr>@endif
            @endforeach
            @if(!empty($receipt_details->discount))<tr><th>{!! $receipt_details->discount_label !!}</th><td>(-) {{ $receipt_details->discount }}</td></tr>@endif
            @foreach($receipt_details->additional_expenses ?? [] as $label => $amount)<tr><th>{{ $label }}:</th><td>(+) {{ $amount }}</td></tr>@endforeach
            @if(!empty($receipt_details->tax))<tr><th>{!! $receipt_details->tax_label !!}</th><td>(+) {{ $receipt_details->tax }}</td></tr>@endif
            @if(!empty($receipt_details->round_off_amount))<tr><th>{!! $receipt_details->round_off_label !!}</th><td>{{ $receipt_details->round_off }}</td></tr>@endif
            <tr><th>{!! $receipt_details->total_label !!}</th><td>{{ $receipt_details->total }}</td></tr>
        </table></td>
    </tr></table>
    @if(count($tc_description ?? []))
        <div class="quotation-terms"><b>Terms and Conditions:</b></div>
        <table class="quotation-terms">@foreach($tc_description as $tc)<tr><td class="quotation-term-number">{{ $loop->iteration }}.</td><td>{{ $tc->description }}</td></tr>@endforeach</table>
    @endif
    @if(!empty($receipt_details->additional_notes))<div class="quotation-terms">{!! nl2br(e($receipt_details->additional_notes)) !!}</div>@endif
    <div class="quotation-signoff">
        <b>Best Regards,</b>
        @if($quotation_author)
            <div class="quotation-signoff-name"><p>{{ trim($quotation_author->first_name . ' ' . $quotation_author->last_name) }}</p><p>{{ $quotation_author->contact_number }}</p><p>{{ $quotation_author->custom_field_1 }}</p></div>
        @endif
    </div>
</div>

@if(empty($quotation_pdf))
    @include('sale_pos.receipts.partial.quotation_footer')
@endif
