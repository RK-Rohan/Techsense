$(function () {
    var selector = $('#linked_purchase_id');
    if (!selector.length) return;
    var fields = $('#custom_field_4, #custom_field_5, #custom_field_6');
    function lockFields() {
        fields.prop('readonly', !!selector.val());
    }
    selector.select2({
        placeholder: 'Select purchase to autofill',
        allowClear: true,
        ajax: {
            url: selector.data('url'),
            dataType: 'json',
            delay: 250,
            data: function (params) { return {q: params.term || '', page: params.page || 1}; },
            processResults: function (data) { return data; }
        }
    }).on('select2:select', function (event) {
        $('#purchase_link_error').text('');
        $.each(event.params.data.fields, function (name, value) {
            $('#' + name).val(value || '').trigger('change');
        });
        lockFields();
    }).on('select2:clear', function () {
        fields.val('').prop('readonly', false).trigger('change');
    });
    var selected = selector.attr('data-selected');
    if (selected) {
        // Preserve saved values on open; fetch only the selected purchase label.
        selector.append(new Option('Purchase #' + selected, selected, true, true)).trigger('change');
        lockFields();
        $.getJSON(selector.data('url'), {id: selected}).done(function (data) {
            if (selector.val() !== selected) return;
            if (data.results.length) {
                selector.find('option:selected').text(data.results[0].text);
                selector.trigger('change');
            } else {
                $('#purchase_link_error').text('The linked purchase is unavailable. Clear it or select another purchase before saving.');
            }
        }).fail(function () {
            $('#purchase_link_error').text('Could not load the linked purchase. Please try again.');
        });
    }
});
