{{--
    Notes to the Accounts: each numbered note shows the total carried to the
    face of the statements and the breakdown that supports it.
--}}
@php
    $cur = $current['notes'];
    $prev = $previous['notes'];
    $cur_pos = $current['position'];
    $prev_pos = $previous['position'];
    $cur_pl = $current['profit_loss'];
    $prev_pl = $previous['profit_loss'];

    // Each note: number, title, the totals on the face of the statements, and
    // the supporting lines from the current and comparative periods.
    $notes = [
        ['01', 'Non-Current Assets', $cur_pos['non_current_assets'], $prev_pos['non_current_assets'], $cur['non_current_assets'], $prev['non_current_assets']],
        ['02', 'Closing Stock', $cur_pos['closing_inventory'], $prev_pos['closing_inventory'], $cur['closing_stock'], $prev['closing_stock']],
        ['03', 'Cash & Cash Equivalent', $cur_pos['cash_and_equivalent'], $prev_pos['cash_and_equivalent'], $cur['cash_and_equivalent'], $prev['cash_and_equivalent']],
        ['04', 'Accounts Receivable', $cur_pos['accounts_receivable'], $prev_pos['accounts_receivable'], $cur['accounts_receivable'], $prev['accounts_receivable']],
        ['05', 'Advance Deposit & Prepayment', $cur_pos['advance_deposit'], $prev_pos['advance_deposit'], $cur['advance_deposit'], $prev['advance_deposit']],
        ['06', 'Paid up Capital', $cur_pos['paid_up_capital'], $prev_pos['paid_up_capital'], $cur['paid_up_capital'], $prev['paid_up_capital']],
        ['07', 'Retained earnings', $cur_pos['retained_earnings'], $prev_pos['retained_earnings'], $cur['retained_earnings'], $prev['retained_earnings']],
        ['08', 'Accounts Payable', $cur_pos['accounts_payable'], $prev_pos['accounts_payable'], $cur['accounts_payable'], $prev['accounts_payable']],
        ['09', 'Loans & Borrowing', $cur_pos['loans_and_borrowing'], $prev_pos['loans_and_borrowing'], $cur['loans_and_borrowing'], $prev['loans_and_borrowing']],
        ['10', 'Expenses payable', $cur_pos['expenses_payable'], $prev_pos['expenses_payable'], $cur['expenses_payable'], $prev['expenses_payable']],
        ['11', 'Revenue', $cur_pl['revenue'], $prev_pl['revenue'], $cur['revenue'], $prev['revenue']],
        ['12', 'Cost of Sales', $cur_pl['cost_of_sales'], $prev_pl['cost_of_sales'], $cur['purchase'], $prev['purchase']],
        ['13', 'Administrative Expenses', $cur_pl['administrative_expenses'], $prev_pl['administrative_expenses'], $cur['administrative_expenses'], $prev['administrative_expenses']],
        ['14', 'Finance Cost', $cur_pl['finance_cost'], $prev_pl['finance_cost'], $cur['finance_cost'], $prev['finance_cost']],
    ];
@endphp
<table class="statement">
    <thead>
        <tr>
            <th class="notes-col">SL</th>
            <th>Particulars</th>
            <th class="num">{{ \Carbon::parse($period['end_date'])->format('d-M-y') }}</th>
            <th class="num">{{ \Carbon::parse($period['prev_end_date'])->format('d-M-y') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($notes as [$sl, $title, $cur_total, $prev_total, $cur_lines, $prev_lines])
            <tr class="section">
                <th class="notes-col">{{ $sl }}</th>
                <th>{{ $title }}</th>
                <th class="num">@include('financial_report.partials.amount', ['value' => $cur_total])</th>
                <th class="num">@include('financial_report.partials.amount', ['value' => $prev_total])</th>
            </tr>
            @php
                // Normalise to plain arrays so collections do not leak their internals.
                $cur_rows = collect($cur_lines)->toArray();
                $prev_rows = collect($prev_lines)->toArray();

                // Show every line appearing in either period so the columns align.
                $line_names = array_unique(array_merge(array_keys($cur_rows), array_keys($prev_rows)));
                sort($line_names);
            @endphp
            @forelse ($line_names as $line_name)
            <tr>
                <td class="notes-col"></td>
                <td class="indent">{{ $line_name }}</td>
                <td class="num">@include('financial_report.partials.amount', ['value' => $cur_rows[$line_name] ?? 0])</td>
                <td class="num">@include('financial_report.partials.amount', ['value' => $prev_rows[$line_name] ?? 0])</td>
            </tr>
            @empty
            <tr>
                <td class="notes-col"></td>
                <td class="indent"><em>No entries recorded for this period</em></td>
                <td class="num">-</td>
                <td class="num">-</td>
            </tr>
            @endforelse
            <tr class="spacer"><td colspan="4"></td></tr>
        @endforeach
    </tbody>
</table>
