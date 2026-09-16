<div class="book-row well" data-index="{{ $index }}">
    <div class="clearfix"><button type="button" class="btn btn-danger btn-xs pull-right remove-book-row">Remove Row</button></div>
    <div class="row">
        @foreach (\App\MushakBook::fields($type) as $field => $kind)
            @php
                $label = ucwords(str_replace('_', ' ', $field));
                $label = str_replace(['Qty', 'Bin', 'Sd Amount', 'Vat'], ['Quantity', 'BIN / NID', 'Supplementary Duty', 'VAT'], $label);
            @endphp
            <div class="col-md-3 form-group">
                <label for="row_{{ $index }}_{{ $field }}">{{ $label }}</label>
                <input id="row_{{ $index }}_{{ $field }}" class="form-control" data-field="{{ $field }}" name="rows[{{ $index }}][{{ $field }}]" type="{{ $kind }}" value="{{ $row[$field] ?? ($kind === 'number' ? 0 : '') }}" @if ($kind === 'number') step="any" @endif @if ($kind === 'date' || $field === 'description') required @endif>
            </div>
        @endforeach
    </div>
</div>
