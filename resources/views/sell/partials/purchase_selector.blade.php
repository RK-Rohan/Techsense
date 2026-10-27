@if(auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') || auth()->user()->can('view_own_purchase'))
<div class="col-md-12">
    <div class="form-group">
        <label for="linked_purchase_id">Link Purchase:</label>
        <select name="linked_purchase_id" id="linked_purchase_id" class="form-control"
                data-url="{{ action([\App\Http\Controllers\SellController::class, 'purchaseOptions']) }}"
                data-selected="{{ old('linked_purchase_id', $transaction->linked_purchase_id ?? '') }}" style="width:100%;">
            <option value=""></option>
        </select>
        <p class="help-block">Search by purchase reference, supplier, company, client PO, or sales invoice. Selecting a purchase fills Supplier Name, Investor Name, and Purchase Amount from the purchase total.</p>
        <span id="purchase_link_error" class="text-danger" role="alert"></span>
    </div>
</div>
@endif
