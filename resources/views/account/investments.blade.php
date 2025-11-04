@extends('layouts.app')
@section('title', 'Investments')

@section('content')
<section class="content-header">
    <h1>Investments<small>List of investments</small></h1>
    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#add_investment_modal">
        <i class="fa fa-plus"></i> Add Investment
    </button>
    <div class="clearfix"></div>
    <br>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Date range</label>
                            <input type="text" id="investment_date_range" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
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
                                <th>Return Date</th>
                                <th>Payment Status</th>
                                <th>Remarks</th>
                                <th>Loan Duration</th>
                            </tr>
                        </thead>
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
            }
        },
        columns: [
            { data: null, orderable:false, searchable:false, render: function(data, type, row){
                var html = ''+
                '<div class="btn-group">'+
                  '<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>'+
                  '<ul class="dropdown-menu dropdown-menu-right" role="menu">'+
                    '<li><a href="#" class="edit-investment-btn" data-id="'+row.id+'">Edit</a></li>'+
                    '<li><a href="#" class="edit-investment-return-btn" data-id="'+row.id+'">Edit Return</a></li>'+
                    '<li><a href="#" class="delete-investment-btn" data-id="'+row.id+'">Delete</a></li>'+
                    '<li class="divider"></li>'+
                    '<li><a href="/account/investments/'+row.id+'/pdf" target="_blank">PDF Slip</a></li>'+
                  '</ul>'+
                '</div>';
                return html;
            }},
            { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
            { data: 'received_date' },
            { data: 'investor_name' },
            { data: 'txn_ref' },
            { data: 'invoice_no' },
            { data: 'amount', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'received_account_name' },
            { data: 'return_amount', render: function(data){ return data ? $.fn.dataTable.render.number(',', '.', 2).display(data) : ''; } },
            { data: 'return_date' },
            { data: 'payment_status', render: function(data, type, row){
                var amt = row.return_amount ? parseFloat(row.return_amount) : 0;
                if (amt > 0) { return '<span class="label bg-green">Paid</span>'; }
                return '<span class="label bg-red">Due</span>';
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

    $(document).on('change', '#investment_account_filter', function(){
        table.ajax.reload();
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
        $('#edit_amount').val(row.amount);
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
});
</script>

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
            <label>Invest Amount*</label>
            <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
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
