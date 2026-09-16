@extends('layouts.app')
@section('title', 'Mushak '.str_replace('-', '.', $type))
@section('content')
<section class="content-header"><h1>{{ $book->exists ? 'Edit' : 'Generate' }} Mushak {{ str_replace('-', '.', $type) }}</h1></section>
<section class="content">
    @if ($errors->any())
        <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" action="{{ $book->exists ? route('mushak.books.update', [$type, $book->id]) : route('mushak.books.store', $type) }}" id="book_form">
        @csrf
        @if ($book->exists) @method('PUT') @endif
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Document details'])
            <div class="row">
                @foreach (['document_no' => 'Document No.', 'issued_at' => 'Date of Issue', 'start_date' => 'Period From', 'end_date' => 'Period To', 'registered_name' => 'Registered Person', 'seller_address' => 'Registered Address', 'seller_bin' => 'BIN'] as $field => $label)
                    <div class="col-md-4 form-group">
                        <label for="{{ $field }}">{{ $label }}</label>
                        <input class="form-control" id="{{ $field }}" name="{{ $field }}" type="{{ in_array($field, ['issued_at', 'start_date', 'end_date']) ? 'date' : 'text' }}" value="{{ old($field, $book->$field) }}" {{ in_array($field, ['seller_address', 'seller_bin']) ? '' : 'required' }}>
                    </div>
                @endforeach
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Custom entries'])
            <p>Enter the details for this account book. Each row has its own opening balance; total and closing balances are calculated when saved.</p>
            <div id="book_rows">
                @foreach (old('rows', $book->rows) as $index => $row)
                    @include('mushak.books.row', ['index' => $index, 'row' => $row])
                @endforeach
            </div>
            <button class="btn btn-default" type="button" id="add_book_row"><i class="fa fa-plus"></i> Add Row</button>
        @endcomponent
        <button class="btn btn-primary" type="submit">{{ $book->exists ? 'Save Changes' : 'Generate Mushak '.str_replace('-', '.', $type) }}</button>
        <a class="btn btn-default" href="{{ route($type === '6-1' ? 'mushak.purchaseBook' : 'mushak.salesBook') }}">Cancel</a>
    </form>
    <template id="book_row_template">@include('mushak.books.row', ['index' => '__INDEX__', 'row' => []])</template>
</section>
@endsection
@section('javascript')
<script>
$(function() {
    var nextIndex = Math.max.apply(null, $('#book_rows .book-row').map(function() { return Number($(this).data('index')); }).get()) + 1;
    $('#add_book_row').on('click', function() {
        $('#book_rows').append($('#book_row_template').html().replace(/__INDEX__/g, nextIndex++));
    });
    $('#book_rows').on('click', '.remove-book-row', function() {
        if ($('#book_rows .book-row').length > 1) $(this).closest('.book-row').remove();
        else toastr.error('Keep at least one entry.');
    });
    // Send rows as JSON to avoid PHP max_input_vars truncating larger books.
    $('#book_form').on('submit', function() {
        var rows = [];
        $('#book_rows .book-row').each(function() {
            var row = {};
            $(this).find('[data-field]').each(function() { row[$(this).data('field')] = $(this).val(); });
            rows.push(row);
        });
        $('<input>', {type: 'hidden', name: 'rows_json', value: JSON.stringify(rows)}).appendTo(this);
        $('#book_rows [data-field]').prop('disabled', true);
    });
});
</script>
@endsection
