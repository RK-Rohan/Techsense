@php
    $for_print = $for_print ?? false;
    $is_refund = $expense->type == 'expense_refund';
    $business = $expense->business;
    $location = $expense->location;
    $expense_for_name = !empty($expense->transaction_for) ? $expense->transaction_for->user_full_name : '';
    $added_by_name = !empty($expense->sales_person) ? $expense->sales_person->user_full_name : '';
@endphp
<style>
    .expense-voucher { color: #111827; font-size: 13px; }
    .expense-voucher .ev-head { text-align: center; margin-bottom: 14px; }
    .expense-voucher .ev-head img { max-height: 60px; margin-bottom: 6px; }
    .expense-voucher .ev-head h3 { margin: 0 0 2px; font-weight: 700; }
    .expense-voucher .ev-head .ev-address { color: #4b5563; font-size: 12px; }
    .expense-voucher .ev-title { display: inline-block; margin-top: 10px; padding: 4px 18px; border: 1px solid #111827; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
    .expense-voucher table.ev-info { width: 100%; margin-bottom: 14px; }
    .expense-voucher table.ev-info td { padding: 3px 6px; vertical-align: top; }
    .expense-voucher table.ev-info td.ev-label { width: 18%; color: #4b5563; font-weight: 600; white-space: nowrap; }
    .expense-voucher table.ev-grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .expense-voucher table.ev-grid th, .expense-voucher table.ev-grid td { border: 1px solid #d1d5db; padding: 6px 8px; }
    .expense-voucher table.ev-grid th { background: #f3f4f6; }
    .expense-voucher .text-right { text-align: right; }
    .expense-voucher .ev-section { font-weight: 700; margin: 6px 0; }
    .expense-voucher .ev-note { border: 1px solid #d1d5db; padding: 8px; min-height: 36px; margin-bottom: 14px; white-space: pre-line; }
    .expense-voucher .ev-sign { width: 100%; margin-top: 60px; }
    .expense-voucher .ev-sign td { width: 33%; text-align: center; padding: 0 12px; }
    .expense-voucher .ev-sign span { display: block; border-top: 1px solid #111827; padding-top: 4px; }
    .expense-voucher .ev-foot { margin-top: 18px; font-size: 11px; color: #6b7280; text-align: center; }
</style>

<div class="expense-voucher">
    <div class="ev-head">
        @if($for_print && !empty($business->logo) && file_exists(public_path('uploads/business_logos/' . $business->logo)))
            <img src="{{ asset('uploads/business_logos/' . $business->logo) }}" alt="Logo"><br>
        @endif
        <h3>{{ $business->name ?? '' }}</h3>
        @if(!empty($location))
            <div class="ev-address">
                @if(!empty($location->location_address)){!! strip_tags($location->location_address, '<br>') !!}@endif
                @if(!empty($location->mobile)) <br>{{ $location->mobile }} @endif
            </div>
        @endif
        <div class="ev-title">{{ $is_refund ? 'Expense Refund Voucher' : 'Expense Voucher' }}</div>
    </div>

    <table class="ev-info">
        <tr>
            <td class="ev-label">@lang('purchase.ref_no'):</td>
            <td>{{ $expense->ref_no }}</td>
            <td class="ev-label">@lang('messages.date'):</td>
            <td>{{ @format_datetime($expense->transaction_date) }}</td>
        </tr>
        <tr>
            <td class="ev-label">@lang('expense.expense_category'):</td>
            <td>{{ $category_name ?: '-' }}</td>
            <td class="ev-label">Sub category:</td>
            <td>{{ $sub_category_names ?: '-' }}</td>
        </tr>
        <tr>
            <td class="ev-label">@lang('expense.expense_for'):</td>
            <td>{{ $expense_for_name ?: '-' }}</td>
            <td class="ev-label">Contact:</td>
            <td>{{ $expense->contact->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="ev-label">Payment status:</td>
            <td>{{ __('lang_v1.' . $expense->payment_status) }}</td>
            <td class="ev-label">@lang('lang_v1.added_by'):</td>
            <td>{{ $added_by_name ?: '-' }}</td>
        </tr>
        @if(!empty($expense->recur_parent_id) && !empty($expense->recurring_parent))
            <tr>
                <td class="ev-label">@lang('lang_v1.recurred_from'):</td>
                <td colspan="3">{{ $expense->recurring_parent->ref_no }}</td>
            </tr>
        @endif
    </table>

    <table class="ev-grid">
        <thead>
            <tr>
                <th style="width: 50px;">SL</th>
                <th>Sub Category</th>
                <th>Description / Note</th>
                <th class="text-right" style="width: 160px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['name'] ?: '-' }}</td>
                    <td>{{ $item['note'] }}</td>
                    <td class="text-right">@format_currency($item['amount'])</td>
                </tr>
            @empty
                <tr>
                    <td>1</td>
                    <td>{{ $sub_category_names ?: ($category_name ?: '-') }}</td>
                    <td>{{ $expense->additional_notes }}</td>
                    <td class="text-right">@format_currency($expense->total_before_tax)</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            @if(!empty($expense->tax_amount) && $expense->tax_amount != 0)
                <tr>
                    <th colspan="3" class="text-right">@lang('sale.tax') @if(!empty($expense->tax)) ({{ $expense->tax->name }}) @endif</th>
                    <td class="text-right">@format_currency($expense->tax_amount)</td>
                </tr>
            @endif
            <tr>
                <th colspan="3" class="text-right">@lang('sale.total_amount')</th>
                <th class="text-right">@if($is_refund) - @endif @format_currency($expense->final_total)</th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Total paid</th>
                <td class="text-right">@format_currency($amount_paid)</td>
            </tr>
            <tr>
                <th colspan="3" class="text-right">@lang('purchase.payment_due')</th>
                <td class="text-right">@format_currency($payment_due)</td>
            </tr>
        </tfoot>
    </table>

    @if($expense->payment_lines->count())
        <div class="ev-section">Payments</div>
        <table class="ev-grid">
            <thead>
                <tr>
                    <th>@lang('messages.date')</th>
                    <th>@lang('purchase.ref_no')</th>
                    <th>Method</th>
                    <th>Account</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expense->payment_lines as $payment)
                    <tr>
                        <td>{{ @format_datetime($payment->paid_on) }}</td>
                        <td>{{ $payment->payment_ref_no }}</td>
                        <td>{{ $payment_types[$payment->method] ?? ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                        <td>{{ $payment->payment_account->name ?? '-' }}</td>
                        <td class="text-right">@format_currency($payment->amount)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="ev-section">@lang('expense.expense_note')</div>
    <div class="ev-note">{{ $expense->additional_notes ?: '-' }}</div>

    @if($for_print)
        <table class="ev-sign">
            <tr>
                <td><span>Prepared by</span></td>
                <td><span>Approved by</span></td>
                <td><span>Received by</span></td>
            </tr>
        </table>
        <div class="ev-foot">Printed on {{ \Carbon\Carbon::now()->format('d-m-Y h:i A') }}</div>
    @endif
</div>
