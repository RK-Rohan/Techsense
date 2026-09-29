@extends('layouts.investor')
@section('title', 'Change Password')

@section('content')
<section class="content">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-key"></i> Change Password</h3>
                    <a href="{{ route('investor.portal') }}" class="btn btn-default btn-sm pull-right">
                        <i class="fa fa-arrow-left"></i> Dashboard
                    </a>
                </div>
                <form method="POST" action="{{ url('/investor-portal/change-password') }}">
                    @csrf
                    <div class="box-body">
                        @if (session('password_status'))
                            <div class="alert alert-success">{{ session('password_status') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul style="margin:0; padding-left:18px;">
                                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required autocomplete="current-password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="6" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
