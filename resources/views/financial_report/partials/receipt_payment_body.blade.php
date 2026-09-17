{{--
    Receipt & Payment account: money actually received and paid in the period.
--}}
@php
    $cur = $current['receipt_payment'];
    $prev = $previous['receipt_payment'];

    // Plain arrays covering every account named in either period, so an account
    // that only appears in one of them still lines up across the two columns.
    $cur_open = collect($cur['opening_breakdown'])->toArray();
    $prev_open = collect($prev['opening_breakdown'])->toArray();
    $open_names = array_unique(array_merge(array_keys($cur_open), array_keys($prev_open)));
    sort($open_names);

    $cur_close = collect($cur['closing_breakdown'])->toArray();
    $prev_close = collect($prev['closing_breakdown'])->toArray();
    $close_names = array_unique(array_merge(array_keys($cur_close), array_keys($prev_close)));
    sort($close_names);
@endphp
<table class="statement">
    <thead>
        <tr>
            <th>Particulars</th>
            <th class="notes-col">Notes</th>
            <th class="num">{{ \Carbon::parse($period['end_date'])->format('d-M-y') }}</th>
            <th class="num">{{ \Carbon::parse($period['prev_end_date'])->format('d-M-y') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr class="total">
            <td>Opening Balance</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['opening_balance']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['opening_balance']])</td>
        </tr>
        @forelse ($open_names as $name)
        <tr>
            <td class="indent">{{ $name }}</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur_open[$name] ?? 0])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev_open[$name] ?? 0])</td>
        </tr>
        @empty
        <tr>
            <td class="indent">Cash in Hand</td>
            <td class="notes-col"></td>
            <td class="num">-</td>
            <td class="num">-</td>
        </tr>
        @endforelse

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="section"><th colspan="4">RECEIVED</th></tr>
        <tr>
            <td class="indent">Receive from Customers</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['receive_from_customers']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['receive_from_customers']])</td>
        </tr>
        <tr>
            <td class="indent">Receive from Bank or Loan</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['receive_from_others']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['receive_from_others']])</td>
        </tr>
        @if ($cur['unreconciled'] != 0 || $prev['unreconciled'] != 0)
        <tr>
            <td class="indent">Receive from others</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['unreconciled']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['unreconciled']])</td>
        </tr>
        @endif
        <tr class="total">
            <td>Total received</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['total_received']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['total_received']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="section"><th colspan="4">PAYMENT</th></tr>
        <tr>
            <td class="indent">Payment to Suppliers</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['payment_to_suppliers']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['payment_to_suppliers']])</td>
        </tr>
        <tr>
            <td class="indent">Payment as Expenses</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['payment_as_expenses']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['payment_as_expenses']])</td>
        </tr>
        <tr>
            <td class="indent">Other Payments</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['payment_others']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['payment_others']])</td>
        </tr>
        <tr class="total">
            <td>Total payment</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['total_payment']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['total_payment']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="grand-total">
            <td>Closing Balance</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['closing_balance']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['closing_balance']])</td>
        </tr>
        @foreach ($close_names as $name)
        <tr>
            <td class="indent">{{ $name }}</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur_close[$name] ?? 0])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev_close[$name] ?? 0])</td>
        </tr>
        @endforeach
    </tbody>
</table>
