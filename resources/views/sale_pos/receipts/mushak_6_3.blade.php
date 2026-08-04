<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page { margin: 12mm 13mm 10mm; }
        * { box-sizing: border-box; }
        body { color: #000; font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.2; }
        table { border-collapse: collapse; width: 100%; }
        .header td { vertical-align: top; }
        .seal-cell { width: 18%; text-align: center; padding-top: 2px; }
        .seal { height: 52px; width: 52px; }
        .heading { color: #006dcc; text-align: center; width: 64%; }
        .heading h1, .heading h2, .heading p { margin: 0; }
        .heading h1 { font-size: 12px; font-weight: normal; }
        .heading h2 { font-size: 11px; margin-top: 2px; }
        .heading .vat-title { color: #b00080; }
        .heading p { font-size: 9px; margin-top: 3px; }
        .form-number { color: #009a36; font-size: 10px; font-weight: bold; text-align: right; width: 18%; padding-top: 25px; }
        .seller { margin: 4px 17% 13px; width: 66%; }
        .seller td, .party td { padding: 1.5px 2px; vertical-align: top; }
        .field-label { white-space: nowrap; }
        .colon { text-align: center; width: 3%; }
        .seller .field-label { width: 31%; }
        .party-wrap { margin-bottom: 12px; }
        .party-wrap > tbody > tr > td { vertical-align: top; }
        .party-left { width: 67%; padding-right: 12px; }
        .party-right { width: 33%; }
        .party .field-label { width: 37%; }
        .party-right .field-label { width: 47%; }
        .field-value { word-wrap: break-word; }
        .uppercase { text-transform: uppercase; }
        .items { table-layout: fixed; page-break-inside: auto; }
        .items thead { display: table-header-group; }
        .items tr { page-break-inside: avoid; }
        .items th, .items td { border: 1px solid #000; padding: 2px 1.5px; vertical-align: middle; }
        .items th { font-size: 6.7px; font-weight: normal; text-align: center; }
        .items td { font-size: 7px; }
        .items .number { text-align: right; white-space: nowrap; }
        .items .centered { text-align: center; }
        .items .description { line-height: 1.1; }
        .items .blank-row td { height: 17px; }
        .items tfoot td { font-weight: normal; }
        .items tfoot .total-label, .items tfoot .total-value { font-weight: bold; }
        .authorisation { margin-top: 8px; width: 55%; }
        .authorisation td { padding: 2px 2px; vertical-align: top; }
        .authorisation .label { width: 34%; white-space: nowrap; }
        .authorisation .colon { text-align: center; width: 4%; }
        .authorisation .value { width: 62%; }
        .authorisation .signature-row td,
        .authorisation .seal-row td { height: 17px; }
        .notes { font-size: 6.8px; margin: 13px 0 0; }
    </style>
</head>
<body>
    @php
        $money = function ($value) {
            return number_format((float) $value, 2, '.', ',');
        };
        $number = function ($value) {
            return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
        };
        $percent = function ($value) {
            return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
        };
        $issuedAt = \Carbon\Carbon::parse($transaction->transaction_date);
        $purchaserName = optional($transaction->contact)->supplier_business_name
            ?: optional($transaction->contact)->full_name;
        $totalValue = $lines->sum('total_value');
        $totalVat = $lines->sum('vat_amount');
        $totalVatFraction = $totalValue > 0 ? $totalVat / $totalValue : 0;
    @endphp

    <table class="header">
        <tr>
            <td class="seal-cell">
                @if ($government_seal)
                    <img class="seal" src="data:image/png;base64,{{ $government_seal }}" alt="Government of Bangladesh seal">
                @endif
            </td>
            <td class="heading">
                <h1>Government of Peoples' Republic of Bangladesh</h1>
                <h2>National Board of Revenue</h2>
                <h2 class="vat-title">VAT Invoice</h2>
                <p>As per Para (Ga) and Para (Cha) of Subrule 1 of Rule 40</p>
            </td>
            <td class="form-number">Mushak - 6.3</td>
        </tr>
    </table>

    <table class="seller">
        <tr><td class="field-label">Name of registered person</td><td class="colon">:</td><td class="field-value uppercase">{{ optional($transaction->business)->name }}</td></tr>
        <tr><td class="field-label">BIN of the registered person</td><td class="colon">:</td><td class="field-value">{{ $seller_bin }}</td></tr>
        <tr><td class="field-label">Invoice issuance address</td><td class="colon">:</td><td class="field-value">{{ $seller_address }}</td></tr>
    </table>

    <table class="party-wrap">
        <tr>
            <td class="party-left">
                <table class="party">
                    <tr><td class="field-label">Name of Purchaser</td><td class="colon">:</td><td class="field-value">{{ $purchaserName }}</td></tr>
                    <tr><td class="field-label">Purchaser's BIN</td><td class="colon">:</td><td class="field-value">{{ $purchaser_bin }}</td></tr>
                    <tr><td class="field-label">Address of Purchaser</td><td class="colon">:</td><td class="field-value">{{ $purchaser_address }}</td></tr>
                    <tr><td class="field-label">Address of Destination</td><td class="colon">:</td><td class="field-value">{{ $destination ?: $purchaser_address }}</td></tr>
                    <tr><td class="field-label">Nature and Number of Vehicle</td><td class="colon">:</td><td class="field-value">{{ $transaction->shipping_details ?: 'Transport' }}</td></tr>
                </table>
            </td>
            <td class="party-right">
                <table class="party">
                    <tr><td class="field-label">Mushak Invoice No.</td><td class="colon">:</td><td class="field-value">{{ $mushak_invoice_no }}</td></tr>
                    <tr><td class="field-label">Date of Issue</td><td class="colon">:</td><td class="field-value">{{ $issuedAt->format('n/j/Y') }}</td></tr>
                    <tr><td class="field-label">Time of Issue</td><td class="colon">:</td><td class="field-value">{{ $issuedAt->format('g.iA') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items">
        <colgroup>
            <col style="width: 4%"><col style="width: 20%"><col style="width: 6.5%"><col style="width: 7.5%">
            <col style="width: 8.5%"><col style="width: 11.5%"><col style="width: 8.5%"><col style="width: 11%">
            <col style="width: 7.5%"><col style="width: 10%"><col style="width: 14.5%">
        </colgroup>
        <thead>
            <tr>
                <th>SL<br>No.</th>
                <th>Description of Goods /<br>Services (including Brand<br>name if applicable)</th>
                <th>Unit of<br>Supply</th>
                <th>Quantity</th>
                <th>Unit price*</th>
                <th>Total Value</th>
                <th>Rate of<br>Supplementary Duty</th>
                <th>Supplementary<br>Duty</th>
                <th>VAT Rate</th>
                <th>VAT</th>
                <th>Value including SP<br>and VAT</th>
            </tr>
            <tr>
                @for ($column = 1; $column <= 11; $column++)
                    <th>{{ $column }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse ($lines as $line)
                <tr>
                    <td class="centered">{{ $loop->iteration }}</td>
                    <td class="description">{!! nl2br(e($line['description'])) !!}</td>
                    <td class="centered">{{ $line['unit'] }}</td>
                    <td class="centered">{{ $number($line['quantity']) }}</td>
                    <td class="number">{{ $number($line['unit_price']) }}</td>
                    <td class="number">{{ $money($line['total_value']) }}</td>
                    <td class="centered">-</td>
                    <td class="centered">-</td>
                    <td class="number">{{ $line['vat_rate'] ? $percent($line['vat_rate']).'%' : '-' }}</td>
                    <td class="number">{{ $line['vat_amount'] ? $money($line['vat_amount']) : '-' }}</td>
                    <td class="number">{{ $money($line['total_including_tax']) }}</td>
                </tr>
            @empty
            @endforelse
            @for ($row = $lines->count(); $row < 10; $row++)
                <tr class="blank-row">
                    @for ($column = 1; $column <= 11; $column++)<td></td>@endfor
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"></td>
                <td class="number total-value">{{ $money($totalValue) }}</td>
                <td></td>
                <td class="centered">-</td>
                <td class="number">{{ number_format($totalVatFraction, 2, '.', '') }}</td>
                <td class="number total-value">{{ $money($totalVat) }}</td>
                <td class="number total-value">{{ $money($lines->sum('total_including_tax')) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="authorisation">
        <tr><td class="label">Name of Authorised Person</td><td class="colon">:</td><td class="value">{{ $authorised_person }}</td></tr>
        <tr><td class="label">Designation</td><td class="colon">:</td><td class="value">{{ $designation }}</td></tr>
        <tr class="signature-row"><td class="label">Signature</td><td class="colon">:</td><td class="value"></td></tr>
        <tr class="seal-row"><td class="label">Seal</td><td class="colon">:</td><td class="value"></td></tr>
    </table>

    <p class="notes">* Value exclusive all taxes i.e. SD&nbsp; and VAT</p>
</body>
</html>
