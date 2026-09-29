@extends('layouts.app')
@section('title', 'Ledger')

@section('content')
<section class="content-header no-print">
    <h1>Ledger</h1>
</section>

<section class="content">
    <div class="box box-solid no-print">
        <div class="box-body">
            <form method="GET" action="{{ action([\App\Http\Controllers\AccountController::class, 'ledger']) }}" id="ledger_filter_form">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('account_id', 'Ledger Name:') !!}
                        {!! Form::select('account_id', $accounts, optional($account)->id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%', 'required']) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('ledger_date_range', __('report.date_range') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('ledger_date_range', null, ['class' => 'form-control', 'readonly', 'id' => 'ledger_date_range']) !!}
                        </div>
                        <input type="hidden" name="start_date" id="ledger_start_date" value="{{ $start_date }}">
                        <input type="hidden" name="end_date" id="ledger_end_date" value="{{ $end_date }}">
                    </div>
                </div>
                <div class="col-sm-4">
                    <label>&nbsp;</label><br>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> View</button>
                    @if ($account)
                        <button type="button" class="btn btn-default" onclick="window.print()"><i class="fa fa-print"></i> @lang('messages.print')</button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if ($account)
        @php
            $closing = $opening + collect($rows)->sum('debit') - collect($rows)->sum('credit');
            $total_debit = collect($rows)->sum('debit') + max($opening, 0) + max(-$closing, 0);
            $total_credit = collect($rows)->sum('credit') + max(-$opening, 0) + max($closing, 0);
            $drcr = function ($value) { return $value >= 0 ? 'Dr' : 'Cr'; };
            $util = app(\App\Utils\Util::class);
            $num = function ($value) use ($util) { return $util->num_f($value); };
            $fdate = function ($date) use ($util) { return $util->format_date($date); };
            $last_date = null;
        @endphp
        <div class="box box-solid" id="ledger_report">
            <div class="box-body">
                <div class="text-center ledger-head">
                    <h3>{{ optional($business)->name }}</h3>
                    @if ($address)<p>{{ $address }}</p>@endif
                    <h4><strong>{{ $account->name }}</strong></h4>
                    <p>{{ $fdate($start_date) }} to {{ $fdate($end_date) }}</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed ledger-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th></th>
                                <th>Particulars</th>
                                <th>Vch Type</th>
                                <th class="text-right">Vch No.</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $fdate($start_date) }}</td>
                                <td>{{ $opening >= 0 ? 'Cr' : 'Dr' }}</td>
                                <td><strong>Opening Balance</strong></td>
                                <td></td>
                                <td></td>
                                <td class="text-right"><strong>{{ $opening >= 0 ? $num($opening) : '' }}</strong></td>
                                <td class="text-right"><strong>{{ $opening < 0 ? $num(-$opening) : '' }}</strong></td>
                                <td></td>
                            </tr>
                            @forelse ($rows as $row)
                                <tr>
                                    {{-- Like Tally, a date is printed once for its group of entries. --}}
                                    <td>{{ $row['date'] != $last_date ? $fdate($row['date']) : '' }}</td>
                                    @php $last_date = $row['date']; @endphp
                                    <td>{{ $row['debit'] !== null ? 'Cr' : 'Dr' }}</td>
                                    <td>
                                        @if ($row['link'])
                                            <a href="#" class="btn-modal" data-href="{{ $row['link'] }}" data-container=".view_modal">{{ $row['particulars'] }}</a>
                                        @else
                                            {{ $row['particulars'] }}
                                        @endif
                                    </td>
                                    <td>{{ $row['vch_type'] }}</td>
                                    <td class="text-right">{{ $row['vch_no'] }}</td>
                                    <td class="text-right">{{ $row['debit'] !== null ? $num($row['debit']) : '' }}</td>
                                    <td class="text-right">{{ $row['credit'] !== null ? $num($row['credit']) : '' }}</td>
                                    <td class="text-right">{{ $num(abs($row['balance'])) }} {{ $drcr($row['balance']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No transactions in this period.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="ledger-subtotal">
                                <td colspan="5"></td>
                                <td class="text-right">{{ $num(collect($rows)->sum('debit') + max($opening, 0)) }}</td>
                                <td class="text-right">{{ $num(collect($rows)->sum('credit') + max(-$opening, 0)) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>{{ $drcr($closing) }}</td>
                                <td><strong>Closing Balance</strong></td>
                                <td colspan="2"></td>
                                <td class="text-right">{{ $closing < 0 ? $num(-$closing) : '' }}</td>
                                <td class="text-right">{{ $closing >= 0 ? $num($closing) : '' }}</td>
                                <td></td>
                            </tr>
                            <tr class="ledger-total">
                                <td colspan="5"></td>
                                <td class="text-right"><strong>{{ $num($total_debit) }}</strong></td>
                                <td class="text-right"><strong>{{ $num($total_credit) }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</section>
@endsection

@section('css')
<style>
    .ledger-head h3, .ledger-head h4 { margin: 4px 0; }
    .ledger-head p { margin: 2px 0; }
    .ledger-table > thead > tr > th { border-top: 1px solid #000; border-bottom: 1px solid #000; }
    .ledger-table > tbody > tr > td, .ledger-table > tfoot > tr > td { border-top: none; }
    .ledger-subtotal td.text-right { border-top: 1px solid #000 !important; }
    .ledger-total td.text-right { border-top: 1px solid #000 !important; border-bottom: 3px double #000 !important; }
    @media print {
        .ledger-table a { color: #000; text-decoration: none; }
        #ledger_report { box-shadow: none; border: none; }
    }
</style>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var start = moment('{{ $start_date }}', 'YYYY-MM-DD');
    var end = moment('{{ $end_date }}', 'YYYY-MM-DD');
    $('#ledger_date_range').daterangepicker(
        $.extend({}, dateRangeSettings, {startDate: start, endDate: end}),
        function(start, end) {
            $('#ledger_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            $('#ledger_start_date').val(start.format('YYYY-MM-DD'));
            $('#ledger_end_date').val(end.format('YYYY-MM-DD'));
        }
    );
    $('#ledger_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));

    $('#account_id').on('change', function() {
        if ($(this).val()) {
            $('#ledger_filter_form').submit();
        }
    });
});
</script>
@endsection
