@php
    $quotation_logo_path = public_path('uploads/invoice_logos/TBL-Logo-PNG.png');
    $quotation_logo = is_file($quotation_logo_path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($quotation_logo_path)) : ($receipt_details->logo ?? '');
@endphp
    <table class="quotation-header">
        <tr>
            <td class="quotation-logo-cell">@if($quotation_logo)<div class="quotation-logo-frame"><img class="quotation-logo" src="{{ $quotation_logo }}" alt="Company logo"></div>@endif</td>
            <td class="quotation-brand">
                <div class="quotation-company">{{ $receipt_details->display_name }}</div>
                <div class="quotation-tagline">Your trusted technology supplier and solution partner.</div>
            </td>
        </tr>
    </table>
    <div class="quotation-title"><span>{{ $document_title ?? 'Quotation' }}</span></div>
