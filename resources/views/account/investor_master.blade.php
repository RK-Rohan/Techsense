@extends('layouts.app')
@section('title', 'Investors')

@section('content')
<section class="content-header">
    <h1>Investors<small>Master list</small></h1>
    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#add_investor_modal">
        <i class="fa fa-plus"></i> Add Investor
    </button>
    <div class="clearfix"></div>
    <br>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="investor_master_table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Name</th>
                                <th>NID/Passport</th>
                                <th>Phone Number</th>
                                <th>Total Investment</th>
                                <th>Total Pay</th>
                                <th>Total Due</th>
                                <th>Profit</th>
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
    <style>
        .table-responsive { overflow: visible; }
        .btn-group .dropdown-menu { position: absolute; right: 0; left: auto; }
        .dropdown-menu-right { right: 0; left: auto; }
        .dataTables_wrapper { overflow: visible; }
        .dropdown-menu { z-index: 1051; }
    </style>
@endsection

@section('javascript')
<script>
$(document).ready(function(){
    var table = $('#investor_master_table').DataTable({
        processing: true,
        serverSide: false,
        ajax: { url: '/account/investor-master-data' },
        columns: [
            { data: null, orderable:false, searchable:false, render: function(data, type, row){
                var html = ''+
                '<div class="btn-group">'+
                  '<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>'+
                  '<ul class="dropdown-menu dropdown-menu-right" role="menu">'+
                    '<li><a href="#" class="edit-investor-master-btn" data-id="'+row.id+'">Edit</a></li>'+
                    '<li><a href="#" class="delete-investor-master-btn" data-id="'+row.id+'">Delete</a></li>'+
                  '</ul>'+
                '</div>';
                return html;
            }},
            { data: 'name' },
            { data: 'nid' },
            { data: 'phone' },
            { data: 'total_investment', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'total_pay', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'total_due', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'profit', render: function(data){ return data ? $.fn.dataTable.render.number(',', '.', 2).display(data) : ''; } }
        ]
    });

    // Add submit
    $(document).on('submit', '#add_investor_master_form', function(e){
        e.preventDefault();
        $.ajax({
            method: 'POST',
            url: '/account/investor-master',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#add_investor_modal').modal('hide');
                    toastr.success(res.msg);
                    table.ajax.reload();
                    $('#add_investor_master_form')[0].reset();
                } else { toastr.error('Unable to add'); }
            },
            error: function(xhr){ toastr.error('Error adding'); }
        });
    });

    // Open edit
    $(document).on('click', '.edit-investor-master-btn', function(){
        var row = $('#investor_master_table').DataTable().row($(this).closest('tr')).data();
        if(!row) return;
        $('#edit_master_id').val(row.id);
        $('#edit_master_name').val(row.name);
        $('#edit_master_nid').val(row.nid);
        $('#edit_master_phone').val(row.phone);
        $('#edit_investor_master_modal').modal('show');
    });

    // Update submit
    $(document).on('submit', '#edit_investor_master_form', function(e){
        e.preventDefault();
        var id = $('#edit_master_id').val();
        $.ajax({
            method: 'PUT',
            url: '/account/investor-master/' + id,
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#edit_investor_master_modal').modal('hide');
                    toastr.success(res.msg);
                    table.ajax.reload();
                } else { toastr.error('Unable to update'); }
            },
            error: function(){ toastr.error('Error updating'); }
        });
    });

    // Delete
    $(document).on('click', '.delete-investor-master-btn', function(){
        var row = $('#investor_master_table').DataTable().row($(this).closest('tr')).data();
        if(!row) return;
        if(!confirm('Are you sure you want to delete this investor?')) return;
        $.ajax({
            method: 'DELETE',
            url: '/account/investor/' + row.id,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){ toastr.success(res.msg); table.ajax.reload(); }
                else { toastr.error('Unable to delete'); }
            },
            error: function(){ toastr.error('Error deleting investor'); }
        });
    });
});
</script>

<!-- Add Investor Modal -->
<div class="modal fade" id="add_investor_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add Investor</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="add_investor_master_form">
      <div class="modal-body">
        <div class="form-group">
            <label>Name*</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>NID/Passport</label>
            <input type="text" name="nid" class="form-control">
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" class="form-control">
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

<!-- Edit Investor Modal -->
<div class="modal fade" id="edit_investor_master_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Investor</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="edit_investor_master_form">
      <div class="modal-body">
        <input type="hidden" id="edit_master_id" name="id">
        <div class="form-group">
            <label>Name*</label>
            <input type="text" name="name" id="edit_master_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>NID/Passport</label>
            <input type="text" name="nid" id="edit_master_nid" class="form-control">
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" id="edit_master_phone" class="form-control">
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
