@php
    $custom_labels = json_decode(session('business.custom_labels'), true);
    $custom_field_1_label = $custom_labels['purchase']['custom_field_1'] ?? __('lang_v1.custom_field', ['number' => 1]);
    $custom_field_2_label = $custom_labels['purchase']['custom_field_2'] ?? __('lang_v1.custom_field', ['number' => 2]);
@endphp
<table class="table table-bordered table-striped ajax_view" id="purchase_table" style="width: 100%;">
    <thead>
        <tr>
            <th>@lang('messages.action')</th>
            <th>@lang('messages.date')</th>
            <th>Supplier PO</th>
            <th>{{ $custom_field_1_label }}</th>
            <th>{{ $custom_field_2_label }}</th>
            <th>@lang('purchase.supplier')</th>
            <th>@lang('purchase.purchase_status')</th>
            <th>@lang('purchase.payment_status')</th>
            <th>@lang('purchase.grand_total')</th>
            <th>@lang('purchase.payment_due') &nbsp;&nbsp;<i class="fa fa-info-circle text-info no-print" data-toggle="tooltip" data-placement="bottom" data-html="true" data-original-title="{{ __('messages.purchase_due_tooltip')}}" aria-hidden="true"></i></th>
            <th>@lang('lang_v1.added_by')</th>
        </tr>
    </thead>
    <tfoot>
        <tr class="bg-gray font-17 text-center footer-total">
            <td colspan="6"><strong>@lang('sale.total'):</strong></td>
            <td class="footer_status_count"></td>
            <td class="footer_payment_status_count"></td>
            <td class="footer_purchase_total"></td>
            <td class="text-left"><small>@lang('report.purchase_due') - <span class="footer_total_due"></span><br>
            @lang('lang_v1.purchase_return') - <span class="footer_total_purchase_return_due"></span>
            </small></td>
            <td></td>
        </tr>
    </tfoot>
</table>
