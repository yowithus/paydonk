@extends('admin.index')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Order List</h3>

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ url('admin/orders') }}" method="GET" class="form-horizontal" style="margin-top: 10px;">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Order</label>
                            <div class="col-sm-2">
                                <input type="number" class="form-control" name="order_id" value="{{ Request::get('order_id') }}" placeholder="Order ID">
                            </div>

                            <label class="col-sm-2 control-label">Order Status</label>
                            <div class="col-sm-2">
                                <select class="form-control" name="order_status">
                                    <option value="all" @if (Request::get('order_status') == 'all') selected @endif>All</option>
                                    <option value="0" @if (Request::get('order_status') == '0') selected @endif>Pending</option>
                                    <option value="1" @if (Request::get('order_status') == '1') selected @endif>Success</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Email</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="email" value="{{ Request::get('email') }}" placeholder="Email">
                            </div>

                            <label class="col-sm-2 control-label">Payment Status</label>
                            <div class="col-sm-2">
                                <select class="form-control" name="payment_status">
                                    <option value="all" @if (Request::get('payment_status') == 'all') selected @endif>All</option>
                                    <option value="0" @if (Request::get('payment_status') == '0') selected @endif>Pending</option>
                                    <option value="1" @if (Request::get('payment_status') == '1') selected @endif>Success</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Date</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control order-time" name="order_date" value="{{ Request::get('order_date') }}" required>
                            </div>
                        </div>
                        <a href="{{ url('/admin/orders') }}"><button type="button" class="btn btn-primary">Clear</button></a>
                        <button type="submit" class="btn btn-success">Search</button>
                    </div>
                </form>
            </div>
            <div class="box-body">
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="11%">Order</th>
                                <th width="25%">Payment</th>
                                <th width="15%">Product</th>
                                <th width="15%">User</th>
                                <th width="15%">Date</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody style="font-weight: 400">
                            @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>
                                    {{ $order->reference_id }}<br>
                                    {{ 'Rp ' . number_format($order->order_amount) }}<br>
                                    @if ($order->order_status == 0)
                                        Pending
                                    @else
                                        Success
                                    @endif
                                </td>
                                <td>
                                    {{ $order->payment_method }}<br>
                                    @if ($order->payment_status == 0)
                                        Pending
                                    @else
                                        Success
                                    @endif
                                    <br>

                                    @if ($order->bank_transfer)
                                        <table>
                                            <tr>
                                                <td width="55%">
                                                    {{ $order->bank_transfer->sender_bank_name }}<br>
                                                    {{ $order->bank_transfer->sender_account_name }}<br>
                                                    {{ $order->bank_transfer->sender_account_number }}
                                                </td>
                                                <td>
                                                    {{ $order->bank_transfer->recipient_bank->name }}<br>
                                                    {{ $order->bank_transfer->recipient_bank->account_name }}<br>
                                                    {{ $order->bank_transfer->recipient_bank->account_number }}
                                                </td>
                                            </tr>
                                        </table>
                                    @endif
                                </td>
                                <td>
                                    {{ $order->product->name }}<br>
                                    {{ $order->customer_number }}
                                </td>
                                <td>
                                    {{ $order->user->first_name . ' ' . $order->user->last_name }}<br>
                                    {{ $order->user->email }}<br>
                                    {{ $order->user->phone_number }}
                                </td>
                                <td>{{ Carbon\Carbon::parse($order->created_at)->format('M d, Y | g.i A') }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm @if ($order->order_status == 1) disabled @endif" data-toggle="modal" data-target="#verify-order-{{ $order->id }}">Verify</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
    
                </div>
            </div>
        </div>
    </div>
</div>

@foreach ($orders as $order)
<div class="modal fade" id="verify-order-{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <form action="{{ url('/admin/orders/verify') }}" method="POST" enctype="multipart/form-data">
            {!! csrf_field() !!}
            {{ method_field('POST') }}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Verify Order</h4>
                </div>
                <div class="modal-body">
                    Are you sure you want to verify this order ?
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <input type="hidden" name="user_id" value="{{ $order->user_id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary @if ($order->order_status == 1) disabled @endif">Verify</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection

@section('scripts')
<script>
$(function () {
    $('.pagination').addClass('pagination-sm no-margin pull-right');
});
</script>
@endsection