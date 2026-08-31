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
                                <th>Login</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>

<!-- Manage Login Modal -->
<div class="modal fade" id="manage_login_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Manage Login &mdash; <span id="login_investor_name"></span></h4>
      </div>
      <form id="manage_login_form">
      <div class="modal-body">
        <input type="hidden" id="login_investor_id">
        <div class="form-group">
            <label>Username*</label>
            <input type="text" name="username" id="login_username" class="form-control" required minlength="4" autocomplete="off">
            <small class="text-muted">The investor signs in with this. Phone number works well.</small>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="text" name="password" id="login_password" class="form-control" minlength="6" autocomplete="new-password">
            <small class="text-muted" id="login_password_help"></small>
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="allow_login" id="login_allow" value="1"> Login enabled
            </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pull-left" id="remove_login_btn" style="display:none;">Remove Login</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="reset_password_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Reset Password &mdash; <span id="reset_investor_name"></span></h4>
      </div>
      <form id="reset_password_form">
      <div class="modal-body">
        <input type="hidden" id="reset_investor_id">
        <input type="hidden" id="reset_username">
        <input type="hidden" id="reset_allow">
        <div class="form-group">
            <label>New Password*</label>
            <input type="text" id="reset_password" class="form-control" required minlength="6" autocomplete="new-password">
            <small class="text-muted">Minimum 6 characters. Share it with the investor.</small>
        </div>
        <div class="form-group">
            <label>Confirm Password*</label>
            <input type="text" id="reset_password_confirm" class="form-control" required minlength="6" autocomplete="new-password">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Reset Password</button>
      </div>
      </form>
    </div>
  </div>
</div>
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
                    '<li><a href="#" class="manage-login-btn" data-id="'+row.id+'" data-name="'+row.name+'">Manage Login</a></li>'+
                    (row.has_login ?
                      '<li><a href="#" class="reset-password-btn" data-id="'+row.id+'" data-name="'+row.name+'">Reset Password</a></li>' : '')+
                    '<li class="divider"></li>'+
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
            { data: 'profit', render: function(data){ return data ? $.fn.dataTable.render.number(',', '.', 2).display(data) : ''; } },
            { data: null, orderable:false, render: function(data, type, row){
                if(!row.has_login){
                    return '<span class="label label-default">No login</span>';
                }
                var cls = row.login_active ? 'label-success' : 'label-warning';
                var txt = row.login_active ? 'Active' : 'Disabled';
                return '<span class="label '+cls+'">'+txt+'</span><br><small>'+(row.login_username || '')+'</small>';
            }}
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
        $('#edit_master_address').val(row.address);
        $('#edit_master_ec_name').val(row.emergency_contact_name);
        $('#edit_master_ec_number').val(row.emergency_contact_number);
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

    // Open Manage Login
    $(document).on('click', '.manage-login-btn', function(){
        var id = $(this).data('id');
        var name = $(this).data('name');
        $.ajax({
            method: 'GET',
            url: '/account/investor/' + id + '/login',
            success: function(res){
                if(!res.success) { toastr.error('Unable to load login'); return; }
                var d = res.data;
                $('#login_investor_id').val(d.investor_id);
                $('#login_investor_name').text(name);
                $('#login_username').val(d.username || '');
                $('#login_password').val('');
                $('#login_allow').prop('checked', d.has_login ? (d.allow_login == 1) : true);
                // Password is mandatory only while creating the account.
                $('#login_password').prop('required', !d.has_login);
                $('#login_password_help').text(d.has_login
                    ? 'Leave blank to keep the current password.'
                    : 'Required. Minimum 6 characters.');
                $('#remove_login_btn').toggle(!!d.has_login);
                $('#manage_login_modal').modal('show');
            },
            error: function(){ toastr.error('Error loading login'); }
        });
    });

    // Save Manage Login
    $(document).on('submit', '#manage_login_form', function(e){
        e.preventDefault();
        var id = $('#login_investor_id').val();
        $.ajax({
            method: 'POST',
            url: '/account/investor/' + id + '/login',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#manage_login_modal').modal('hide');
                    toastr.success(res.msg);
                    table.ajax.reload();
                } else { toastr.error(res.msg || 'Unable to save login'); }
            },
            error: function(xhr){
                if(xhr.status === 422){
                    var errors = xhr.responseJSON.errors || {};
                    var first = Object.keys(errors)[0];
                    toastr.error(first ? errors[first][0] : 'Validation failed');
                } else { toastr.error('Error saving login'); }
            }
        });
    });

    // Remove login
    $(document).on('click', '#remove_login_btn', function(){
        var id = $('#login_investor_id').val();
        if(!confirm('Remove login access for this investor?')) return;
        $.ajax({
            method: 'DELETE',
            url: '/account/investor/' + id + '/login',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#manage_login_modal').modal('hide');
                    toastr.success(res.msg);
                    table.ajax.reload();
                } else { toastr.error('Unable to remove login'); }
            },
            error: function(){ toastr.error('Error removing login'); }
        });
    });

    // Open Reset Password
    $(document).on('click', '.reset-password-btn', function(){
        var id = $(this).data('id');
        var name = $(this).data('name');
        $.ajax({
            method: 'GET',
            url: '/account/investor/' + id + '/login',
            success: function(res){
                if(!res.success || !res.data.has_login){
                    toastr.error('This investor has no login yet. Use Manage Login first.');
                    return;
                }
                $('#reset_investor_id').val(res.data.investor_id);
                // Username is carried through unchanged; only the password changes.
                $('#reset_username').val(res.data.username);
                $('#reset_allow').val(res.data.allow_login);
                $('#reset_investor_name').text(name);
                $('#reset_password').val('');
                $('#reset_password_confirm').val('');
                $('#reset_password_modal').modal('show');
            },
            error: function(){ toastr.error('Error loading login'); }
        });
    });

    // Save Reset Password
    $(document).on('submit', '#reset_password_form', function(e){
        e.preventDefault();
        var id = $('#reset_investor_id').val();
        var pwd = $('#reset_password').val();
        if(pwd !== $('#reset_password_confirm').val()){
            toastr.error('Passwords do not match');
            return;
        }
        $.ajax({
            method: 'POST',
            url: '/account/investor/' + id + '/login',
            data: {
                username: $('#reset_username').val(),
                password: pwd,
                allow_login: $('#reset_allow').val()
            },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                if(res.success){
                    $('#reset_password_modal').modal('hide');
                    toastr.success('Password reset');
                    table.ajax.reload();
                } else { toastr.error(res.msg || 'Unable to reset password'); }
            },
            error: function(xhr){
                if(xhr.status === 422){
                    var errors = xhr.responseJSON.errors || {};
                    var first = Object.keys(errors)[0];
                    toastr.error(first ? errors[first][0] : 'Validation failed');
                } else { toastr.error('Error resetting password'); }
            }
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
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label>Emergency Contact Name</label>
            <input type="text" name="emergency_contact_name" class="form-control">
        </div>
        <div class="form-group">
            <label>Emergency Contact Number</label>
            <input type="text" name="emergency_contact_number" class="form-control">
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
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" id="edit_master_address" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label>Emergency Contact Name</label>
            <input type="text" name="emergency_contact_name" id="edit_master_ec_name" class="form-control">
        </div>
        <div class="form-group">
            <label>Emergency Contact Number</label>
            <input type="text" name="emergency_contact_number" id="edit_master_ec_number" class="form-control">
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
