{{--
    Cash Flow Statement by the direct method.
--}}
@php
    $cur = $current['cash_flow'];
    $prev = $previous['cash_flow'];
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
        <tr class="section"><th colspan="4">Cash flows from Operating Activities</th></tr>
        <tr>
            <td class="indent">Cash receive from Customers</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['cash_from_customers']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['cash_from_customers']])</td>
        </tr>
        <tr>
            <td class="indent">Cash Paid to Suppliers &amp; Others</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $cur['cash_to_suppliers']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $prev['cash_to_suppliers']])</td>
        </tr>
        <tr>
            <td class="indent">Interest Paid</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $cur['interest_paid']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $prev['interest_paid']])</td>
        </tr>
        <tr>
            <td class="indent">Income Tax Paid</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $cur['income_tax_paid']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $prev['income_tax_paid']])</td>
        </tr>
        <tr class="total">
            <td>Net Cash Provided by Operating Activities</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['net_operating']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['net_operating']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="section"><th colspan="4">Cash flows from Investing Activities</th></tr>
        <tr>
            <td class="indent">Purchase of Fixed Assets</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $cur['purchase_of_fixed_assets']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $prev['purchase_of_fixed_assets']])</td>
        </tr>
        <tr>
            <td class="indent">Sales on Fixed Assets</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['sale_of_fixed_assets']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['sale_of_fixed_assets']])</td>
        </tr>
        <tr class="total">
            <td>Net Cash Used in Investing Activities</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['net_investing']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['net_investing']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="section"><th colspan="4">Cash flows from Financing Activities</th></tr>
        <tr>
            <td class="indent">Loan Received</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['loan_received']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['loan_received']])</td>
        </tr>
        <tr>
            <td class="indent">Loan Repayment</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $cur['loan_repayment']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => -1 * $prev['loan_repayment']])</td>
        </tr>
        <tr class="total">
            <td>Net Cash Provided in Financing Activities</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['net_financing']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['net_financing']])</td>
        </tr>

        <tr class="spacer"><td colspan="4"></td></tr>
        <tr class="total">
            <td>Net Increase / (Decrease) in Cash</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['net_increase']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['net_increase']])</td>
        </tr>
        <tr>
            <td>Opening Cash Balance</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['opening_cash']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['opening_cash']])</td>
        </tr>
        <tr class="grand-total">
            <td>Ending Cash Balance</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['ending_cash']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['ending_cash']])</td>
        </tr>
    </tbody>
</table>
