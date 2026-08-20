@extends('layouts.app')
@section('title', __('lang_v1.mushak_6_3'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.mushak_6_3')
        <small>VAT Invoice</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">

    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('mushak_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('mushak_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.mushak_6_3')])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary"
                    href="{{action([\App\Http\Controllers\MushakInvoiceController::class, 'create'])}}">
                    <i class="fa fa-plus"></i> @lang('lang_v1.generate_mushak')</a>
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="mushak_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('lang_v1.mushak_invoice_no')</th>
                        <th>@lang('lang_v1.date_time_of_issue')</th>
                        <th>@lang('sale.invoice_no')</th>
                        <th>@lang('lang_v1.purchaser_name')</th>
                        <th>@lang('sale.total_amount')</th>
                        <th>@lang('sale.tax')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    $('#mushak_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#mushak_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            mushak_table.ajax.reload();
        }
    );
    $('#mushak_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#mushak_date_range').val('');
        mushak_table.ajax.reload();
    });

    mushak_table = $('#mushak_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[2, 'desc']],
        ajax: {
            url: "{{action([\App\Http\Controllers\MushakInvoiceController::class, 'index'])}}",
            data: function(d) {
                if ($('#mushak_date_range').val()) {
                    var start = $('#mushak_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#mushak_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
            }
        },
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'mushak_invoice_no', name: 'mushak_invoices.mushak_invoice_no' },
            { data: 'issued_at', name: 'mushak_invoices.issued_at' },
            { data: 'invoice_no', name: 't.invoice_no' },
            { data: 'purchaser_name', name: 'mushak_invoices.purchaser_name' },
            { data: 'final_total', name: 't.final_total' },
            { data: 'tax_amount', name: 't.tax_amount' },
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#mushak_table'));
        },
    });

    $(document).on('click', 'a.delete_mushak', function(e) {
        e.preventDefault();
        var href = $(this).data('href');

        swal({
            title: LANG.sure,
            text: LANG.confirm_delete,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            mushak_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
});
</script>
@endsection
