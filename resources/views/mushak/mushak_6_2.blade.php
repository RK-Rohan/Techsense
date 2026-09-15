@extends('layouts.app')
@section('title', __('lang_v1.mushak_6_2'))

@section('content')

<section class="content-header">
    <h1>@lang('lang_v1.mushak_6_2')
        <small>@lang('lang_v1.sales_account_book')</small>
    </h1>
</section>

<section class="content">

    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('mushak_6_2_location_id', __('purchase.business_location') . ':') !!}
                {!! Form::select('mushak_6_2_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('mushak_6_2_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('mushak_6_2_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'mushak_6_2_date_range', 'readonly']); !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.mushak_6_2')])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" id="mushak_6_2_pdf" href="#" target="_blank">
                    <i class="fa fa-file-pdf-o"></i> @lang('messages.view') PDF</a>
            </div>
        @endslot

        <div class="table-responsive" id="mushak_6_2_container">
            <i class="fas fa-sync fa-spin fa-fw"></i>
        </div>
    @endcomponent

</section>

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    var base_url = "{{ action([\App\Http\Controllers\MushakRegisterController::class, 'salesBook']) }}";

    function mushak_6_2_filters() {
        var filters = {};

        if ($('#mushak_6_2_date_range').val()) {
            var picker = $('#mushak_6_2_date_range').data('daterangepicker');
            filters.start_date = picker.startDate.format('YYYY-MM-DD');
            filters.end_date = picker.endDate.format('YYYY-MM-DD');
        }

        if ($('#mushak_6_2_location_id').val()) {
            filters.location_id = $('#mushak_6_2_location_id').val();
        }

        return filters;
    }

    function load_mushak_6_2() {
        $('#mushak_6_2_container').html('<i class="fas fa-sync fa-spin fa-fw"></i>');

        $.ajax({
            url: base_url,
            data: mushak_6_2_filters(),
            success: function(result) {
                $('#mushak_6_2_container').html(result);
                __currency_convert_recursively($('#mushak_6_2_container'));
            },
        });

        //Keep the PDF link in step with the filters on screen.
        var pdf_params = $.param($.extend({ pdf: 1 }, mushak_6_2_filters()));
        $('#mushak_6_2_pdf').attr('href', base_url + '?' + pdf_params);
    }

    $('#mushak_6_2_date_range').daterangepicker(
        dateRangeSettings,
        function(start, end) {
            $('#mushak_6_2_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            load_mushak_6_2();
        }
    );
    $('#mushak_6_2_date_range').on('cancel.daterangepicker', function() {
        $('#mushak_6_2_date_range').val('');
        load_mushak_6_2();
    });

    $('#mushak_6_2_location_id').change(function() {
        load_mushak_6_2();
    });

    load_mushak_6_2();
});
</script>
@endsection
