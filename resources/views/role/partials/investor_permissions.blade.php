@foreach(['investor' => __('role.investor'), 'investment' => __('role.investment')] as $prefix => $title)
    <hr>
    <div class="row check_group">
        <div class="col-md-2"><h4>{{ $title }}</h4></div>
        <div class="col-md-2">
            <div class="checkbox"><label><input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}</label></div>
        </div>
        <div class="col-md-8">
            @foreach(['view', 'create', 'update', 'delete'] as $action)
                @php $permission = $prefix.'.'.$action; @endphp
                <div class="col-md-12"><div class="checkbox"><label>
                    {!! Form::checkbox('permissions[]', $permission, in_array($permission, $role_permissions ?? []), ['class' => 'input-icheck']) !!} {{ __('role.'.$permission) }}
                    @if($permission === 'investor.update')
                        <small class="text-muted">(includes managing portal login)</small>
                    @elseif($permission === 'investment.create')
                        <small class="text-muted">(includes Add Amount)</small>
                    @elseif($permission === 'investment.update')
                        <small class="text-muted">(includes Edit Return and fixing bank mismatches)</small>
                    @endif
                </label></div></div>
            @endforeach
        </div>
    </div>
@endforeach
