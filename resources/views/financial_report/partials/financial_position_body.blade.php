{{--
    Statement of Financial Position, current period against its comparative.

    $current  - figures as at the period end
    $previous - figures as at the comparative period end
    $period   - the two period end dates
--}}
@php
    $cur = $current['position'];
    $prev = $previous['position'];
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
        <tr class="section"><th colspan="4"><u>ASSETS</u></th></tr>

        <tr class="section"><th colspan="4">Non-Current Assets</th></tr>
        <tr>
            <td class="indent">Property, plant and equipment</td>
            <td class="notes-col">01</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['non_current_assets']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['non_current_assets']])</td>
        </tr>
        <tr class="total">
            <td>Total Non Current Assets</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['non_current_assets']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['non_current_assets']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="section"><th colspan="4">Current Assets</th></tr>
        <tr>
            <td class="indent">Closing Inventory</td>
            <td class="notes-col">02</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['closing_inventory']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['closing_inventory']])</td>
        </tr>
        <tr>
            <td class="indent">Cash &amp; Cash Equivalent</td>
            <td class="notes-col">03</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['cash_and_equivalent']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['cash_and_equivalent']])</td>
        </tr>
        <tr>
            <td class="indent">Accounts Receivable</td>
            <td class="notes-col">04</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['accounts_receivable']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['accounts_receivable']])</td>
        </tr>
        <tr>
            <td class="indent">Advance Deposit &amp; Prepayment</td>
            <td class="notes-col">05</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['advance_deposit']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['advance_deposit']])</td>
        </tr>
        <tr class="total">
            <td>Total Current Assets</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['total_current_assets']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['total_current_assets']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="grand-total">
            <td>Total Assets</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['total_assets']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['total_assets']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="section"><th colspan="4">EQUITY &amp; LIABILITIES</th></tr>
        <tr class="section"><th colspan="4">Capital and Reserves</th></tr>
        <tr>
            <td class="indent">Paid up capital</td>
            <td class="notes-col">06</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['paid_up_capital']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['paid_up_capital']])</td>
        </tr>
        <tr>
            <td class="indent">Retained earnings</td>
            <td class="notes-col">07</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['retained_earnings']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['retained_earnings']])</td>
        </tr>
        <tr class="total">
            <td>Total Equity</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['total_equity']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['total_equity']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="section"><th colspan="4">Current Liabilities</th></tr>
        <tr>
            <td class="indent">Accounts Payable</td>
            <td class="notes-col">08</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['accounts_payable']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['accounts_payable']])</td>
        </tr>
        <tr>
            <td class="indent">Loan &amp; Borrowing</td>
            <td class="notes-col">09</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['loans_and_borrowing']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['loans_and_borrowing']])</td>
        </tr>
        <tr>
            <td class="indent">Expenses Payable</td>
            <td class="notes-col">10</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['expenses_payable']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['expenses_payable']])</td>
        </tr>
        <tr class="total">
            <td>Total Current Liabilities</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['total_current_liabilities']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['total_current_liabilities']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="grand-total">
            <td>Total Equity and Liabilities</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['total_equity_and_liabilities']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['total_equity_and_liabilities']])</td>
        </tr>
    </tbody>
</table>
