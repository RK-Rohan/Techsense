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
        @if ($book->exists)
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Saved report entries'])
            <p>Changes apply only to this saved report. Purchase and sales data remain unchanged. Total and closing balances are calculated when saved.</p>
            <div id="book_rows">
                @forelse (old('rows', $book->rows) as $index => $row)
                    @include('mushak.books.row', ['index' => $index, 'row' => $row])
                @empty
                    <p>No transactions were found in the selected date range.</p>
                @endforelse
            </div>
        @endcomponent
        @else
            <p>Generate and save a report from transactions in the selected date range.</p>
        @endif
        <button class="btn btn-primary" type="submit">{{ $book->exists ? 'Save Changes' : 'Generate Mushak '.str_replace('-', '.', $type) }}</button>
        <a class="btn btn-default" href="{{ route($type === '6-1' ? 'mushak.purchaseBook' : 'mushak.salesBook') }}">Cancel</a>
    </form>
</section>
@endsection
@section('javascript')
<script>
$(function() {
    // Send rows as JSON to avoid PHP max_input_vars truncating larger books.
    $('#book_form').on('submit', function() {
        if (!$('#book_rows').length) return;
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
