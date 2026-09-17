{{--
    Profit or Loss and other Comprehensive Income, with its comparative column.
--}}
@php
    $cur = $current['profit_loss'];
    $prev = $previous['profit_loss'];
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
        <tr>
            <td>Sales Revenue</td>
            <td class="notes-col">11</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['revenue']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['revenue']])</td>
        </tr>
        <tr>
            <td>Less: Cost of Sold**</td>
            <td class="notes-col">12</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['cost_of_sales']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['cost_of_sales']])</td>
        </tr>
        <tr class="total">
            <td>Gross Profit</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['gross_profit']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['gross_profit']])</td>
        </tr>
        <tr>
            <td>Less: Administrative &amp; Selling Expenses</td>
            <td class="notes-col">13</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['administrative_expenses']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['administrative_expenses']])</td>
        </tr>
        <tr class="total">
            <td>Profit from Operation</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['operating_profit']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['operating_profit']])</td>
        </tr>
        <tr>
            <td>Add: Non-Operating Income</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['non_operating_income']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['non_operating_income']])</td>
        </tr>
        <tr>
            <td>Less: Finance Cost</td>
            <td class="notes-col">14</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['finance_cost']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['finance_cost']])</td>
        </tr>
        <tr class="grand-total">
            <td>Profit/(Loss) before Tax</td>
            <td class="notes-col"></td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['profit_before_tax']])</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $prev['profit_before_tax']])</td>
        </tr>
    </tbody>
</table>

<br>
<p><strong>** Cost of Goods Sold</strong></p>
<p style="color:#2a6099;">Beginning Inventory + Purchases &minus; Ending Inventory</p>
<p>Carriage inward</p>

<table class="statement" style="max-width:520px;">
    <tbody>
        <tr>
            <td class="indent">Opening Stock</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['opening_stock']])</td>
        </tr>
        <tr>
            <td class="indent">Add: Net Purchase</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['net_purchase']])</td>
        </tr>
        <tr>
            <td class="indent">Add: Carriage Inward</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['carriage_inward']])</td>
        </tr>
        <tr>
            <td class="indent">Less: Closing Stock</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['closing_stock']])</td>
        </tr>
        <tr class="total">
            <td>Cost of Goods Sold</td>
            <td class="num">@include('financial_report.partials.amount', ['value' => $cur['cost_of_sales']])</td>
        </tr>
    </tbody>
</table>
