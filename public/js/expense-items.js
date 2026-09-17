$(function () {
    var form = $('#add_expense_form');
    if (!form.length) return;
    var selected = $('#expense_sub_category_id');
    var initial = JSON.parse($('#expense_items_initial').text() || '[]');
    var cache = {};
    initial.forEach(function (item) { cache[String(item.subcategory_id)] = item; });
    var legacyTotal = __read_number($('#final_total')) || 0;
    var legacy = initial.length === 0;
    var nextPayment = $('#expense_payment_rows .payment_row').length;
    var itemized = initial.length > 0;

    function readRows() {
        var items = [];
        $('#expense_items_rows tr').each(function () {
            var row = $(this);
            var item = {subcategory_id: Number(row.attr('data-id')), note: row.find('.expense-item-note').val(), amount: Number(row.find('.expense-item-amount').val()) || 0};
            cache[String(item.subcategory_id)] = item;
            items.push(item);
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
    function sync() {
        readRows();
        var body = $('#expense_items_rows').empty();
        (selected.val() || []).forEach(function (id, index) {
            var item = cache[id] || {amount: legacy && index === 0 ? legacyTotal : 0, note: ''};
            var row = $('<tr>').attr('data-id', id);
            $('<td>').text(index + 1).appendTo(row);
            $('<td>').text(selected.find('option').filter(function () { return this.value === String(id); }).text()).appendTo(row);
            $('<td>').append($('<input>', {type:'text', 'class':'form-control expense-item-note', maxlength:5000}).val(item.note)).appendTo(row);
            $('<td>').append($('<input>', {type:'number', 'class':'form-control expense-item-amount', min:'0.01', max:999999999, step:'0.0001', required:true}).val(item.amount)).appendTo(row);
            $('<td>').append($('<button>', {type:'button', 'class':'btn btn-danger btn-xs remove-expense-item', text:'Remove'})).appendTo(row);
            body.append(row);
        });
        legacy = false;
        totals();
    }
    selected.on('change', sync);
    $('#add_expense_item').on('click', function () { selected.select2('open'); });
    form.on('click', '.remove-expense-item', function () {
        var id = $(this).closest('tr').attr('data-id');
        selected.val((selected.val() || []).filter(function (value) { return value !== id; })).trigger('change');
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
    sync();

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
