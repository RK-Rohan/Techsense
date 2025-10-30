@extends('layouts.app')
@section('title', 'Investors')

@section('content')
<section class="content-header">
    <h1>Investors<small>List of investors and loans</small></h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
                <div class="row">
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date range</label>
                                    <input type="text" id="investor_date_range" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Received Account</label>
                                    <select id="investor_account_filter" class="form-control select2">
                                        <option value="">All</option>
                                        @if(!empty($accounts))
                                            @foreach($accounts as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select id="investor_payment_status" class="form-control select2">
                                        <option value="">All</option>
                                        <option value="paid">Paid</option>
                                        <option value="due">Due</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary pull-right" id="add_investor_btn" data-toggle="modal" data-target="#add_investor_modal">
                            <i class="fa fa-plus"></i> Add Investor
                        </button>
                        <button type="button" class="btn btn-default pull-right m-1" id="investor_reset_filters" style="margin-right:10px;">
                            <i class="fa fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="investor_table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>SL No</th>
                                <th>Name</th>
                                <th>Invoice No</th>
                                <th>Phone Number</th>
                                <th>Invest Amount</th>
                                <th>Received Date</th>
                                <th>Received Account</th>
                                <th>Return Amount</th>
                                <th>Return Date</th>
                                <th>Return Account</th>
                                <th>Payment Status</th>
                                <th>Remarks</th>
                                <th>Loan Duration</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script>
    $(document).ready(function(){
            var table = $('#investor_table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '/account/investors-data',
                data: function(d){
                    // date range
                    if($('#investor_date_range').val()){
                        var drp = $('#investor_date_range').data('daterangepicker');
                        d.start_date = drp.startDate.format('YYYY-MM-DD');
                        d.end_date = drp.endDate.format('YYYY-MM-DD');
                    }
                    d.received_account_id = $('#investor_account_filter').val();
                    d.payment_status = $('#investor_payment_status').val();
                }
            },
            columns: [
                { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
                { data: 'name' },
                { data: 'invoice_no' },
                { data: 'phone' },
                { data: 'invest_amount', render: $.fn.dataTable.render.number(',', '.', 2) },
                { data: 'received_date' },
                { data: 'received_account_name' },
                { data: 'return_amount', render: function(data){ return data ? $.fn.dataTable.render.number(',', '.', 2).display(data) : ''; } },
                { data: 'return_date' },
                { data: 'return_account_name' },
                { data: 'payment_status', render: function(data, type, row){
                        var amt = row.return_amount ? parseFloat(row.return_amount) : 0;
                        if (amt > 0) {
                            return '<span class="label bg-green">Paid</span>';
                        }
                        return '<span class="label bg-red">Due</span>';
                    }
                },
                { data: 'remarks' },
                { data: 'loan_duration_days', render: function(data, type, row){
                        if(!data && data !== 0) return '';
                        return data + ' day(s)';
                    }
                },
                { data: null, orderable: false, searchable: false, render: function(data, type, row){
                        var returnBtn = row.return_amount ? '<button class="btn btn-sm btn-info add-return-btn" data-id="'+row.id+'">Edit Return</button>' : '<button class="btn btn-sm btn-primary add-return-btn" data-id="'+row.id+'">Add Return</button>';
                        var editBtn = '<button class="btn btn-sm btn-warning edit-investor-btn" data-id="'+row.id+'">Edit</button>';
                        var delBtn = '<button class="btn btn-sm btn-danger delete-investor-btn" data-id="'+row.id+'">Delete</button>';
                        return returnBtn + ' ' + editBtn + ' ' + delBtn;
                    }
                }
            ]
        });

        // daterangepicker
        $('#investor_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#investor_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                table.ajax.reload();
            }
        );
        $('#investor_date_range').on('cancel.daterangepicker', function(){
            $('#investor_date_range').val('');
            table.ajax.reload();
        });
        // filter change
        $(document).on('change', '#investor_account_filter, #investor_payment_status', function(){
            table.ajax.reload();
        });
        $('#investor_reset_filters').on('click', function(){
            $('#investor_date_range').val('');
            $('#investor_account_filter').val('').trigger('change');
            $('#investor_payment_status').val('').trigger('change');
            table.ajax.reload();
        });
    });
