<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\Accounting\Http\Controllers\CoaController::class, 'store']), 'method' => 'post', 'id' => 'create_client_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">{{ __($control_ledger ? 'accounting::lang.add_posting_account' : 'accounting::lang.add_account') }}</h4>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                @if($control_ledger)
                    {!! Form::hidden('account_primary_type', $control_ledger->account_primary_type) !!}
                    {!! Form::hidden('account_sub_type_id', $control_ledger->account_sub_type_id) !!}
                    {!! Form::hidden('parent_account_id', $control_ledger->id) !!}
                    <p><strong>{{ __('accounting::lang.control_ledger') }}</strong>
                        {{ $control_ledger->gl_code }} {{ $control_ledger->name }}</p>
                @else
                <div class="form-group">
                    {!! Form::label('account_primary_type', __($pdf_chart_only ? 'accounting::lang.account_class' : 'accounting::lang.account_type') . ':*') !!}
                    <select class="form-control" name="account_primary_type" id="account_primary_type" required>
                        <option value="">@lang('messages.please_select')</option>
                        @foreach($account_types as $account_type => $account_details)
                            <option value="{{$account_type}}">{{ $account_details['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    {!! Form::label('account_sub_type', __($pdf_chart_only ? 'accounting::lang.account_group' : 'accounting::lang.account_sub_type') . ':*') !!}
                    <select class="form-control" name="account_sub_type_id" id="account_sub_type" required>
                        <option value="">@lang('messages.please_select')</option>
                    </select>
                </div>
                @if($pdf_chart_only)
                <div class="form-group">
                    {!! Form::label('parent_account', __('accounting::lang.control_ledger') . ':*') !!}
                    {!! Form::select('parent_account_id', [], null,
                        ['class' => 'form-control', 'id' => 'parent_account', 'required',
                        'placeholder' => __('messages.please_select')]); !!}
                </div>
                @else
                <div class="form-group">
                    {!! Form::label('detail_type', __( 'accounting::lang.detail_type' ) . ':*') !!}
                    {!! Form::select('detail_type_id', [], null,  ['class' => 'form-control', 
                        'required', 'placeholder' => __('messages.please_select'), 'id' => 'detail_type' ]); !!}
                    <p class="help-block" id="detail_type_desc"></p>
                </div>
                @endif
                @endif
                <div class="form-group">
                    {!! Form::label('name', __(($pdf_chart_only || $control_ledger) ? 'accounting::lang.posting_account_name' : 'user.name') . ':*') !!}
                    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __(($pdf_chart_only || $control_ledger) ? 'accounting::lang.posting_account_name' : 'user.name') ]); !!}
                </div>
                <div class="form-group">
                    {!! Form::label('gl_code', __( 'accounting::lang.gl_code' ) . ':') !!}
                    {!! Form::text('gl_code', null, ['class' => 'form-control', 'placeholder' => __( 'accounting::lang.gl_code' ) ]); !!}
                    <p class="help-block">{{ __(($pdf_chart_only || $control_ledger) ? 'accounting::lang.pdf_gl_code_help' : 'accounting::lang.gl_code_help') }}</p>
                </div>
                @unless($pdf_chart_only || $control_ledger)
                <div class="form-group">
                    {!! Form::label('parent_account', __( 'accounting::lang.parent_account' ) . ':') !!}
                    {!! Form::select('parent_account_id', [], null,  
                        ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 
                        'id' => 'parent_account' ]); !!}
                </div>
                @endunless
            </div>
        </div>
        <div class="row" id="bal_div">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('balance', __( 'lang_v1.balance' ) . ':') !!}
                    {!! Form::text('balance', null, ['class' => 'form-control input_number', 
                        'placeholder' => __( 'lang_v1.balance' ) ]); !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('as_of', __( 'accounting::lang.as_of' ) . ':') !!}
                    <div class="input-group">
                        {!! Form::text('balance_as_of', null, ['class' => 'form-control', 'id' => 'as_of' ]); !!}
                        <span class="input-group-addon"><i class="fas fa-calendar"></i></span>
                    </div>
                </div>
            </div>
        </div>   
        <div class="row"">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('description', __( 'lang_v1.description' ) . ':') !!}
                    {!! Form::textarea('description', null, ['class' => 'form-control', 
                        'placeholder' => __( 'lang_v1.description' ) ]); !!}
                </div>
            </div>
        </div> 
    </div>

    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'messages.save' )</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->