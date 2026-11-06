@extends('layouts.app')
@section('title', 'Investments')

@section('content')
<section class="content-header">
    <h1>Investments<small id="investment_subtitle">{{ $selected_investor_name ? 'Investor: ' . $selected_investor_name : 'List of investments' }}</small></h1>
    @can('investor.view')
    <a href="{{ url('/account/investor') }}" class="btn btn-default pull-right" style="margin-left:6px;">
        <i class="fa fa-arrow-left"></i> Investors
    </a>
    @endcan
    <button type="button" class="btn btn-warning pull-right" id="check_bank_mismatch_btn" style="margin-left:6px;">
        <i class="fa fa-balance-scale"></i> Check Bank Mismatch
    </button>
    @can('investment.create')
    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#add_investment_modal">
        <i class="fa fa-plus"></i> Add Investment
    </button>
    @endcan
    <div class="clearfix"></div>
    <br>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Investor</label>
                            <select id="investment_investor_filter" class="form-control select2">
                                <option value="">All</option>
                                @foreach($investors as $id => $name)
                                    <option value="{{ $id }}" @if((string)$selected_investor_id === (string)$id) selected @endif>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date range</label>
                            <input type="text" id="investment_date_range" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Received Account</label>
                            <select id="investment_account_filter" class="form-control select2">
                                <option value="">All</option>
                                @foreach($accounts as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select id="investment_status_filter" class="form-control select2">
                                <option value="">All</option>
                                <option value="paid">Paid</option>
                                <option value="due">Due</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="investment_table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>SL No</th>
                                <th>Received Date</th>
                                <th>Investor</th>
                                <th>Transaction Ref No</th>
                                <th>Invoice No</th>
                                <th>Amount</th>
                                <th>Received Account</th>
                                <th>Return Amount</th>
                                <th>Profit</th>
                                <th>ROI %</th>
                                <th>Return Date</th>
                                <th>Payment Status</th>
                                <th>Bank</th>
                                <th>Remarks</th>
                                <th>Loan Duration</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 footer-total">
                                <td colspan="6" class="text-right"><strong>Total:</strong></td>
                                <td id="footer_total_amount"></td>
                                <td></td>
                                <td id="footer_total_return"></td>
                                <td id="footer_total_profit"></td>
                                <td colspan="6"><strong>Due:</strong> <span id="footer_total_due"></span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
@endsection

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v=' . $asset_v) }}">
    <style>
        .select2 { width: 100% !important; }
        .select2-container { width: 100% !important; }
        /* Ensure action dropdown positions correctly and isn't clipped */
        .table-responsive { overflow: visible; }
        .btn-group .dropdown-menu { position: absolute; right: 0; left: auto; }
        /* Fallback for older bootstrap variations */
        .dropdown-menu-right { right: 0; left: auto; }
    </style>
@endsection

@section('javascript')
<script>
function fmtNum(v){ return $.fn.dataTable.render.number(',', '.', 2).display(parseFloat(v) || 0); }
function esc(v){ return $('<div>').text(v === null || v === undefined ? '' : v).html(); }
var csrfHeaders = { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
// UI hints only; every action is also checked on the server
var can = @json([
    'create' => auth()->user()->can('investment.create'),
    'update' => auth()->user()->can('investment.update'),
    'delete' => auth()->user()->can('investment.delete'),
]);

$(document).ready(function(){
    var table = $('#investment_table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/account/investments-data',
            data: function(d){
                if($('#investment_date_range').val()){
                    var drp = $('#investment_date_range').data('daterangepicker');
                    d.start_date = drp.startDate.format('YYYY-MM-DD');
                    d.end_date = drp.endDate.format('YYYY-MM-DD');
                }
                d.received_account_id = $('#investment_account_filter').val();
                d.investor_id = $('#investment_investor_filter').val();
                d.payment_status = $('#investment_status_filter').val();
            }
        },
        // Totals follow the filters and the search box (rows with search applied)
        footerCallback: function(){
            var api = this.api();
            var totalAmount = 0, totalReturn = 0, totalProfit = 0;
            api.rows({ search: 'applied' }).data().each(function(row){
                totalAmount += parseFloat(row.amount) || 0;
                totalReturn += parseFloat(row.return_amount) || 0;
                totalProfit += parseFloat(row.profit) || 0;
            });
            var fmt = $.fn.dataTable.render.number(',', '.', 2).display;
            $('#footer_total_amount').text(fmt(totalAmount));
            $('#footer_total_return').text(fmt(totalReturn));
            $('#footer_total_profit').text(fmt(totalProfit));
            $('#footer_total_due').text(fmt(Math.max(totalAmount - totalReturn, 0)));
        },
        columns: [
            { data: null, orderable:false, searchable:false, render: function(data, type, row){
                var items = [];
                if (can.create) items.push('<li><a href="#" class="add-amount-btn" data-id="'+row.id+'"><i class="fa fa-plus-circle"></i> Add Amount</a></li>');
                items.push('<li><a href="#" class="investment-history-btn" data-id="'+row.id+'"><i class="fa fa-history"></i> History</a></li>');
                if (can.update || can.delete) items.push('<li class="divider"></li>');
                if (can.update) {
                    items.push('<li><a href="#" class="edit-investment-btn" data-id="'+row.id+'">Edit</a></li>');
                    items.push('<li><a href="#" class="edit-investment-return-btn" data-id="'+row.id+'">Edit Return</a></li>');
                }
                if (can.delete) items.push('<li><a href="#" class="delete-investment-btn" data-id="'+row.id+'">Delete</a></li>');
                items.push('<li class="divider"></li>');
                items.push('<li><a href="/account/investments/'+row.id+'/pdf" target="_blank">PDF Slip</a></li>');
                return '<div class="btn-group">'+
                  '<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>'+
                  '<ul class="dropdown-menu dropdown-menu-right" role="menu">'+items.join('')+'</ul>'+
                '</div>';
            }},
            { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
            { data: 'received_date' },
            { data: 'investor_name' },
            { data: 'txn_ref' },
            { data: 'invoice_no' },
            { data: 'amount', render: function(data, type, row){
                if (type !== 'display') return data;
                var html = fmtNum(data);
                if (row.top_up_count > 0) {
                    html += '<br><small class="text-muted" title="Initial '+fmtNum(row.initial_amount)+' + added '+fmtNum(row.top_up_total)+'">'+
                        'incl. '+row.top_up_count+' added</small>';
                }
                return html;
            } },
            { data: 'received_account_name' },
            { data: 'return_amount', render: function(data){ return data ? $.fn.dataTable.render.number(',', '.', 2).display(data) : ''; } },
            { data: 'profit', render: function(data){ return data === null ? '' : $.fn.dataTable.render.number(',', '.', 2).display(data); } },
            { data: 'roi', render: function(data){ return data === null ? '' : $.fn.dataTable.render.number(',', '.', 2).display(data) + '%'; } },
            { data: 'return_date' },
            { data: 'payment_status', render: function(data, type, row){
                var amt = row.return_amount ? parseFloat(row.return_amount) : 0;
                if (amt > 0) { return '<span class="label bg-green">Paid</span>'; }
                return '<span class="label bg-red">Due</span>';
            } },
            { data: 'bank_issues', orderable:false, render: function(data, type, row){
                if (type !== 'display') return data && data.length ? 'mismatch' : 'ok';
                if (!data || !data.length) return '<span class="label bg-green">OK</span>';
                return '<a href="#" class="investment-history-btn" data-id="'+row.id+'" title="'+esc(data.join('\n'))+'">'+
                    '<span class="label bg-orange"><i class="fa fa-exclamation-triangle"></i> Mismatch</span></a>';
            } },
            { data: 'remarks' },
            { data: 'loan_duration_days', render: function(data){ if(!data && data !== 0) return ''; return data + ' day(s)'; } }
        ]
    });

    // daterangepicker
    $('#investment_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#investment_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            table.ajax.reload();
        }
    );
    $('#investment_date_range').on('cancel.daterangepicker', function(){
        $('#investment_date_range').val('');
        table.ajax.reload();
    });

    $(document).on('change', '#investment_account_filter, #investment_status_filter', function(){
        table.ajax.reload();
    });

    $(document).on('change', '#investment_investor_filter', function(){
        var id = $(this).val();
        $('#investment_subtitle').text(id ? 'Investor: ' + $(this).find('option:selected').text() : 'List of investments');
        // Keep the URL shareable / refresh-safe
        if (window.history && history.replaceState) {
            history.replaceState(null, '', '/account/investments' + (id ? '?investor_id=' + encodeURIComponent(id) : ''));
        }
        table.ajax.reload();
    });

    // Default the Add Investment form to the investor being viewed
    $('#add_investment_modal').on('show.bs.modal', function(){
        var id = $('#investment_investor_filter').val();
        if (id) { $('#add_investment_form select[name="investor_id"]').val(id).trigger('change'); }
    });

    // submit add investment
    $(document).on('submit', '#add_investment_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            method: 'POST',
            url: '/account/investments',
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#add_investment_modal').modal('hide');
                    toastr.success(res.msg);
                    table.ajax.reload();
                    $('#add_investment_form')[0].reset();
                    $('.select2').val('').trigger('change');
                } else {
                    toastr.error(res.msg || 'Unable to add');
                }
            },
            error: function(){
                toastr.error('Error adding investment');
            }
        });
    });

    // Initialize select2 inside modal on show to ensure correct width
    $('#add_investment_modal').on('shown.bs.modal', function(){
        $(this).find('.select2').select2({
            width: '100%',
            dropdownParent: $('#add_investment_modal')
        });
    });

    $('#edit_investment_modal').on('shown.bs.modal', function(){
        $(this).find('.select2').select2({
            width: '100%',
            dropdownParent: $('#edit_investment_modal')
        });
    });

    $('#edit_investment_return_modal').on('shown.bs.modal', function(){
        $(this).find('.select2').select2({
            width: '100%',
            dropdownParent: $('#edit_investment_return_modal')
        });
    });

    // Edit investment open
    $(document).on('click', '.edit-investment-btn', function(){
        var row = $('#investment_table').DataTable().row($(this).closest('tr')).data();
        if(!row) return;
        $('#edit_investment_id').val(row.id);
        $('#edit_investor_id').val(row.investor_id).trigger('change');
        // The amount field edits the initial amount; added amounts sit on top of it
        $('#edit_amount').val((parseFloat(row.initial_amount) || 0).toFixed(2));
        if (row.top_up_count > 0) {
            $('#edit_amount_label').text('Initial Amount*');
            $('#edit_amount_help').text('Plus ' + fmtNum(row.top_up_total) + ' added in ' + row.top_up_count +
                ' later entr' + (row.top_up_count === 1 ? 'y' : 'ies') + ' (see History). Current total: ' + fmtNum(row.amount) + '.').show();
        } else {
            $('#edit_amount_label').text('Invest Amount*');
            $('#edit_amount_help').text('').hide();
        }
        $('#edit_received_account_id').val(row.received_account_id).trigger('change');
        $('#edit_received_date').val(row.received_date);
        $('#edit_invoice_no').val(row.invoice_no).trigger('change');
        $('#edit_txn_ref').val(row.txn_ref);
        $('#edit_remarks').val(row.remarks);
        $('#edit_investment_modal').modal('show');
    });

    // Update investment submit
    $(document).on('submit', '#edit_investment_form', function(e){
        e.preventDefault();
        var id = $('#edit_investment_id').val();
        var data = $(this).serialize();
        $.ajax({
            method: 'PUT',
            url: '/account/investments/' + id,
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#edit_investment_modal').modal('hide');
                    toastr.success(res.msg);
                    table.ajax.reload();
                } else {
                    toastr.error(res.msg || 'Unable to update');
                }
            },
            error: function(){
                toastr.error('Error updating investment');
            }
        });
    });

    // Delete investment
    $(document).on('click', '.delete-investment-btn', function(){
        var row = $('#investment_table').DataTable().row($(this).closest('tr')).data();
        if(!row) return;
        if(!confirm('Are you sure you want to delete this investment?')) return;
        $.ajax({
            method: 'DELETE',
            url: '/account/investments/' + row.id,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    toastr.success(res.msg);
                    table.ajax.reload();
                } else {
                    toastr.error('Unable to delete');
                }
            },
            error: function(){ toastr.error('Error deleting investment'); }
        });
    });

    // Edit return open
    $(document).on('click', '.edit-investment-return-btn', function(){
        var row = $('#investment_table').DataTable().row($(this).closest('tr')).data();
        if(!row) return;
        $('#edit_return_investment_id').val(row.id);
        $('#edit_return_amount').val(row.return_amount);
        $('#edit_return_date').val(row.return_date);
        $('#edit_return_account_id').val(row.return_account_id).trigger('change');
        $('#edit_investment_return_modal').modal('show');
    });

    // Submit edit return
    $(document).on('submit', '#edit_investment_return_form', function(e){
        e.preventDefault();
        var id = $('#edit_return_investment_id').val();
        var data = $(this).serialize();
        $.ajax({
            method: 'POST',
            url: '/account/investments/' + id + '/return',
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#edit_investment_return_modal').modal('hide');
                    toastr.success(res.msg);
                    $('#investment_table').DataTable().ajax.reload();
                } else {
                    toastr.error(res.msg || 'Unable to save return');
                }
            },
            error: function(){ toastr.error('Error saving return'); }
        });
    });

    function ajaxError(xhr, fallback){
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            var errors = xhr.responseJSON.errors, first = Object.keys(errors)[0];
            toastr.error(first ? errors[first][0] : fallback);
        } else { toastr.error(fallback); }
    }

    // ---- Add Amount (top-up on the same invoice) ----
    $('#add_amount_modal').on('shown.bs.modal', function(){
        $(this).find('.select2').select2({ width: '100%', dropdownParent: $('#add_amount_modal') });
    });

    $(document).on('click', '.add-amount-btn', function(e){
        e.preventDefault();
        var row = table.row($(this).closest('tr')).data();
        if(!row) return;
        $('#add_amount_form')[0].reset();
        $('#add_amount_investment_id').val(row.id);
        $('#add_amount_investor').text(row.investor_name || '');
        $('#add_amount_invoice').text(row.invoice_no || '—');
        $('#add_amount_current').text(fmtNum(row.amount));
        $('#add_amount_date').val(moment().format('YYYY-MM-DD'));
        $('#add_amount_account').val(row.received_account_id || '').trigger('change');
        $('#add_amount_new_total').text(fmtNum(row.amount)).data('base', parseFloat(row.amount) || 0);
        $('#add_amount_modal').modal('show');
    });

    $(document).on('input', '#add_amount_value', function(){
        var base = $('#add_amount_new_total').data('base') || 0;
        $('#add_amount_new_total').text(fmtNum(base + (parseFloat($(this).val()) || 0)));
    });

    $(document).on('submit', '#add_amount_form', function(e){
        e.preventDefault();
        var id = $('#add_amount_investment_id').val();
        var $btn = $(this).find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            method: 'POST',
            url: '/account/investments/' + id + '/add-amount',
            data: $(this).serialize(),
            headers: csrfHeaders,
            success: function(res){
                if(res.success){
                    $('#add_amount_modal').modal('hide');
                    toastr.success(res.msg);
                    table.ajax.reload(null, false);
                } else { toastr.error(res.msg || 'Unable to add amount'); }
            },
            error: function(xhr){ ajaxError(xhr, 'Error adding amount'); },
            complete: function(){ $btn.prop('disabled', false); }
        });
    });

    // ---- History (ledger entries + bank status) ----
    function loadHistory(id){
        $('#history_investment_id').val(id);
        $('#history_body').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
        $.get('/account/investments/' + id + '/history', function(res){
            if(!res.success){ toastr.error('Unable to load history'); return; }
            $('#history_investor').text(res.investor_name || '');
            $('#history_invoice').text(res.invoice_no || '—');
            $('#history_total').text(fmtNum(res.total));
            var html = '';
            $.each(res.data, function(i, r){
                var bank = r.bank_issue
                    ? '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> '+esc(r.bank_issue)+'</span> '+
                      (can.update ? '<button type="button" class="btn btn-xs btn-warning sync-entry-btn" data-id="'+r.id+'">Fix</button>' : '')
                    : (r.account_name ? '<span class="text-success"><i class="fa fa-check"></i> Matched</span>' : '<span class="text-muted">No account</span>');
                var del = r.type === 'top_up' && can.delete
                    ? '<button type="button" class="btn btn-xs btn-danger delete-entry-btn" data-id="'+r.id+'"><i class="fa fa-trash"></i></button>' : '';
                var amt = r.type === 'return' ? '<span class="text-danger">-'+fmtNum(r.amount)+'</span>' : fmtNum(r.amount);
                html += '<tr><td>'+esc(r.transaction_date)+'</td><td>'+esc(r.type_label)+'</td><td class="text-right">'+amt+'</td>'+
                        '<td>'+esc(r.account_name)+'</td><td>'+bank+'</td><td>'+esc(r.note)+'</td><td>'+del+'</td></tr>';
            });
            $('#history_body').html(html || '<tr><td colspan="7" class="text-center">No entries</td></tr>');
        }).fail(function(){ toastr.error('Error loading history'); });
    }

    $(document).on('click', '.investment-history-btn', function(e){
        e.preventDefault();
        loadHistory($(this).data('id'));
        $('#investment_history_modal').modal('show');
    });

    $(document).on('click', '.delete-entry-btn', function(){
        if(!confirm('Remove this added amount? Its bank account entry will be removed too.')) return;
        $.ajax({
            method: 'DELETE',
            url: '/account/investment-entries/' + $(this).data('id'),
            headers: csrfHeaders,
            success: function(res){
                if(res.success){ toastr.success(res.msg); loadHistory($('#history_investment_id').val()); table.ajax.reload(null, false); }
                else { toastr.error(res.msg || 'Unable to remove'); }
            },
            error: function(){ toastr.error('Error removing entry'); }
        });
    });

    // Fix one entry (used in both History and the mismatch report)
    $(document).on('click', '.sync-entry-btn', function(){
        var $btn = $(this).prop('disabled', true);
        $.ajax({
            method: 'POST',
            url: '/account/investment-entries/' + $btn.data('id') + '/sync-bank',
            headers: csrfHeaders,
            success: function(res){
                if(res.success){
                    toastr.success(res.msg);
                    if ($('#investment_history_modal').hasClass('in')) loadHistory($('#history_investment_id').val());
                    if ($('#bank_mismatch_modal').hasClass('in')) loadReconcile();
                    table.ajax.reload(null, false);
                } else { toastr.error(res.msg || 'Unable to fix'); $btn.prop('disabled', false); }
            },
            error: function(){ toastr.error('Error fixing entry'); $btn.prop('disabled', false); }
        });
    });

    // ---- Bank mismatch report ----
    function loadReconcile(){
        $('#mismatch_body').html('<tr><td colspan="8" class="text-center">Checking...</td></tr>');
        $('#orphan_body').html('');
        $.get('/account/investments-reconcile', function(res){
            var m = '', o = '';
            $.each(res.mismatches, function(i, r){
                m += '<tr><td>'+esc(r.investor_name)+'</td><td>'+esc(r.invoice_no)+'</td><td>'+esc(r.type_label)+'</td>'+
                     '<td>'+esc(r.date)+'</td><td class="text-right">'+fmtNum(r.amount)+'<br><small>'+esc(r.account_name)+'</small></td>'+
                     '<td class="text-right">'+(r.bank_amount === null ? '—' : fmtNum(r.bank_amount))+'<br><small>'+esc(r.bank_account_name)+'</small></td>'+
                     '<td class="text-danger">'+esc(r.issue)+'</td>'+
                     '<td>'+(can.update ? '<button type="button" class="btn btn-xs btn-warning sync-entry-btn" data-id="'+r.entry_id+'">Fix</button>' : '')+'</td></tr>';
            });
            $.each(res.orphans, function(i, r){
                o += '<tr><td>'+esc(r.operation_date)+'</td><td>'+esc(r.account_name)+'</td><td>'+esc(r.type)+'</td>'+
                     '<td class="text-right">'+fmtNum(r.amount)+'</td><td>'+esc(r.note)+'</td>'+
                     '<td>'+(can.delete ? '<button type="button" class="btn btn-xs btn-danger delete-orphan-btn" data-id="'+r.id+'">Remove</button>' : '')+'</td></tr>';
            });
            $('#mismatch_count').text(res.mismatches.length);
            $('#orphan_count').text(res.orphans.length);
            $('#sync_all_btn').toggle(can.update && res.mismatches.length > 0);
            $('#mismatch_body').html(m || '<tr><td colspan="8" class="text-center text-success"><i class="fa fa-check"></i> Every investment entry matches its bank account entry</td></tr>');
            $('#orphan_body').html(o || '<tr><td colspan="6" class="text-center text-success"><i class="fa fa-check"></i> No unlinked investment entries in bank accounts</td></tr>');
        }).fail(function(){ toastr.error('Error checking mismatches'); });
    }

    $('#check_bank_mismatch_btn').on('click', function(){
        loadReconcile();
        $('#bank_mismatch_modal').modal('show');
    });

    $('#sync_all_btn').on('click', function(){
        if(!confirm('Update every mismatched bank entry to match the investment records?')) return;
        var $btn = $(this).prop('disabled', true);
        $.ajax({
            method: 'POST',
            url: '/account/investments-reconcile/sync-all',
            headers: csrfHeaders,
            success: function(res){
                if(res.success){ toastr.success(res.msg); loadReconcile(); table.ajax.reload(null, false); }
                else { toastr.error(res.msg || 'Unable to fix'); }
            },
            error: function(){ toastr.error('Error fixing mismatches'); },
            complete: function(){ $btn.prop('disabled', false); }
        });
    });

    $(document).on('click', '.delete-orphan-btn', function(){
        if(!confirm('Remove this bank entry? No investment record points to it, so it is likely left over from a deleted investment or a repeated return.')) return;
        $.ajax({
            method: 'DELETE',
            url: '/account/investments-orphan-bank/' + $(this).data('id'),
            headers: csrfHeaders,
            success: function(res){
                if(res.success){ toastr.success(res.msg); loadReconcile(); }
                else { toastr.error(res.msg || 'Unable to remove'); }
            },
            error: function(){ toastr.error('Error removing bank entry'); }
        });
    });
});
</script>

