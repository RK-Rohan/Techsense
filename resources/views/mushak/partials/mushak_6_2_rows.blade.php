{{-- On-screen Mushak 6.2 rows. Column numbers follow the NBR form. --}}
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
            <th rowspan="2" class="text-center">@lang('messages.date')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.opening_balance_of_produced')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.production')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.total_produced_goods_services')</th>
            <th colspan="3" class="text-center">@lang('lang_v1.buyer_recipient')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.details_of_challan')</th>
            <th colspan="5" class="text-center">@lang('lang_v1.description_of_sold_supplied_goods')</th>
            <th colspan="2" class="text-center">@lang('lang_v1.closing_balance_of_goods')</th>
            <th rowspan="2" class="text-center">@lang('lang_v1.mushak_remarks')</th>
        </tr>
        <tr>
            <th class="text-center">@lang('lang_v1.quantity_unit')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
            <th class="text-center">@lang('lang_v1.quantity_unit')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
            <th class="text-center">@lang('lang_v1.quantity_unit')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
            <th class="text-center">@lang('lang_v1.name')</th>
            <th class="text-center">@lang('business.address')</th>
            <th class="text-center">@lang('lang_v1.registration_enlistment_nid')</th>
            <th class="text-center">@lang('lang_v1.challan_number')</th>
            <th class="text-center">@lang('messages.date')</th>
            <th class="text-center">@lang('lang_v1.description')</th>
            <th class="text-center">@lang('lang_v1.quantity')</th>
            <th class="text-center">@lang('lang_v1.taxable_value')</th>
            <th class="text-center">@lang('lang_v1.supplementary_duty')</th>
            <th class="text-center">@lang('lang_v1.mushak_vat')</th>
            <th class="text-center">@lang('lang_v1.quantity_unit')</th>
            <th class="text-center">@lang('lang_v1.value_excluding_all_taxes')</th>
        </tr>
        <tr>
            {{-- The NBR form numbers this book from (2) through (21). --}}
            @for ($column = 2; $column <= 21; $column++)
                <th class="text-center">
                    ({{ $column }})
                    @if ($column == 7)
                        <br><small>=(3+5)</small>
                    @elseif ($column == 8)
                        <br><small>=(4+6)</small>
                    @elseif ($column == 19)
                        <br><small>=(7-11)</small>
                    @elseif ($column == 20)
                        <br><small>=(8-16)</small>
                    @endif
                </th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="text-center">{{ @format_date($row['date']) }}</td>
                <td class="text-center">{{ $number($row['opening_qty']) }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $money($row['opening_value']) }}</td>
                <td class="text-center">{{ $row['produced_qty'] ? $number($row['produced_qty']) : '-' }}</td>
                <td class="text-right">{{ $row['produced_value'] ? $money($row['produced_value']) : '-' }}</td>
                <td class="text-center">{{ $number($row['total_qty']) }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $money($row['total_value']) }}</td>
                <td>{{ $row['buyer_name'] }}</td>
                <td>{{ $row['buyer_address'] }}</td>
                <td class="text-center">{{ $row['buyer_bin'] }}</td>
                <td class="text-center">{{ $row['challan_no'] }}</td>
                <td class="text-center">{{ @format_date($row['challan_date']) }}</td>
                <td>{!! nl2br(e($row['description'])) !!}</td>
                <td class="text-center">{{ $number($row['quantity']) }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $money($row['taxable_value']) }}</td>
                <td class="text-right">{{ $row['sd_amount'] ? $money($row['sd_amount']) : '-' }}</td>
                <td class="text-right">{{ $row['vat'] ? $money($row['vat']) : '-' }}</td>
                <td class="text-center">{{ $number($row['closing_qty']) }} {{ $row['unit'] }}</td>
                <td class="text-right">{{ $money($row['closing_value']) }}</td>
                <td>{{ $row['remarks'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="20" class="text-center">@lang('purchase.no_records_found')</td>
            </tr>
        @endforelse
    </tbody>
    @if (count($rows))
        <tfoot>
            <tr class="bg-gray">
                <td colspan="12" class="text-right"><strong>@lang('sale.total')</strong></td>
                <td class="text-center"><strong>{{ $number($rows->sum('quantity')) }}</strong></td>
                <td class="text-right"><strong>{{ $money($rows->sum('taxable_value')) }}</strong></td>
                <td class="text-right"><strong>{{ $money($rows->sum('sd_amount')) }}</strong></td>
                <td class="text-right"><strong>{{ $money($rows->sum('vat')) }}</strong></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    @endif
</table>
