@extends('admin.index')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <h3 class="box-title">User List</h3>
                <form action="{{ url('admin/users') }}" method="GET" class="form-horizontal" style="margin-top: 10px;">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Email</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="email" value="{{ Request::get('email') }}" placeholder="Email">
                            </div>

                            <label class="col-sm-1 control-label">Phone</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="phone_number" value="{{ Request::get('phone_number') }}" placeholder="Phone Number">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Date</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="join-date" name="join_date" value="{{ Request::get('join_date') }}" required>
                            </div>
                        </div>
                        <a href="{{ url('/admin/users') }}"><button type="button" class="btn btn-primary">Clear</button></a>
                        <button type="submit" class="btn btn-success">Search</button>
                    </div>
                </form>
            </div>
            <div class="box-body">
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Balance</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody style="font-weight: 400">
                            @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->first_name }}</td>
                                <td>{{ $user->last_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone_number }}</td>
                                <td>{{ 'Rp '.number_format($user->balance, 0, '', '.') }}</td>
                                <td>{{ $user->role }}</td>
                                <td>
                                    <form action="{{ url('admin/users/'.$user->id.'/status') }}" method="POST">
                                        {!! csrf_field() !!}
                                        {{ method_field('PATCH') }}
                                        <input type="checkbox" name="status" class="status" data-size="mini" @if ($user->status > 0) checked @endif ><br>
                                    </form>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#edit-user-{{ $user->id }}"><i class="fa fa-edit text-blue"></i></button>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#add-balance-{{ $user->id }}"><i class="fa fa fa-money text-warning"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
                {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@foreach ($users as $user)
<div id="edit-user-{{ $user->id }}" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit user</h4>
            </div>
            <div class="modal-body">
                <form action="{{ url('admin/users/'.$user->id) }}" method="POST">
                    {!! csrf_field() !!}
                    {{ method_field('PATCH') }}
                    <div class="form-group">
                        <label for="first_name">First name:</label>
                        <input type="text" class="form-control" name="first_name" value="{{ $user->first_name }}">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last name:</label>
                        <input type="text" class="form-control" name="last_name" value="{{ $user->last_name }}">
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" name="email" value="{{ $user->email }}">
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone number:</label>
                        <input type="text" class="form-control" name="phone_number" value="{{ $user->phone_number }}">
                    </div>
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select class="form-control" name="role">
                            <option value="User" @if ($user->role == 'User') selected @endif>User</option>
                            <option value="Admin" @if ($user->role == 'Admin') selected @endif>Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@foreach ($users as $user)
<div id="add-balance-{{ $user->id }}" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add balance</h4>
            </div>
            <div class="modal-body">
                <form action="{{ url('admin/users/'.$user->id.'/add-balance') }}" method="POST">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label for="amount">Balance:</label>
                        <input type="number" class="form-control" name="amount" value="0">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('scripts')
<script>
$(function () {
    $('.status').bootstrapSwitch();
    $('.status').on('switchChange.bootstrapSwitch', function(event, state) {
        $(this).closest('form').submit();
    });

    $('.pagination').addClass('pagination-sm no-margin pull-right');

    $('#join-date').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
    });
});
</script>
@endsection