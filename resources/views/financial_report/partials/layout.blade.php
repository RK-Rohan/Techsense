@extends('layouts.app')
@section('title', $report_title)

@section('content')

<section class="content-header">
    <h1>{{ $report_title }}</h1>
</section>

<section class="content">
    <div class="row no-print">
        <div class="col-sm-12">
            @component('components.filters', ['title' => __('report.filters')])
                {!! Form::open(['url' => action([\App\Http\Controllers\FinancialReportController::class, 'show'], ['report' => $report]), 'method' => 'get', 'id' => 'financial_report_form']) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                        {!! Form::select('location_id', $business_locations, $location_id, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('start_date', __('business.start_date') . ':') !!}
                        {!! Form::date('start_date', $start_date, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('end_date', 'End Date:') !!}
                        {!! Form::date('end_date', $end_date, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i> @lang('report.apply_filters')
                        </button>
                    </div>
                </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>

    <div class="row no-print">
        <div class="col-sm-12">
            <div class="btn-group" style="margin-bottom:10px;">
                <button type="button" class="btn btn-default btn-sm" onclick="window.print();">
                    <i class="fa fa-print"></i> @lang('messages.print')
                </button>
                <a class="btn btn-default btn-sm" target="_blank"
                   href="{{ action([\App\Http\Controllers\FinancialReportController::class, 'show'], ['report' => $report, 'start_date' => $start_date, 'end_date' => $end_date, 'location_id' => $location_id, 'pdf' => 1]) }}">
                    <i class="fa fa-file-pdf"></i> @lang('lang_v1.download_pdf')
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="box box-solid">
                <div class="box-body">
                    <div class="statement-wrapper">
                        <div class="statement-heading">
                            <h3>{{ $business_name }}</h3>
                            <h4>{{ $report_title }}</h4>
                            <p>@yield('statement_period')</p>
                        </div>

                        @yield('statement_body')
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('css')
<style type="text/css">
    .statement-wrapper { max-width: 900px; margin: 0 auto; }
    .statement-heading { text-align: center; margin-bottom: 20px; }
    .statement-heading h3 { font-weight: bold; margin-bottom: 4px; }
    .statement-heading h4 { font-weight: bold; margin-top: 0; margin-bottom: 4px; }
    .statement-heading p { margin: 0; }
    table.statement { width: 100%; border-collapse: collapse; }
    table.statement th, table.statement td { padding: 4px 8px; vertical-align: top; }
    table.statement thead th { border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: bold; }
    table.statement .num { text-align: right; width: 140px; white-space: nowrap; }
    table.statement .notes-col { text-align: center; width: 60px; }
    table.statement tr.total td, table.statement tr.total th { font-weight: bold; border-top: 1px solid #000; }
    table.statement tr.grand-total td, table.statement tr.grand-total th { font-weight: bold; border-top: 1px solid #000; border-bottom: 3px double #000; }
    table.statement tr.section th { font-weight: bold; padding-top: 12px; }
    table.statement td.indent { padding-left: 28px; }
    table.statement tr.spacer td { height: 10px; border: none; }
    @media print {
        .no-print, .main-sidebar, .main-header, .content-header { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .box { border: none !important; box-shadow: none !important; }
    }
</style>
@endsection
