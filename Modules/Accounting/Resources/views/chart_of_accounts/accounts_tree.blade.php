
@if(!$account_exist)
<table class="table table-bordered table-striped">
    <tr>
        <td colspan="10" class="text-center">
            <h3>@lang( 'accounting::lang.no_accounts' )</h3>
            <p>@lang( 'accounting::lang.add_default_accounts_help' )</p>
            <a href="{{route('accounting.create-default-accounts')}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-accent">@lang( 'accounting::lang.add_default_accounts' ) <i class="fas fa-file-import"></i></a>
        </td>
    </tr>
</table>
@else
<div class="row">
    <div class="col-md-4 mb-12 col-md-offset-4">
        <div class="input-group">
            <input type="input" class="form-control" id="accounts_tree_search">
            <span class="input-group-addon">
                <i class="fas fa-search"></i>
            </span>
        </div>
    </div>
    <div class="col-md-4">
        <button class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm" id="expand_all">@lang('accounting::lang.expand_all')</button>
        <button class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm" id="collapse_all">@lang('accounting::lang.collapse_all')</button>
    </div>
    <div class="col-md-12" id="accounts_tree_container">
        <ul>
        @foreach($account_types as $key => $value)
            @if($has_accrual_chart && !$accounts->contains('account_primary_type', $key))
                @continue
            @endif
            <li @if($loop->index==0) data-jstree='{ "opened" : true }' @endif>
                {{$value}}
                <ul>
                    @foreach($account_sub_types->where('account_primary_type', $key)->all() as $sub_type)
                        @if($has_accrual_chart && !$sub_type->chart_code && !$accounts->contains('account_sub_type_id', $sub_type->id))
                            @continue
                        @endif
                        <li @if($loop->index==0) data-jstree='{ "opened" : true }' @endif>
                            {{$sub_type->account_type_name}} @if($sub_type->chart_code) ({{$sub_type->chart_code}}) @endif
                            <ul>
                            @foreach($accounts->where('account_sub_type_id', $sub_type->id)->sortBy($sub_type->chart_code ? 'id' : 'name')->all() as $account)
                                <li @if(count($account->child_accounts) == 0) data-jstree='{ "icon" : "fas fa-arrow-alt-circle-right" }' @endif>
                                    {{$account->name}} @if(!empty($account->gl_code))({{$account->gl_code}}) @endif 
                                    - @format_currency($account->chart_balance)
                                    @if($account->is_control_ledger)
                                        <span class="text-muted">({{ __('accounting::lang.control_ledger') }})</span>
                                    @endif
                                    @if($account->status == 'active')  
                                        <span><i class="fas fa-check text-success" title="@lang( 'accounting::lang.active' )"></i></span>
                                    @elseif($account->status == 'inactive') 
                                        <span><i class="fas fa-times text-danger" 
                                        title="@lang( 'lang_v1.inactive' )" style="font-size: 14px;"></i></span>
                                    @endif
                                    <span class="tree-actions">
                                        @if($account->is_control_ledger)
                                            @php($addPostingAccountUrl = route('chart-of-accounts.create', ['control_ledger_id' => $account->id]))
                                            <a class="btn btn-xs btn-default text-primary btn-modal"
                                                title="{{ __('accounting::lang.add_posting_account') }}"
                                                aria-label="{{ __('accounting::lang.add_posting_account') }}"
                                                href="{{ $addPostingAccountUrl }}" data-href="{{ $addPostingAccountUrl }}"
                                                data-container="#create_account_modal">
                                                <i class="fas fa-plus" aria-hidden="true"></i></a>
                                        @endif
                                        <a class="btn btn-xs btn-default text-success ledger-link" 
                                            title="@lang( 'accounting::lang.ledger' )"
                                            href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'], $account->id)}}">
                                            <i class="fas fa-file-alt"></i></a>
                                        <a class="btn-modal btn-xs btn-default text-primary" title="@lang('messages.edit')"
                                            href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $account->id)}}" 
                                            data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $account->id)}}" 
                                            data-container="#create_account_modal">
                                        <i class="fas fa-edit"></i></a>
                                        <a class="activate-deactivate-btn text-warning  btn-xs btn-default" 
                                            title="@if($account->status=='active') @lang('messages.deactivate') @else 
                                            @lang('messages.activate') @endif"
                                            href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'activateDeactivate'], $account->id)}}">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                    </span>
                                    @if(count($account->child_accounts) > 0)
                                        <ul>
                                        @foreach($account->child_accounts as $child_account)
                                            <li data-jstree='{ "icon" : "fas fa-arrow-alt-circle-right" }'>
                                                {{$child_account->name}} 
                                                @if(!empty($child_account->gl_code))({{$child_account->gl_code}}) @endif
                                                 - @format_currency($child_account->balance)

                                                @if($child_account->status == 'active') 
                                                    <span><i class="fas fa-check text-success" title="@lang( 'accounting::lang.active' )"></i></span>
                                                @elseif($child_account->status == 'inactive') 
                                                    <span><i class="fas fa-times text-danger" 
                                                    title="@lang( 'lang_v1.inactive' )" style="font-size: 14px;"></i></span>
                                                @endif
                                                 <span class="tree-actions">
                                                    <a class="btn btn-xs btn-default text-success ledger-link" 
                                                        title="@lang( 'accounting::lang.ledger' )"
                                                        href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'], $child_account->id)}}">
                                                        <i class="fas fa-file-alt"></i></a>
                                                    <a class="btn-modal btn-xs btn-default text-primary" title="@lang('messages.edit')"
                                                        href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $child_account->id)}}" 
                                                        data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $child_account->id)}}" 
                                                        data-container="#create_account_modal">
                                                    <i class="fas fa-edit"></i></a>
                                                    <a class="activate-deactivate-btn text-warning  btn-xs btn-default" 
                                                        title="@if($child_account->status=='active') @lang('messages.deactivate') @else 
                                                        @lang('messages.activate') @endif"
                                                        href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'activateDeactivate'], $child_account->id)}}">
                                                        <i class="fas fa-power-off"></i>
                                                        </a>
                                                </span>
                                            </li>
                                        @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
        </ul>
    </div>
</div>
@endif