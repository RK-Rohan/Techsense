<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page { margin: 8mm; }
        * { box-sizing: border-box; }
        body { color: #000; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.25; }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: 15px; }
        h2 { font-size: 13px; margin-top: 2px; }
        h3 { font-size: 10px; font-weight: normal; margin-top: 2px; }
        .center { text-align: center; }
        .header { margin-bottom: 7px; position: relative; }
        .form-number { font-size: 11px; font-weight: bold; position: absolute; right: 0; top: 2px; }
        table { border-collapse: collapse; width: 100%; }
        .details td { padding: 2px 4px; vertical-align: top; }
        .details .label { font-weight: bold; width: 23%; }
        .details .value { border-bottom: 1px dotted #555; width: 27%; }
        .items { margin-top: 7px; table-layout: fixed; }
        .items th, .items td { border: 1px solid #000; padding: 3px 2px; vertical-align: middle; }
        .items th { font-size: 7.5px; text-align: center; }
        .items td { font-size: 8px; }
        .items .number { text-align: right; white-space: nowrap; }
        .items .centered { text-align: center; }
        .items tfoot td { font-weight: bold; }
        .notes { font-size: 8px; margin-top: 4px; }
        .signature { margin-top: 9px; }
        .signature td { padding: 2px 4px; vertical-align: top; }
        .signature .label { font-weight: bold; width: 16%; }
        .signature .line { border-bottom: 1px dotted #555; width: 34%; }
        .signature-space { height: 18px; }
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
            ?: optional($transaction->contact)->name;
    @endphp

    <div class="header center">
        <div class="form-number">Mushak - 6.3</div>
        <h1>Government of the People's Republic of Bangladesh</h1>
        <h2>National Board of Revenue</h2>
        <h2>VAT Invoice</h2>
        <h3>As per Para (Ga) and Para (Cha) of Sub-rule (1) of Rule 40</h3>
    </div>

    <table class="details">
        <tr>
            <td class="label">Name of registered person</td><td class="value">{{ optional($transaction->business)->name }}</td>
            <td class="label">Name of Purchaser</td><td class="value">{{ $purchaserName }}</td>
        </tr>
        <tr>
            <td class="label">BIN of the registered person</td><td class="value">{{ optional($transaction->business)->tax_number_1 }}</td>
            <td class="label">Mushak Invoice No.</td><td class="value">{{ $transaction->invoice_no }}</td>
        </tr>
        <tr>
            <td class="label">Invoice issuance address</td><td class="value">{{ $seller_address }}</td>
            <td class="label">Purchaser's BIN</td><td class="value">{{ optional($transaction->contact)->tax_number }}</td>
        </tr>
        <tr>
            <td class="label">Address of Purchaser</td><td class="value">{{ $purchaser_address }}</td>
            <td class="label">Date of Issue</td><td class="value">{{ $issuedAt->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Address of Destination</td><td class="value">{{ $destination ?: $purchaser_address }}</td>
            <td class="label">Time of Issue</td><td class="value">{{ $issuedAt->format('h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">Nature and Number of Vehicle</td><td class="value">{{ $transaction->shipping_details ?: '—' }}</td>
            <td></td><td></td>
        </tr>
    </table>

    <table class="items">
        <colgroup>
            <col style="width: 3%"><col style="width: 20%"><col style="width: 6%"><col style="width: 6%">
            <col style="width: 9%"><col style="width: 9%"><col style="width: 7%"><col style="width: 9%">
            <col style="width: 7%"><col style="width: 9%"><col style="width: 15%">
        </colgroup>
        <thead>
            <tr>
                <th>SL No.</th>
                <th>Description of Goods / Services (including Brand name if applicable)</th>
                <th>Unit of Supply</th>
                <th>Quantity</th>
                <th>Unit price*</th>
                <th>Total Value</th>
                <th>Rate of Supplementary Duty</th>
                <th>Supplementary Duty</th>
                <th>VAT Rate</th>
                <th>VAT</th>
                <th>Value including SD and VAT</th>
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
                    <td>{!! nl2br(e($line['description'])) !!}</td>
                    <td class="centered">{{ $line['unit'] }}</td>
                    <td class="number">{{ $number($line['quantity']) }}</td>
                    <td class="number">{{ $money($line['unit_price']) }}</td>
                    <td class="number">{{ $money($line['total_value']) }}</td>
                    <td class="centered">{{ $line['sd_rate'] ? $percent($line['sd_rate']).'%' : '—' }}</td>
                    <td class="number">{{ $line['sd_amount'] ? $money($line['sd_amount']) : '—' }}</td>
                    <td class="centered">{{ $line['vat_rate'] ? $percent($line['vat_rate']).'%' : '—' }}</td>
                    <td class="number">{{ $line['vat_amount'] ? $money($line['vat_amount']) : '—' }}</td>
                    <td class="number">{{ $money($line['total_including_tax']) }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="centered">No invoice items</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="number">Total</td>
                <td class="number">{{ $money($lines->sum('total_value')) }}</td>
                <td></td>
                <td class="number">{{ $money($lines->sum('sd_amount')) }}</td>
                <td></td>
                <td class="number">{{ $money($lines->sum('vat_amount')) }}</td>
                <td class="number">{{ $money($lines->sum('total_including_tax')) }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="notes">* Value exclusive of all taxes, i.e. Supplementary Duty and VAT.</p>

    <table class="signature">
        <tr><td class="label">Name of Authorised Person</td><td class="line">{{ $authorised_person }}</td><td class="label">Designation</td><td class="line">{{ $designation }}</td></tr>
        <tr class="signature-space"><td></td><td></td><td></td><td></td></tr>
        <tr><td class="label">Signature</td><td class="line"></td><td class="label">Seal</td><td class="line"></td></tr>
    </table>
</body>
</html>