</script>

<!-- Edit Investor Modal -->
<div class="modal fade" id="edit_investor_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Investor</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="edit_investor_form">
      <div class="modal-body">
        <input type="hidden" name="investor_id" id="edit_investor_id">
        <div class="form-group">
            <label>Name*</label>
            <input type="text" name="name" id="edit_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Invoice No</label>
            <select name="invoice_no" class="form-control select2" id="edit_invoice_no">
                <option value="">None</option>
                @if(!empty($invoices))
                    @foreach($invoices as $inv_no => $inv_label)
                        <option value="{{ $inv_no }}">{{ $inv_label }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" id="edit_phone" class="form-control">
        </div>
        <div class="form-group">
            <label>Invest Amount*</label>
            <input type="number" step="0.01" name="invest_amount" id="edit_invest_amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Deposit To Account*</label>
            <select name="received_account_id" class="form-control select2" id="edit_received_account_id">
                <option value="">Select account</option>
                @if(!empty($accounts))
                    @foreach($accounts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="form-group">
            <label>Received Date</label>
            <input type="date" name="received_date" id="edit_received_date" class="form-control">
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

<script>
    // Edit investor click
    $(document).on('click', '.edit-investor-btn', function(){
        var id = $(this).data('id');
        var row = $('#investor_table').DataTable().row($(this).closest('tr')).data();
        $('#edit_investor_id').val(id);
        $('#edit_name').val(row.name);
        $('#edit_phone').val(row.phone);
        $('#edit_invest_amount').val(row.invest_amount);
        $('#edit_invoice_no').val(row.invoice_no).trigger('change');
        $('#edit_received_account_id').val(row.received_account_id).trigger('change');
        $('#edit_received_date').val(row.received_date);
        $('#edit_remarks').val(row.remarks);
        $('#edit_investor_modal').modal('show');
    });

    // Submit edit
    $(document).on('submit', '#edit_investor_form', function(e){
        e.preventDefault();
        var id = $('#edit_investor_id').val();
        var data = {
            name: $('#edit_name').val(),
            phone: $('#edit_phone').val(),
            invoice_no: $('#edit_invoice_no').val(),
            invest_amount: $('#edit_invest_amount').val(),
            received_account_id: $('#edit_received_account_id').val(),
            received_date: $('#edit_received_date').val(),
            remarks: $('#edit_remarks').val()
        };
        $.ajax({
            method: 'PUT',
            url: '/account/investor/' + id,
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#edit_investor_modal').modal('hide');
                    toastr.success(res.msg);
                    $('#investor_table').DataTable().ajax.reload();
                } else {
                    toastr.error('Unable to update');
                }
            },
            error: function(xhr){
                var msg = 'Error';
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            }
        });
    });

    // Delete investor
    $(document).on('click', '.delete-investor-btn', function(){
        var id = $(this).data('id');
        if(!confirm('Are you sure you want to delete this investor?')) return;
        $.ajax({
            method: 'DELETE',
            url: '/account/investor/' + id,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    toastr.success(res.msg);
                    $('#investor_table').DataTable().ajax.reload();
                } else {
                    toastr.error('Unable to delete');
                }
            },
            error: function(){
                toastr.error('Error deleting investor');
            }
        });
    });
