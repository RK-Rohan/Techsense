<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page { margin: 8mm 7mm; }
        * { box-sizing: border-box; }
        body { color: #000; font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.2; }
        table { border-collapse: collapse; width: 100%; }
        .header td { vertical-align: top; }
        .seal-cell { width: 10%; text-align: left; }
        .seal { height: 40px; width: 40px; }
        .heading { text-align: center; width: 80%; }
        .heading h1, .heading h2, .heading p { margin: 0; }
        .heading h1 { font-size: 11px; }
        .heading h2 { font-size: 10px; margin-top: 1px; }
        .heading .sub { font-size: 9px; font-weight: bold; margin-top: 2px; }
        .heading p { font-size: 8px; margin-top: 1px; }
        .form-number { border: 1px solid #000; color: #009a36; font-size: 9px; font-weight: bold;
            padding: 2px 4px; text-align: center; width: 10%; }
        .registered { margin: 4px 0 6px; }
        .registered td { font-size: 8.5px; padding: 1px 2px; vertical-align: top; }
        .registered .label { font-weight: bold; white-space: nowrap; width: 15%; }
        .registered .colon { width: 2%; }
        .period { font-size: 8.5px; margin: 0 0 4px; text-align: right; }
        .book-title { font-size: 9px; font-weight: bold; margin: 2px 0; text-align: center; }
        .items { table-layout: fixed; page-break-inside: auto; }
        .items thead { display: table-header-group; }
        .items tr { page-break-inside: avoid; }
        .items th, .items td { border: 1px solid #000; padding: 1.5px 1px; vertical-align: middle;
            word-wrap: break-word; }
        .items th { font-size: 6.5px; font-weight: normal; text-align: center; }
        .items td { font-size: 6.5px; }
        .items .number { text-align: right; }
        .items .centered { text-align: center; }
        .items tfoot td { font-weight: bold; }
        .notes { font-size: 7px; margin: 8px 0 0; }
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

    <table class="header">
        <tr>
            <td class="seal-cell">
                @if ($government_seal)
                    <img class="seal" src="data:image/png;base64,{{ $government_seal }}" alt="Government of Bangladesh seal">
                @endif
            </td>
            <td class="heading">
                <h1>Government of the People's Republic of Bangladesh</h1>
                <h2>National Board of Revenue</h2>
                <p class="sub">Sales Account Book</p>
                <p>(Applicable to a registered or enlisted person engaged in the supply of goods or services)</p>
                <p>[See Rule 40, Sub-rule (1), Clause (b) and Rule 41, Clause (a)]</p>
            </td>
            <td class="form-number">Mushak - 6.2</td>
        </tr>
    </table>

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
            <td class="label">Business Identification Number (BIN)</td>
            <td class="colon">:</td>
            <td>{{ $seller_bin }}</td>
        </tr>
    </table>

    <p class="period">Period: {{ $date($start_date) }} to {{ $date($end_date) }}</p>

    <p class="book-title">Sale of Goods/Services</p>

    <table class="items">
        <colgroup>
            <col style="width: 4.4%">
            <col style="width: 3.8%"><col style="width: 4.6%">
            <col style="width: 3.8%"><col style="width: 4.6%">
            <col style="width: 3.8%"><col style="width: 4.6%">
            <col style="width: 8%"><col style="width: 9%"><col style="width: 5.6%">
            <col style="width: 4%"><col style="width: 4.2%">
            <col style="width: 8.8%"><col style="width: 3.6%"><col style="width: 4.8%">
            <col style="width: 4%"><col style="width: 4.4%">
            <col style="width: 3.8%"><col style="width: 4.6%">
            <col style="width: 3.2%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">Date</th>
                <th colspan="2">Opening Balance of<br>Produced</th>
                <th colspan="2">Production</th>
                <th colspan="2">Total Produced<br>Goods/Services</th>
                <th colspan="3">Buyer/Recipient</th>
                <th colspan="2">Details of Challan</th>
                <th colspan="5">Description of Sold/Supplied Goods</th>
                <th colspan="2">Closing Balance of<br>Goods</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Quantity<br>(Unit)</th>
                <th>Value<br>(Excluding<br>All Taxes)</th>
                <th>Quantity<br>(Unit)</th>
                <th>Value<br>(Excluding<br>All Taxes)</th>
                <th>Quantity<br>(Unit)</th>
                <th>Value<br>(Excluding<br>All Taxes)</th>
                <th>Name</th>
                <th>Address</th>
                <th>Registration /<br>Enlistment /<br>National ID No.</th>
                <th>Number</th>
                <th>Date</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Taxable<br>Value</th>
                <th>Supple-<br>mentary<br>Duty<br>(if any)</th>
                <th>VAT</th>
                <th>Quantity<br>(Unit)</th>
                <th>Value<br>(Excluding<br>All Taxes)</th>
            </tr>
            <tr>
                {{-- The NBR form numbers this book from (2) through (21). --}}
                @for ($column = 2; $column <= 21; $column++)
                    <th>
                        ({{ $column }})
                        @if ($column == 7)
                            <br>=(3+5)
                        @elseif ($column == 8)
                            <br>=(4+6)
                        @elseif ($column == 19)
                            <br>=(7-11)
                        @elseif ($column == 20)
                            <br>=(8-16)
                        @endif
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="centered">{{ $date($row['date']) }}</td>
                    <td class="centered">{{ $number($row['opening_qty']) }} {{ $row['unit'] }}</td>
                    <td class="number">{{ $money($row['opening_value']) }}</td>
                    <td class="centered">{{ $row['produced_qty'] ? $number($row['produced_qty']) : '-' }}</td>
                    <td class="number">{{ $row['produced_value'] ? $money($row['produced_value']) : '-' }}</td>
                    <td class="centered">{{ $number($row['total_qty']) }} {{ $row['unit'] }}</td>
                    <td class="number">{{ $money($row['total_value']) }}</td>
                    <td>{{ $row['buyer_name'] }}</td>
                    <td>{{ $row['buyer_address'] }}</td>
                    <td class="centered">{{ $row['buyer_bin'] }}</td>
                    <td class="centered">{{ $row['challan_no'] }}</td>
                    <td class="centered">{{ $date($row['challan_date']) }}</td>
                    <td>{!! nl2br(e($row['description'])) !!}</td>
                    <td class="centered">{{ $number($row['quantity']) }}</td>
                    <td class="number">{{ $money($row['taxable_value']) }}</td>
                    <td class="number">{{ $row['sd_amount'] ? $money($row['sd_amount']) : '-' }}</td>
                    <td class="number">{{ $row['vat'] ? $money($row['vat']) : '-' }}</td>
                    <td class="centered">{{ $number($row['closing_qty']) }} {{ $row['unit'] }}</td>
                    <td class="number">{{ $money($row['closing_value']) }}</td>
                    <td>{{ $row['remarks'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="20" class="centered">No records found for this period.</td>
                </tr>
            @endforelse
        </tbody>
        @if (count($rows))
            <tfoot>
                <tr>
                    <td colspan="12" class="number">Total</td>
                    <td class="centered">{{ $number($rows->sum('quantity')) }}</td>
                    <td class="number">{{ $money($rows->sum('taxable_value')) }}</td>
                    <td class="number">{{ $money($rows->sum('sd_amount')) }}</td>
                    <td class="number">{{ $money($rows->sum('vat')) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="notes">* Where goods are sold to an unregistered person, the person's full name, complete address, and National Identification Number (NID) must be properly and mandatorily mentioned in the respective Columns (9), (10), and (11).</p>
</body>
</html>
