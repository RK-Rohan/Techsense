<div class="table-responsive">
    <table class="table table-bordered table-striped table-text-center" id="profit_by_invoice_table">
        <thead>
            <tr>
                <th>@lang('sale.invoice_no')</th>
                <th>Total Purchase Price</th>
                <th>Total Sales Price</th>
                <th>@lang('lang_v1.gross_profit')</th>

            </tr>
        </thead>
        <tfoot>
            <tr class="bg-gray font-17 footer-total">
                <td><strong>@lang('sale.total'):</strong></td>
                <td class="footer_total_purchase"></td>
                <td class="footer_total_sales"></td>
                <td class="footer_total"></td>
            </tr>
        </tfoot>

    </table>
</div>