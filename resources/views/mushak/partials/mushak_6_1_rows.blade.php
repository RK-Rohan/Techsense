{{-- On-screen Mushak 6.1 rows. Column numbers follow the NBR form. --}}
@php
    $money = function ($value) {
        return number_format((float) $value, 2, '.', ',');
    };
    $number = function ($value) {
        return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
    };
@endphp

<table class="table table-bordered table-striped table-condensed" style="font-size: 11px;">
    <thead>
        <tr>
            <th rowspan="2" class="text-center">@lang('lang_v1.serial_no')</th>
            <th rowspan="2" class="text-center">@lang('messages.date')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.opening_balance_of_stock_inputs')</th>
            <th rowspan="2" class="text-center">@lang('lang_v1.challan_bill_of_entry_no')</th>
            <th rowspan="2" class="text-center">@lang('messages.date')</th>
            <th colspan="3" class="text-center">@lang('lang_v1.seller_supplier')</th>
            <th colspan="5" class="text-center">@lang('lang_v1.purchased_inputs')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.total_quantity_of_inputs')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.use_of_inputs_in_production')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.closing_balance_of_inputs')</th>
            <th rowspan="2" class="text-center">@lang('lang_v1.mushak_remarks')</th>
        </tr>
        <tr>
            <th class="text-center">@lang('lang_v1.quantity_unit')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
            <th class="text-center">@lang('lang_v1.name')</th>
            <th class="text-center">@lang('business.address')</th>
            <th class="text-center">@lang('lang_v1.registration_enlistment_nid')</th>
            <th class="text-center">@lang('lang_v1.description')</th>
            <th class="text-center">@lang('lang_v1.quantity')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
            <th class="text-center">@lang('lang_v1.supplementary_duty')</th>
            <th class="text-center">@lang('lang_v1.mushak_vat')</th>
            <th class="text-center">@lang('lang_v1.quantity_unit')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
            <th class="text-center">@lang('lang_v1.quantity_unit')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
            <th class="text-center">@lang('lang_v1.quantity_unit')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
        </tr>
        <tr>
            @for ($column = 1; $column <= 21; $column++)
                <th class="text-center">
                    ({{ $column }})
                    @if ($column == 15)
                        <br><small>=(3+11)</small>
                    @elseif ($column == 16)
                        <br><small>=(4+12)</small>
                    @endif
                </th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="text-center">{{ $row['serial'] }}</td>
                <td class="text-center">{{ @format_date($row['date']) }}</td>
                <td class="text-center">{{ $number($row['opening_qty']) }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $money($row['opening_value']) }}</td>
                <td class="text-center">{{ $row['challan_no'] }}</td>
                <td class="text-center">{{ @format_date($row['challan_date']) }}</td>
                <td>{{ $row['supplier_name'] }}</td>
                <td>{{ $row['supplier_address'] }}</td>
                <td class="text-center">{{ $row['supplier_bin'] }}</td>
                <td>{!! nl2br(e($row['description'])) !!}</td>
                <td class="text-center">{{ $number($row['quantity']) }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $money($row['value']) }}</td>
                <td class="text-right">{{ $row['sd_amount'] ? $money($row['sd_amount']) : '-' }}</td>
                <td class="text-right">{{ $row['vat'] ? $money($row['vat']) : '-' }}</td>
                <td class="text-center">{{ $number($row['total_qty']) }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $money($row['total_value']) }}</td>
                <td class="text-center">{{ $row['used_qty'] ? $number($row['used_qty']) : '-' }}</td>
                <td class="text-right">{{ $row['used_value'] ? $money($row['used_value']) : '-' }}</td>
                <td class="text-center">{{ $number($row['closing_qty']) }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $money($row['closing_value']) }}</td>
                <td>{{ $row['remarks'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="21" class="text-center">@lang('purchase.no_records_found')</td>
            </tr>
        @endforelse
    </tbody>
    @if (count($rows))
        <tfoot>
            <tr class="bg-gray">
                <td colspan="10" class="text-right"><strong>@lang('sale.total')</strong></td>
                <td class="text-center"><strong>{{ $number($rows->sum('quantity')) }}</strong></td>
                <td class="text-right"><strong>{{ $money($rows->sum('value')) }}</strong></td>
                <td class="text-right"><strong>{{ $money($rows->sum('sd_amount')) }}</strong></td>
                <td class="text-right"><strong>{{ $money($rows->sum('vat')) }}</strong></td>
                <td colspan="7"></td>
            </tr>
        </tfoot>
    @endif
</table>
