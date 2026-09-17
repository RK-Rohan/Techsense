@if ($errors->any())
    <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="box box-solid">
    <div class="box-header"><h3 class="box-title">Expense Details</h3></div>
    <div class="box-body">
        <p>Select subcategories above and enter an amount for each item. Amounts include any applicable tax.</p>
        <input type="hidden" id="expense_items_json" name="expense_items_json" disabled>
        <script type="application/json" id="expense_items_initial">{!! json_encode(old('expense_items', isset($expense) ? ($expense->expense_items ?: []) : []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        <div class="table-responsive"><table class="table table-bordered">
            <thead><tr><th>SL</th><th>Sub Category</th><th>Description / Note</th><th>Amount</th><th>Action</th></tr></thead>
            <tbody id="expense_items_rows"></tbody>
        </table></div>
        <button type="button" class="btn btn-primary" id="add_expense_item">+ Add More Sub Category</button>
    </div>
</div>
