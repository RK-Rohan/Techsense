<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('lang_v1.expense') ({{ $expense->ref_no }})</h4>
        </div>
        <div class="modal-body">
            @include('expense.partials.expense_details')
        </div>
        <div class="modal-footer">
            <a href="#" class="btn btn-primary no-print print-invoice"
                data-href="{{ action([\App\Http\Controllers\ExpenseController::class, 'show'], [$expense->id]) }}?print=1">
                <i class="fa fa-print" aria-hidden="true"></i> @lang('messages.print')
            </a>
            <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
