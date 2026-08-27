{{-- Shared editable fields for generating/editing a Mushak 6.3. --}}
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('mushak_invoice_no', __('lang_v1.mushak_invoice_no') . ':') !!}
            {!! Form::text('mushak_invoice_no', $values['mushak_invoice_no'] ?? null, ['class' => 'form-control', 'placeholder' => __('lang_v1.mushak_invoice_no')]); !!}
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('issued_at', __('lang_v1.date_time_of_issue') . ':*') !!}
            {!! Form::text('issued_at', $values['issued_at'] ?? null, ['class' => 'form-control', 'id' => 'issued_at', 'required', 'readonly']); !!}
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('vehicle_details', __('lang_v1.vehicle_details') . ':') !!}
            {!! Form::text('vehicle_details', $values['vehicle_details'] ?? null, ['class' => 'form-control', 'placeholder' => __('lang_v1.vehicle_details')]); !!}
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('purchaser_name', __('lang_v1.purchaser_name') . ':') !!}
            {!! Form::text('purchaser_name', $values['purchaser_name'] ?? null, ['class' => 'form-control', 'placeholder' => __('lang_v1.purchaser_name')]); !!}
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('purchaser_bin', __('lang_v1.purchaser_bin') . ':') !!}
            {!! Form::text('purchaser_bin', $values['purchaser_bin'] ?? null, ['class' => 'form-control', 'placeholder' => __('lang_v1.purchaser_bin')]); !!}
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('authorised_person', "Authorised Person's Name:") !!}
            {!! Form::text('authorised_person', $values['authorised_person'] ?? null, ['class' => 'form-control', 'placeholder' => "Authorised Person's Name"]); !!}
        </div>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('designation', 'Designation:') !!}
            {!! Form::text('designation', $values['designation'] ?? null, ['class' => 'form-control', 'placeholder' => 'Designation']); !!}
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('purchaser_address', __('lang_v1.purchaser_address') . ':') !!}
            {!! Form::textarea('purchaser_address', $values['purchaser_address'] ?? null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('lang_v1.purchaser_address')]); !!}
        </div>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('destination_address', __('lang_v1.destination_address') . ':') !!}
            {!! Form::textarea('destination_address', $values['destination_address'] ?? null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('lang_v1.destination_address')]); !!}
        </div>
    </div>
</div>