</script>
<div class="modal fade" id="add_investor_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add Investor</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="add_investor_form">
      <div class="modal-body">
        <div class="form-group">
            <label>Name*</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Invoice No</label>
            <select name="invoice_no" class="form-control select2" id="add_invoice_no">
                <option value="">None</option>
                @if(!empty($invoices))
                    @foreach($invoices as $inv_no => $inv_label)
                        <option value="{{ $inv_no }}">{{ $inv_label }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="form-group">
            <label>Invest Amount*</label>
            <input type="number" step="0.01" name="invest_amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Deposit To Account*</label>
            <select name="received_account_id" class="form-control select2" id="add_received_account_id">
                <option value="">Select account</option>
                @if(!empty($accounts))
                    @foreach($accounts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="form-group">
            <label>Received Date</label>
            <input type="date" name="received_date" class="form-control">
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

<script>
    $(document).on('submit', '#add_investor_form', function(e){
        e.preventDefault();
        var data = $(this).serializeArray();
        // ensure select2 fields included
        data.push({name: 'invoice_no', value: $('#add_invoice_no').val()});
        data.push({name: 'received_account_id', value: $('#add_received_account_id').val()});
        $.ajax({
            method: 'POST',
            url: '/account/investor',
            data: data,
            success: function(res){
                if(res.success){
                    $('#add_investor_modal').modal('hide');
                    toastr.success(res.msg);
                    $('#investor_table').DataTable().ajax.reload();
                    $('#add_investor_form')[0].reset();
                } else {
                    toastr.error('Unable to save');
                }
            },
            error: function(xhr){
                var msg = 'Error';
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            }
        });
    });
</script>

<!-- Add Return Modal -->
<div class="modal fade" id="add_return_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add Return</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="add_return_form">
      <div class="modal-body">
        <input type="hidden" name="investor_id" id="return_investor_id">
        <div class="form-group">
            <label>Return Amount*</label>
            <input type="number" step="0.01" name="return_amount" id="return_amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Return Date*</label>
            <input type="date" name="return_date" id="return_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>From Account</label>
            <select name="return_account_id" id="return_account_id" class="form-control select2">
                <option value="">Select account</option>
                @if(!empty($accounts))
                    @foreach($accounts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Return</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
    $(document).on('click', '.add-return-btn', function(){
        var id = $(this).data('id');
        // set hidden id
        $('#return_investor_id').val(id);
        // optionally prefill existing values from table row
        var row = $('#investor_table').DataTable().row($(this).closest('tr')).data();
        if(row.return_amount){
            $('#return_amount').val(row.return_amount);
            $('#return_date').val(row.return_date);
            $('#return_account_id').val(row.return_account_id).trigger('change');
        } else {
            $('#return_amount').val('');
            $('#return_date').val('');
        }
        $('#add_return_modal').modal('show');
    });

    $(document).on('submit', '#add_return_form', function(e){
        e.preventDefault();
        var id = $('#return_investor_id').val();
        var data = {
            return_amount: $('#return_amount').val(),
            return_date: $('#return_date').val()
        };
        // include return account
        data.return_account_id = $('#return_account_id').val();
        $.ajax({
            method: 'POST',
            url: '/account/investor/' + id + '/return',
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#add_return_modal').modal('hide');
                    toastr.success(res.msg);
                    $('#investor_table').DataTable().ajax.reload();
                } else {
                    toastr.error('Unable to save return info');
                }
            },
            error: function(xhr){
                var msg = 'Error';
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            }
        });
    });

    // initialize select2
    $(document).ready(function(){
        // Outside modals
        $('#investor_account_filter, #investor_payment_status').select2({ width: '100%' });

        // Inside Add Investor modal
        $('#add_invoice_no, #add_received_account_id').select2({
            width: '100%',
            dropdownParent: $('#add_investor_modal')
        });

        // Inside Edit Investor modal
        $('#edit_invoice_no, #edit_received_account_id').select2({
            width: '100%',
            dropdownParent: $('#edit_investor_modal')
        });

        // Inside Add Return modal
        $('#return_account_id').select2({
            width: '100%',
            dropdownParent: $('#add_return_modal')
        });
    });
</script>
@endsection
