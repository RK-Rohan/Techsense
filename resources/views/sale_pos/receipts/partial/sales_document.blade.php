@php
    $is_challan = ($document_kind ?? 'invoice') === 'challan';
    $document_author = Auth::user();
@endphp
@if(!empty($quotation_pdf))
    @include('sale_pos.receipts.partial.quotation_footer')
@endif
<div class="quotation-document">
    @include('sale_pos.receipts.partial.document_header', ['document_title' => $is_challan ? 'Delivery Challan' : 'Sales Invoice'])
    <table class="quotation-meta"><tr>
        <td style="width:33%;"><b>{{ $is_challan ? 'Challan No:' : 'Invoice No:' }}</b> {{ $is_challan ? 'CH-' : '' }}{{ $receipt_details->invoice_no }}</td>
        <td style="width:33%;">@if(!empty($receipt_details->sell_custom_field_1_value))<b>{{ rtrim($receipt_details->sell_custom_field_1_label ?? '', ': ') }}:</b> {{ $receipt_details->sell_custom_field_1_value }}@endif</td>
        <td style="width:34%;"><b>Date:</b> {{ $receipt_details->invoice_date ?? $receipt_details->current_datetime }}</td>
    </tr></table>
    <div class="quotation-rule"></div>
    <table class="quotation-customer"><tr>
        <td style="width:60%; padding-right:10pt;">
            <b>Billing Address,</b><br>
            Name: {{ ($receipt_details->business_name ?? '') ?: ($receipt_details->customer_name ?? '') }}<br>
            Address: {!! nl2br(e($receipt_details->address_line_1 ?? '')) !!}
        </td>
        <td style="width:40%;">
            <b>Shipping Address,</b><br>
            Delivery To: {{ $receipt_details->delivered_to ?? '' }}<br>
            Mobile: {{ $receipt_details->customer_mobile ?? '' }}<br>
            Address: {!! $receipt_details->shipping_address ?? '' !!}<br>
            Delivery Type: {{ $receipt_details->shipping_status ?? '' }}
        </td>
    </tr></table>
    <div class="quotation-sales">@if(!empty($receipt_details->sales_person))<b>{{ rtrim(($receipt_details->sales_person_label ?? '') ?: 'Sales By', ': ') }}:</b> {{ $receipt_details->sales_person }}@endif</div>
    <table class="quotation-items">
        <thead><tr>
            <th style="width:4%;">SL.</th>
            <th style="width:{{ $is_challan ? 22 : 12 }}%;">Part/Model<br>Number</th>
            <th style="width:{{ $is_challan ? 54 : 38 }}%;">Item Name &amp; Description</th>
            <th style="width:{{ $is_challan ? 10 : 7 }}%;">Unit</th>
            <th style="width:{{ $is_challan ? 10 : 7 }}%;">Qty</th>
            @unless($is_challan)<th style="width:15%;">Unit Price (TK)</th><th style="width:17%;">Total Price (TK)</th>@endunless
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
                </td>
                <td class="quotation-center">{{ $line['units'] ?? '' }}</td><td class="quotation-center">{{ $line['quantity'] }}</td>
                @unless($is_challan)<td class="quotation-right">{{ $line['unit_price_inc_tax'] }}</td><td class="quotation-right">{{ $line['line_total'] }}</td>@endunless
            </tr>
            @foreach($line['modifiers'] ?? [] as $modifier)
                <tr><td></td><td>{{ $modifier['sub_sku'] ?? '' }}</td><td>{{ $modifier['name'] }} {{ $modifier['variation'] ?? '' }}
                    @if(!empty($modifier['sell_line_note']))<div>{!! sanitizeEditorHtmlForPdf($modifier['sell_line_note']) !!}</div>@endif
                </td><td class="quotation-center">{{ $modifier['units'] ?? '' }}</td><td class="quotation-center">{{ $modifier['quantity'] }}</td>
                @unless($is_challan)<td class="quotation-right">{{ $modifier['unit_price_inc_tax'] }}</td><td class="quotation-right">{{ $modifier['line_total'] }}</td>@endunless</tr>
            @endforeach
        @empty
            <tr><td colspan="{{ $is_challan ? 5 : 7 }}">&nbsp;</td></tr>
        @endforelse
        </tbody>
    </table>
    @unless($is_challan)
        <table class="quotation-summary"><tr>
            <td style="width:60%; padding-right:8pt;">
                @if(!empty($receipt_details->total_in_words))<b>Amount In Words : {{ $receipt_details->total_in_words }} Taka Only</b>@endif
                <table class="sales-payments">
                    @foreach($receipt_details->payments ?? [] as $payment)<tr><td>{{ $payment['method'] }}</td><td class="quotation-right">{{ $payment['amount'] }}</td><td class="quotation-right">{{ $payment['date'] }}</td></tr>@endforeach
                    @if(!empty($receipt_details->total_paid))<tr><th>{{ $receipt_details->total_paid_label }}</th><td class="quotation-right">{{ $receipt_details->total_paid }}</td><td></td></tr>@endif
                    @if(!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))<tr><th>{{ $receipt_details->total_due_label }}</th><td class="quotation-right">{{ $receipt_details->total_due }}</td><td></td></tr>@endif
                </table>
            </td>
            <td style="width:40%;"><table class="quotation-totals">
                <tr><th>{!! $receipt_details->subtotal_label !!}</th><td>{{ $receipt_details->subtotal }}</td></tr>
                @if(!empty($receipt_details->discount))<tr><th>{!! $receipt_details->discount_label !!}</th><td>(-) {{ $receipt_details->discount }}</td></tr>@endif
                @if(!empty($receipt_details->tax))<tr><th>{!! $receipt_details->tax_label !!}</th><td>(+) {{ $receipt_details->tax }}</td></tr>@endif
                <tr><th>{!! $receipt_details->total_label !!}</th><td>{{ $receipt_details->total }}</td></tr>
            </table></td>
        </tr></table>
    @endunless
    @if(count($tc_description ?? []))
        <div class="quotation-terms"><b>Terms and Conditions:</b></div>
        <table class="quotation-terms">@foreach($tc_description as $tc)<tr><td class="quotation-term-number">{{ $loop->iteration }}.</td><td>{{ $tc->description }}</td></tr>@endforeach</table>
    @endif
    @if(empty($quotation_pdf) && !empty($receipt_details->additional_notes))
        <div class="quotation-terms">{!! nl2br(e($receipt_details->additional_notes)) !!}</div>
    @endif
    <table class="sales-signatures">
        <tr><td style="width:60%;"><b>{{ $document_author ? trim($document_author->first_name . ' ' . $document_author->last_name) : '' }}</b></td><td style="width:40%;">@if($is_challan)<b>Goods Received in Good Condition</b>@endif</td></tr>
        <tr><td><b class="sales-signature-label">Authorized Signature</b></td><td><b class="sales-signature-label">Receiver's Signature</b></td></tr>
        <tr><td>Designation: {{ $document_author->custom_field_1 ?? '' }}</td><td>Name:</td></tr>
        <tr><td>Mobile: {{ $document_author->contact_number ?? '' }}</td><td>Designation:</td></tr>
        <tr><td></td><td>Mobile:</td></tr>
    </table>
    @if(empty($quotation_pdf))
        @if(!empty($receipt_details->footer_text))<div class="quotation-terms">{!! $receipt_details->footer_text !!}</div>@endif
        @if(!empty($receipt_details->show_barcode))<div class="quotation-center"><img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}" alt="Invoice barcode"></div>@endif
        @if(!empty($receipt_details->show_qr_code) && !empty($receipt_details->qr_code_text))<div class="quotation-center"><img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}" alt="Invoice QR code"></div>@endif
    @endif
</div>
@if(empty($quotation_pdf))
    @include('sale_pos.receipts.partial.quotation_footer')
@endif
