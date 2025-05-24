$(document).ready(function () {
    function manageLocationSelection() {
        var selectedValue = $('#select_location_id').val();
        if (selectedValue == '2') {
            $('.delivery_time').addClass('hide');
            $('#custom_field_div_1').addClass('hide');
            $('#custom_field_div_2').addClass('hide');
            $('#custom_field_div_3').addClass('hide');
            $('#custom_field_div_4').addClass('hide');
            $('.term_condition_div').addClass('hide');
            $('.pre_sales_box').removeClass('hide');
            $('.customer_address_on_select').addClass('hide');

            $('.payment_by_div').removeClass('hide');
            $('.supplier_div').removeClass('hide');
            $('.supplier_amount_div').removeClass('hide');

            $('.agent_div').removeClass('hide');
            $('.tracking_div').removeClass('hide');
            $('.carton_no_div').removeClass('hide');
        } else {
            $('.delivery_time').removeClass('hide');
            $('#custom_field_div_1').removeClass('hide');
            $('#custom_field_div_2').removeClass('hide');
            $('#custom_field_div_3').removeClass('hide');
            $('#custom_field_div_4').removeClass('hide');
            $('.term_condition_div').removeClass('hide');
            $('.pre_sales_box').addClass('hide');
            $('.customer_address_on_select').removeClass('hide');

            $('.payment_by_div').addClass('hide');
            $('.supplier_div').addClass('hide');
            $('.supplier_amount_div').addClass('hide');

            $('.agent_div').addClass('hide');
            $('.tracking_div').addClass('hide');
            $('.carton_no_div').addClass('hide');
        }

        //get suppliers
        $('#supplier_id').select2({
            ajax: {
                url: '/purchases/get_suppliers',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data,
                    };
                },
            },
            minimumInputLength: 1,
            escapeMarkup: function (m) {
                return m;
            },
            templateResult: function (data) {
                if (!data.id) {
                    return data.text;
                }
                var html = data.text + ' - ' + data.business_name + ' (' + data.contact_id + ')';
                return html;
            },
            language: {
                noResults: function () {
                    var name = $('#supplier_id')
                        .data('select2')
                        .dropdown.$search.val();
                    return (
                        '<button type="button" data-name="' +
                        name +
                        '" class="btn btn-link add_new_supplier"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                        __translate('add_name_as_new_supplier', { name: name }) +
                        '</button>'
                    );
                },
            },
        });

    }

    $('#supplier_payment_by').on('change', function () {
        // Get the selected value
        var paymentBy = $(this).val();

        if (paymentBy === 'by_client') {
            // Set supplier_amount as readonly
            $('#supplier_amount').prop('readonly', true);
        } else {
            // Remove readonly from supplier_amount
            $('#supplier_amount').prop('readonly', false);
        }
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#saveAgentButton').on('click', function (e) {
        e.preventDefault(); // Prevent the default form submission

        var agentName = $('#agent_name').val();
        var agentPhoneNumber = $('#agent_phone_number').val();
        var agentAddress = $('#agent_address').val();

        if (agentName && agent_phone_number) {
            $.ajax({
                url: '/contacts/agents/store',
                type: 'POST',
                data: {
                    agent_name: agentName,
                    agent_phone_number: agentPhoneNumber,
                    agent_address: agentAddress
                },
                success: function (response) {
                    var newAgentId = response.agent.id;
                    var newAgentName = response.agent.name;

                    var newOption = new Option(newAgentName, newAgentId, true, true); // Select by default
                    $('#agent_id').append(newOption).trigger('change');

                    $('#addAgentModal').modal('hide');
                    $('#addAgentForm')[0].reset();
                    toastr.success(response.message);
                },
                error: function (xhr, status, error) {
                    console.error(error);
                    alert('Failed to save the agent. Please try again.');
                }
            });

        } else {
            alert('Please fill in all the required fields.');
        }
    });

    function calculateCNFCost() {
        // Get quantity and cnf_rate values
        var quantity = $('input[name="products[1][quantity]"]').val();
        var cnfRate = parseFloat($('#cnf_rate').val());
        var posLineTotal = parseFloat($('.pos_line_total').val().replace(/,/g, '')); // Remove commas and convert to number

        // Ensure the values are valid
        if (!isNaN(quantity) && !isNaN(cnfRate)) {
            // Calculate the CNF cost
            var cnfCost = quantity * cnfRate;
            var saleProfit = posLineTotal - cnfCost;


            // Update the cnf_cost field
            $('#cnf_cost').val(cnfCost.toFixed(2));
            // Update the sale_profit field
            $('#sale_profit').val(saleProfit.toFixed(2));
        }
    }

    // Trigger the calculation on quantity or cnf_rate change
    $('input[name="products[1][quantity]"], input[name="products[1][unit_price]"] , #cnf_rate').on('input change', function () {
        calculateCNFCost();
    });



    // Call the function on page load to handle default value
    manageLocationSelection();

    // Call the function on change event
    $('#select_location_id').change(function () {
        manageLocationSelection();
    });

    $('#delivery_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
    $('#received_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });


});