@extends('layouts.app')
@section('title', __('lang_v1.generate_mushak'))

@section('content')

<section class="content-header">
    <h1>@lang('lang_v1.generate_mushak')</h1>
</section>

<section class="content">
    {!! Form::open(['url' => action([\App\Http\Controllers\MushakInvoiceController::class, 'store']), 'method' => 'post', 'id' => 'mushak_form']) !!}

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    {!! Form::label('transaction_id', __('sale.invoice_no') . ':*') !!}
                    <select name="transaction_id" id="transaction_id" class="form-control" required
                        data-defaults-url="{{url('mushak/transaction-defaults')}}"
                        @if(!empty($transaction)) data-preselected="{{$transaction->id}}" @endif>
                        @if(!empty($transaction))
                            <option value="{{$transaction->id}}" selected>
                                {{$transaction->invoice_no}} - {{optional($transaction->contact)->supplier_business_name ?: optional($transaction->contact)->name}}
                            </option>
                        @endif
                    </select>
                    <p class="help-block">@lang('lang_v1.select_invoice_to_generate')</p>
                </div>
            </div>
        </div>

        <hr>

        @include('mushak.partials.form_fields', ['values' => $defaults ?? []])
    @endcomponent

    <div class="row">
        <div class="col-sm-12 text-center">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <a href="{{action([\App\Http\Controllers\MushakInvoiceController::class, 'index'])}}" class="btn btn-default">@lang('messages.cancel')</a>
        </div>
    </div>

    {!! Form::close() !!}
</section>

@endsection

@section('javascript')
@include('mushak.partials.form_javascript')
@endsection
