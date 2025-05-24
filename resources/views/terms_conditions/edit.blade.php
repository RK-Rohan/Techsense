<form action="{{ action([\App\Http\Controllers\TermsConditionController::class, 'update'], [$termsCondition->id]) }}" method="POST" id="terms_edit_form">
    @csrf
    @method('PUT')

    <div class="modal-header">
        <h4 class="modal-title">@lang('Edit Terms & Conditions')</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('description', 'Description:*') !!}
            {!! Form::text('description', $termsCondition->description, ['class' => 'form-control', 'required', 'placeholder' => 'Description']) !!}
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.cancel')</button>
    </div>
</form>
