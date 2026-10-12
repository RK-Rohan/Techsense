@foreach(['mushak_6_1' => 'Mushak 6.1', 'mushak_6_2' => 'Mushak 6.2', 'mushak_6_3' => 'Mushak 6.3', 'sales_documents' => 'Sales Documents'] as $prefix => $title)
    <hr>
    <div class="row check_group">
        <div class="col-md-2"><h4>{{ $title }}</h4></div>
        <div class="col-md-2">
            <div class="checkbox"><label><input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}</label></div>
        </div>
        <div class="col-md-8">
            @foreach($prefix === 'sales_documents' ? ['invoice_download.view' => 'View / Download Invoice', 'delivery_challan.view' => 'View / Download Delivery Chalan'] : [$prefix.'.view' => 'View '.$title, $prefix.'.create' => 'Add '.$title, $prefix.'.update' => 'Edit '.$title, $prefix.'.delete' => 'Delete '.$title] as $permission => $label)
                <div class="col-md-12"><div class="checkbox"><label>
                    {!! Form::checkbox('permissions[]', $permission, in_array($permission, $role_permissions ?? []), ['class' => 'input-icheck']) !!} {{ $label }}
                </label></div></div>
            @endforeach
        </div>
    </div>
@endforeach
