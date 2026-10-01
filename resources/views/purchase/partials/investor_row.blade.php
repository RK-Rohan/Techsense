<tr>
	<td>
		{!! Form::select('investors[' . $index . '][investor_id]', $investors, $investor_id ?? null, ['class' => 'form-control investor-select', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']); !!}
	</td>
	<td>
		{!! Form::text('investors[' . $index . '][amount]', $amount ?? 0, ['class' => 'form-control input_number investor-amount']); !!}
	</td>
	<td>
		<button type="button" class="btn btn-link text-danger remove-purchase-investor"><i class="fa fa-times"></i></button>
	</td>
</tr>
