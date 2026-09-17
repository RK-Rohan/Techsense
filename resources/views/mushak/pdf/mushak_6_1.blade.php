<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page { margin: 8mm 7mm; }
        * { box-sizing: border-box; }
        body { color: #000; font-family: Helvetica, sans-serif; font-size: 8px; line-height: 1.2; }
        table { border-collapse: collapse; width: 100%; }
        .document-header { border: 0.6pt solid #000; border-bottom: 0; position: relative; height: 82px; }
        .seal-cell { position: absolute; left: 4px; top: 3px; width: 10%; text-align: left; }
        .seal { height: 40px; width: 40px; }
        .heading { position: absolute; top: 3px; left: 17%; text-align: center; width: 66%; }
        .heading h1, .heading h2, .heading p { margin: 0; }
        .heading h1 { font-family: Times, serif; font-size: 11px; }
        .heading h2 { font-size: 10px; margin-top: 1px; }
        .heading .sub { font-size: 9px; font-weight: bold; margin-top: 2px; }
        .heading p { font-size: 8px; margin-top: 1px; }
        .form-number { position: absolute; right: 12px; top: 10px; border: 1px solid #00b050; color: #009a36; font-size: 9px; font-weight: bold;
            padding: 4px 8px; text-align: center; white-space: nowrap; }
        .registered { position: absolute; top: 42px; left: 3px; width: 49%; margin: 0; }
        .registered td { font-size: 8px; padding: 0.5px 1px; vertical-align: top; }
        .registered .label { font-weight: bold; white-space: nowrap; width: 126px; }
        .registered .colon { width: 6px; }
        .period { position: absolute; bottom: 2px; right: 4px; font-size: 7px; margin: 0; text-align: right; }
        .book-title { border-left: 0.6pt solid #000; border-right: 0.6pt solid #000; font-size: 9px; font-weight: bold; margin: 0; padding: 3px 0; text-align: center; }
        .items { table-layout: fixed; page-break-inside: auto; }
        .items thead { display: table-header-group; }
        .items tr { page-break-inside: avoid; }
        .items th, .items td { border: 0.6pt solid #000; padding: 3px 2px; vertical-align: middle;
            word-wrap: break-word; }
        .items th { font-size: 8px; font-weight: normal; text-align: center; }
        .items td { font-size: 7px; }
        .items .group { font-weight: bold; }
        .items .number { text-align: right; font-size: 6px; white-space: nowrap; }
        .items .centered { text-align: center; }
        .items tfoot td { font-weight: bold; }
        .notes { font-size: 7px; margin: 8px 0 0; }
        .notes p { margin: 1px 0; }
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
        $date = function ($value) {
            return empty($value) ? '' : \Carbon\Carbon::parse($value)->format('d-m-Y');
        };
    @endphp

    <div class="document-header">
        <div class="seal-cell">
            @if ($government_seal)
                <img class="seal" src="data:image/png;base64,{{ $government_seal }}" alt="Government of Bangladesh seal">
            @endif
        </div>
        <div class="heading">
            <h1>Government of the People's Republic of Bangladesh</h1>
            <h2>National Board of Revenue</h2>
            <p class="sub">Purchase Account Book</p>
            <p>(Applicable to a registered or enlisted person engaged in processing of goods or services)</p>
            <p>[See Rule 40(1)(a) and Rule 41(a)]</p>
        </div>
        <div class="form-number">Mushak - 6.1</div>

        <table class="registered">
            <tr>
                <td class="label">Name of the Registered Person</td>
                <td class="colon">:</td>
                <td>{{ optional($business)->name }}</td>
            </tr>
            <tr>
                <td class="label">Address</td>
                <td class="colon">:</td>
                <td>{{ $seller_address }}</td>
            </tr>
            <tr>
                <td class="label">BIN</td>
                <td class="colon">:</td>
                <td>{{ $seller_bin }}</td>
            </tr>
        </table>

        <p class="period">Period: {{ $date($start_date) }} to {{ $date($end_date) }}</p>

    </div>
    <p class="book-title">Purchase of Goods/Services Inputs</p>

    <table class="items">
        @php
            // Dompdf ignores colgroup widths. Set widths on the unmerged number cells.
            $columnWidths = [2.9, 4.1, 3.6, 4.6, 4.8, 4.1, 8.2, 9.8, 6, 7.1, 3.6, 4.6, 3.5, 3.6, 3.6, 4.6, 4.1, 4.6, 3.6, 4.6, 4.4];
        @endphp
        <thead>
            <tr>
                <th rowspan="3">Serial<br>No.</th>
                <th rowspan="3">Date</th>
                <th colspan="2">Opening Balance of<br>Stock Inputs</th>
                <th colspan="14" class="group">Purchased Inputs</th>
                <th colspan="2">Closing Balance of<br>Inputs</th>
                <th rowspan="3">Remarks</th>
            </tr>
            <tr>
                <th rowspan="2">Quantity<br>(Unit)</th>
                <th rowspan="2">Value<br>(Excluding<br>All Taxes)</th>
                <th rowspan="2">Challan/Bill<br>of Entry<br>No.</th>
                <th rowspan="2">Date</th>
                <th colspan="3">Seller/Supplier</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Quantity</th>
                <th rowspan="2">Value<br>(Excluding<br>All Taxes)</th>
                <th rowspan="2">Supple-<br>mentary<br>Duty<br>(if any)</th>
                <th rowspan="2">VAT</th>
                <th colspan="2">Total Quantity of<br>Inputs</th>
                <th colspan="2">Use of Inputs in<br>Production/Processing<br>of Goods</th>
                <th rowspan="2">Quantity<br>(Unit)</th>
                <th rowspan="2">Value<br>(Excluding<br>All Taxes)</th>
            </tr>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Registration /<br>Enlistment /<br>National ID No.</th>
                <th>Quantity<br>(Unit)</th>
                <th>Value<br>(Excluding<br>All Taxes)</th>
                <th>Quantity<br>(Unit)</th>
                <th>Value<br>(Excluding<br>All Taxes)</th>
            </tr>
            <tr>
                @for ($column = 1; $column <= 21; $column++)
                    <th style="width: {{ $columnWidths[$column - 1] }}%">
                        ({{ $column }})
                        @if ($column == 15)
                            <br>=(3+11)
                        @elseif ($column == 16)
                            <br>=(4+12)
                        @endif
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="centered">{{ $row['serial'] }}</td>
                    <td class="centered">{{ $date($row['date']) }}</td>
                    <td class="centered">{{ $number($row['opening_qty']) }} {{ $row['unit'] }}</td>
                    <td class="number">{{ $money($row['opening_value']) }}</td>
                    <td class="centered">{{ $row['challan_no'] }}</td>
                    <td class="centered">{{ $date($row['challan_date']) }}</td>
                    <td>{{ $row['supplier_name'] }}</td>
                    <td>{{ $row['supplier_address'] }}</td>
                    <td class="centered">{{ $row['supplier_bin'] }}</td>
                    <td>{!! nl2br(e($row['description'])) !!}</td>
                    <td class="centered">{{ $number($row['quantity']) }}</td>
                    <td class="number">{{ $money($row['value']) }}</td>
                    <td class="number">{{ $row['sd_amount'] ? $money($row['sd_amount']) : '-' }}</td>
                    <td class="number">{{ $row['vat'] ? $money($row['vat']) : '-' }}</td>
                    <td class="centered">{{ $number($row['total_qty']) }} {{ $row['unit'] }}</td>
                    <td class="number">{{ $money($row['total_value']) }}</td>
                    <td class="centered">{{ $row['used_qty'] ? $number($row['used_qty']) : '-' }}</td>
                    <td class="number">{{ $row['used_value'] ? $money($row['used_value']) : '-' }}</td>
                    <td class="centered">{{ $number($row['closing_qty']) }} {{ $row['unit'] }}</td>
                    <td class="number">{{ $money($row['closing_value']) }}</td>
                    <td>{{ $row['remarks'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="21" class="centered">No records found for this period.</td>
                </tr>
            @endforelse
        </tbody>
        @if (count($rows))
            <tfoot>
                <tr>
                    <td colspan="10" class="number">Total</td>
                    <td class="centered">{{ $number($rows->sum('quantity')) }}</td>
                    <td class="number">{{ $money($rows->sum('value')) }}</td>
                    <td class="number">{{ $money($rows->sum('sd_amount')) }}</td>
                    <td class="number">{{ $money($rows->sum('vat')) }}</td>
                    <td colspan="7"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="notes">
        <p><strong>Special Note:</strong></p>
        <p>1. Information of all types of purchases related to economic activities shall be included in this form.</p>
        <p>2. Where goods are purchased from an unregistered person, the full name, address and National ID number of that person shall be properly and mandatorily mentioned in the relevant columns [(7), (8) and (9)].</p>
        <p>3. A copy of the Bill of Entry or Challan shall be preserved as supporting documentary evidence for the purchase of inputs.</p>
    </div>
</body>
</html>
