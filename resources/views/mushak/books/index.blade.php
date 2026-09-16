@extends('layouts.app')
@section('title', 'Mushak '.str_replace('-', '.', $type))
@section('content')
<section class="content-header">
    <h1>Mushak {{ str_replace('-', '.', $type) }} <small>{{ $type === '6-1' ? 'Purchase Account Book' : 'Sales Account Book' }}</small></h1>
</section>
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3 form-group">
            <label for="book_date_range">Date Range:</label>
            <input id="book_date_range" class="form-control" readonly placeholder="Select a date range">
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Mushak '.str_replace('-', '.', $type)])
        @slot('tool')
            <div class="box-tools"><a class="btn btn-primary" href="{{ route('mushak.books.create', $type) }}"><i class="fa fa-plus"></i> Generate Mushak {{ str_replace('-', '.', $type) }}</a></div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="book_table">
                <thead><tr><th>Action</th><th>Document No.</th><th>Date of Issue</th><th>Period From</th><th>Period To</th><th>Registered Person</th><th>Total Value (Excluding Taxes)</th><th>VAT</th></tr></thead>
            </table>
        </div>
    @endcomponent
</section>
@endsection
@section('javascript')
<script>
$(function() {
    var table = $('#book_table').DataTable({
        processing: true, serverSide: true, aaSorting: [[2, 'desc']],
        ajax: {url: @json(route($type === '6-1' ? 'mushak.purchaseBook' : 'mushak.salesBook')), data: function(d) {
            if ($('#book_date_range').val()) {
                var picker = $('#book_date_range').data('daterangepicker');
                d.start_date = picker.startDate.format('YYYY-MM-DD');
                d.end_date = picker.endDate.format('YYYY-MM-DD');
            }
        }},
        columns: [
            {data: 'action', orderable: false, searchable: false},
            {data: 'document_no'}, {data: 'issued_at'}, {data: 'start_date'}, {data: 'end_date'},
            {data: 'registered_name'},
            {data: 'total_amount', render: $.fn.dataTable.render.number(',', '.', 2)},
            {data: 'tax_amount', render: $.fn.dataTable.render.number(',', '.', 2)}
        ]
    });
    $('#book_date_range').daterangepicker(dateRangeSettings, function(start, end) {
        $('#book_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        table.ajax.reload();
    }).val('').on('cancel.daterangepicker', function() { $(this).val(''); table.ajax.reload(); });
    $(document).on('click', '.delete_mushak', function(e) {
        e.preventDefault();
        var url = $(this).data('href');
        swal({title: LANG.sure, text: LANG.confirm_delete, icon: 'warning', buttons: true, dangerMode: true}).then(function(confirmed) {
            if (confirmed) $.ajax({url: url, method: 'DELETE', dataType: 'json', success: function(result) {
                toastr.success(result.msg); table.ajax.reload();
            }, error: function() { toastr.error('Unable to delete this document.'); }});
        });
    });
});
</script>
@endsection
