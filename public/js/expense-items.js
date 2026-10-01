$(function () {
    var form = $('#add_expense_form');
    if (!form.length) return;
    var selected = $('#expense_sub_category_id');
    var initial = JSON.parse($('#expense_items_initial').text() || '[]');
    var legacyTotal = __read_number($('#final_total')) || 0;
    var nextPayment = $('#expense_payment_rows .payment_row').length;
    var itemized = initial.length > 0;

    // Each row carries its own sub category, so the same one can be added
    // more than once. The sub category dropdown above only picks the next row.
    function readRows() {
        var items = [];
        $('#expense_items_rows tr').each(function () {
            var row = $(this);
            items.push({subcategory_id: Number(row.attr('data-id')), note: row.find('.expense-item-note').val(), amount: Number(row.find('.expense-item-amount').val()) || 0});
        });
        return items;
    }
    function totals() {
        var items = readRows();
        var total = items.reduce(function (sum, item) { return sum + item.amount; }, 0);
        itemized = itemized || items.length > 0;
        $('#expense_items_json').prop('disabled', !itemized).val(JSON.stringify(items));
        $('#final_total').prop('readonly', items.length > 0);
        if (items.length) __write_number($('#final_total'), total);
        else if (itemized) { __write_number($('#final_total'), 0); }
        else total = __read_number($('#final_total')) || 0;
        var paid = 0;
        $('#expense_payment_rows .payment-amount').each(function () { paid += __read_number($(this)) || 0; });
        $('#expense_summary_total').text(__currency_trans_from_en(total, true, false));
        $('#expense_summary_paid').text(__currency_trans_from_en(paid, true, false));
        $('#payment_due').text(__currency_trans_from_en(total - paid, true, false));
    }
    function subcategoryOptions() {
        return selected.find('option').filter(function () { return this.value !== ''; });
    }
    function fillSubcategorySelect(dropdown, id) {
        dropdown.empty();
        subcategoryOptions().each(function () {
            dropdown.append(new Option($(this).text(), this.value));
        });
        // Keep a value whose options have not loaded yet.
        if (!dropdown.find('option').filter(function () { return this.value === String(id); }).length) {
            dropdown.append(new Option('', id));
        }
        dropdown.val(String(id));
    }
    function renumber() {
        $('#expense_items_rows tr').each(function (index) {
            $(this).find('.expense-item-sl').text(index + 1);
        });
    }
    function addRow(item) {
        var row = $('<tr>').attr('data-id', item.subcategory_id);
        $('<td class="expense-item-sl">').appendTo(row);
        var dropdown = $('<select>', {'class': 'form-control expense-item-subcategory'});
        fillSubcategorySelect(dropdown, item.subcategory_id);
        $('<td>').append(dropdown).appendTo(row);
        $('<td>').append($('<input>', {type:'text', 'class':'form-control expense-item-note', maxlength:5000}).val(item.note || '')).appendTo(row);
        $('<td>').append($('<input>', {type:'number', 'class':'form-control expense-item-amount', min:'0.01', max:999999999, step:'0.0001', required:true}).val(item.amount || 0)).appendTo(row);
        $('<td>').append($('<button>', {type:'button', 'class':'btn btn-danger btn-xs remove-expense-item', text:'Remove'})).appendTo(row);
        $('#expense_items_rows').append(row);
        renumber();
    }
    // A new category reloads the sub categories; rows from the old one go.
    function refreshRows() {
        var ids = subcategoryOptions().map(function () { return this.value; }).get();
        $('#expense_items_rows tr').each(function () {
            var row = $(this), id = row.attr('data-id');
            if (ids.indexOf(id) === -1) row.remove();
            else fillSubcategorySelect(row.find('.expense-item-subcategory'), id);
        });
        renumber();
    }
    function pickSubcategories() {
        var picked = (selected.val() || []).filter(function (id) { return id !== ''; });
        if (picked.length) {
            picked.forEach(function (id) { addRow({subcategory_id: id, note: '', amount: 0}); });
            // Cleared without a change event, so the same item can be picked again.
            selected.val(null).trigger('change.select2');
        } else {
            refreshRows();
        }
        totals();
    }

    if (initial.length) {
        initial.forEach(addRow);
    } else {
        // Expenses saved before itemizing keep their sub categories as rows.
        (selected.val() || []).filter(function (id) { return id !== ''; }).forEach(function (id, index) {
            addRow({subcategory_id: id, note: '', amount: index === 0 ? legacyTotal : 0});
        });
    }
    selected.val(null).trigger('change.select2');
    selected.on('change', pickSubcategories);
    $('#add_expense_item').on('click', function () { selected.select2('open'); });
    form.on('click', '.remove-expense-item', function () {
        $(this).closest('tr').remove();
        renumber();
        totals();
    });
    form.on('change', '.expense-item-subcategory', function () {
        $(this).closest('tr').attr('data-id', $(this).val());
        totals();
    });
    form.on('input change', '.expense-item-amount, .expense-item-note, .payment-amount, #final_total', totals);
    form.on('submit', totals);
    $('#add_expense_payment').on('click', function () {
        var row = $($('#expense_payment_template').html().replace(/__PAYMENT_INDEX__/g, nextPayment++));
        $('#expense_payment_rows').append(row);
        row.find('.paid_on').datetimepicker({format: moment_date_format + ' ' + moment_time_format, ignoreReadonly:true});
        row.find('.payment_types_dropdown').trigger('change');
        totals();
    });
    form.on('click', '.remove-expense-payment', function () { $(this).closest('.payment_row').remove(); totals(); });
    form.on('change', '.payment_types_dropdown, #location_id', function (event) {
        var defaults = $('#location_id option:selected').data('default_payment_accounts') || {};
        var rows = event.target.id === 'location_id' ? $('#expense_payment_rows .payment_row') : $(event.target).closest('.payment_row');
        rows.each(function () {
            var row = $(this), method = row.find('.payment_types_dropdown').val();
            row.find('select[name$="[account_id]"]').val(defaults[method] ? defaults[method].account : '').trigger('change');
        });
    });
    totals();

    // A default or browser-restored category does not emit a change event.
    // Keep server-rendered selections on edit and validation-error reloads.
    function loadInitialSubcategories() {
        var hasOptions = selected.find('option').filter(function () {
            return this.value !== '';
        }).length > 0;
        if ($('#expense_category_id').val() && !hasOptions) {
            get_expense_sub_categories();
        }
    }
    setTimeout(loadInitialSubcategories, 0);
    $(window).on('pageshow', loadInitialSubcategories);
});