<!-- Add Amount Modal -->
<div class="modal fade" id="add_amount_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add Amount</h4>
      </div>
      <form id="add_amount_form">
      <div class="modal-body">
        <input type="hidden" id="add_amount_investment_id">
        <table class="table table-condensed" style="margin-bottom:12px;">
            <tr><th style="width:40%;">Investor</th><td id="add_amount_investor"></td></tr>
            <tr><th>Invoice No</th><td id="add_amount_invoice"></td></tr>
            <tr><th>Current Total</th><td id="add_amount_current"></td></tr>
            <tr><th>New Total</th><td><strong id="add_amount_new_total"></strong></td></tr>
        </table>
        <div class="form-group">
            <label>Amount to Add*</label>
            <input type="number" step="0.01" min="0.01" name="amount" id="add_amount_value" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Date*</label>
            <input type="date" name="transaction_date" id="add_amount_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Deposit To Account</label>
            <select name="account_id" id="add_amount_account" class="form-control select2">
                <option value="">None (no bank entry)</option>
                @foreach($accounts as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Note</label>
            <textarea name="note" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Add Amount</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Investment History Modal -->
<div class="modal fade" id="investment_history_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Investment History &mdash; <span id="history_investor"></span></h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="history_investment_id">
        <p><strong>Invoice No:</strong> <span id="history_invoice"></span> &nbsp; | &nbsp; <strong>Total Invested:</strong> <span id="history_total"></span></p>
        <div class="table-responsive">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr><th>Date</th><th>Type</th><th class="text-right">Amount</th><th>Account</th><th>Bank Status</th><th>Note</th><th></th></tr>
                </thead>
                <tbody id="history_body"></tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Bank Mismatch Modal -->
<div class="modal fade" id="bank_mismatch_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document" style="width:90%;max-width:1200px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Investment vs Bank Account Check</h4>
      </div>
      <div class="modal-body">
        <h4>Mismatched entries (<span id="mismatch_count">0</span>)
            <button type="button" class="btn btn-warning btn-sm pull-right" id="sync_all_btn" style="display:none;">Fix All</button>
        </h4>
        <p class="text-muted">"Fix" updates the bank account entry to match the investment record.</p>
        <div class="table-responsive">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr><th>Investor</th><th>Invoice</th><th>Entry</th><th>Date</th><th class="text-right">Investment</th><th class="text-right">Bank</th><th>Issue</th><th></th></tr>
                </thead>
                <tbody id="mismatch_body"></tbody>
            </table>
        </div>

        <h4>Unlinked bank entries (<span id="orphan_count">0</span>)</h4>
        <p class="text-muted">Investment deposits/returns in bank accounts that no investment record owns, e.g. left behind by a deleted investment or a return saved more than once.</p>
        <div class="table-responsive">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr><th>Date</th><th>Account</th><th>Type</th><th class="text-right">Amount</th><th>Note</th><th></th></tr>
                </thead>
                <tbody id="orphan_body"></tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Investment Modal -->
<div class="modal fade" id="add_investment_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add Investment</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="add_investment_form">
      <div class="modal-body">
        <div class="form-group">
            <label>Investor*</label>
            <select name="investor_id" class="form-control select2" required>
                <option value="">Select investor</option>
                @foreach($investors as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Invest Amount*</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Deposit To Account</label>
            <select name="received_account_id" class="form-control select2">
                <option value="">Select account</option>
                @foreach($accounts as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Received Date</label>
            <input type="date" name="received_date" class="form-control">
        </div>
        <div class="form-group">
            <label>Invoice No</label>
            <select name="invoice_no" class="form-control select2">
                <option value="">None</option>
                @if(!empty($invoices))
                    @foreach($invoices as $inv_no => $inv_label)
                        <option value="{{ $inv_no }}">{{ $inv_label }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="form-group">
            <label>Transaction Ref No</label>
            <input type="text" name="txn_ref" class="form-control">
        </div>
        <div class="form-group">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Investment Return Modal -->
<div class="modal fade" id="edit_investment_return_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Return</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="edit_investment_return_form">
      <div class="modal-body">
        <input type="hidden" id="edit_return_investment_id" name="investment_id">
        <div class="form-group">
            <label>Return Amount*</label>
            <input type="number" step="0.01" name="return_amount" id="edit_return_amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Return Date*</label>
            <input type="date" name="return_date" id="edit_return_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Return Account</label>
            <select name="return_account_id" id="edit_return_account_id" class="form-control select2">
                <option value="">Select account</option>
                @foreach($accounts as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Investment Modal -->
<div class="modal fade" id="edit_investment_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Investment</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="edit_investment_form">
      <div class="modal-body">
        <input type="hidden" id="edit_investment_id" name="investment_id">
        <div class="form-group">
            <label>Investor*</label>
            <select name="investor_id" id="edit_investor_id" class="form-control select2" required>
                <option value="">Select investor</option>
                @foreach($investors as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label id="edit_amount_label">Invest Amount*</label>
            <input type="number" step="0.01" min="0.01" name="amount" id="edit_amount" class="form-control" required>
            <small class="text-muted" id="edit_amount_help" style="display:none;"></small>
            <small class="text-muted" style="display:block;">Saving also updates the linked bank account entry.</small>
        </div>
        <div class="form-group">
            <label>Deposit To Account</label>
            <select name="received_account_id" id="edit_received_account_id" class="form-control select2">
                <option value="">Select account</option>
                @foreach($accounts as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Received Date</label>
            <input type="date" name="received_date" id="edit_received_date" class="form-control">
        </div>
        <div class="form-group">
            <label>Invoice No</label>
            <select name="invoice_no" id="edit_invoice_no" class="form-control select2">
                <option value="">None</option>
                @if(!empty($invoices))
                    @foreach($invoices as $inv_no => $inv_label)
                        <option value="{{ $inv_no }}">{{ $inv_label }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="form-group">
            <label>Transaction Ref No</label>
            <input type="text" name="txn_ref" id="edit_txn_ref" class="form-control">
        </div>
        <div class="form-group">
            <label>Remarks</label>
            <textarea name="remarks" id="edit_remarks" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
      </form>
    </div>
  </div>
</div>
@endsection
