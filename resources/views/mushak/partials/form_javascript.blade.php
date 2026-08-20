<script type="text/javascript">
$(document).ready(function() {
    $('#issued_at').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    var $invoice = $('#transaction_id');

    if ($invoice.length) {
        $invoice.select2({
            ajax: {
                url: "{{url('mushak/get-transactions')}}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data };
                },
            },
            minimumInputLength: 0,
            placeholder: "@lang('lang_v1.select_invoice_to_generate')",
        });

        //Pull the sale's values into the form so every field starts from the
        //invoice and can then be edited for the VAT document.
        $invoice.on('change', function() {
            var transaction_id = $(this).val();
            if (!transaction_id) {
                return;
            }

            $.ajax({
                url: $invoice.data('defaults-url') + '/' + transaction_id,
                dataType: 'json',
                success: function(result) {
                    if (result.existing_id) {
                        toastr.error("@lang('lang_v1.mushak_already_exists')");
                    }

                    var d = result.defaults || {};
                    $.each(d, function(field, value) {
                        var $field = $('#mushak_form').find('[name="' + field + '"]');
                        if ($field.length) {
                            $field.val(value);
                        }
                    });
                },
            });
        });
    }
});
</script>
