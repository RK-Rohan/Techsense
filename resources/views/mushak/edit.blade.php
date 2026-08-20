@extends('layouts.app')
@section('title', __('lang_v1.edit_mushak'))

@section('content')

<section class="content-header">
    <h1>@lang('lang_v1.edit_mushak')
        <small>{{$transaction->invoice_no}}</small>
    </h1>
</section>

<section class="content">
    {!! Form::open(['url' => action([\App\Http\Controllers\MushakInvoiceController::class, 'update'], [$mushak->id]), 'method' => 'PUT', 'id' => 'mushak_form']) !!}

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    {!! Form::label('invoice_no_display', __('sale.invoice_no') . ':') !!}
                    {!! Form::text('invoice_no_display', $transaction->invoice_no, ['class' => 'form-control', 'readonly']); !!}
                    <p class="help-block">@lang('lang_v1.mushak_6_3') &mdash; {{optional($transaction->contact)->supplier_business_name ?: optional($transaction->contact)->name}}</p>
                </div>
            </div>
        </div>

        <hr>

        @php
            $values = [
                'mushak_invoice_no' => $mushak->mushak_invoice_no,
                'issued_at' => $issued_at_formatted,
                'purchaser_name' => $mushak->purchaser_name,
                'purchaser_bin' => $mushak->purchaser_bin,
                'purchaser_address' => $mushak->purchaser_address,
                'destination_address' => $mushak->destination_address,
                'vehicle_details' => $mushak->vehicle_details,
            ];
        @endphp

        @include('mushak.partials.form_fields', ['values' => $values])
    @endcomponent

    <div class="row">
        <div class="col-sm-12 text-center">
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
            <a href="{{action([\App\Http\Controllers\SellPosController::class, 'downloadMushak63Pdf'], [$transaction->id])}}" target="_blank" class="btn btn-info">
                <i class="fa fa-file-pdf-o"></i> @lang('messages.view') PDF</a>
            <a href="{{action([\App\Http\Controllers\MushakInvoiceController::class, 'index'])}}" class="btn btn-default">@lang('messages.cancel')</a>
        </div>
    </div>

    {!! Form::close() !!}
</section>

@endsection

@section('javascript')
@include('mushak.partials.form_javascript')
@endsection
