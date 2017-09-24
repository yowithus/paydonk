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

                <form action="{{ url('admin/topup-orders') }}" method="GET" class="form-horizontal" style="margin-top: 10px;">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Order</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="reference_id" value="{{ Request::get('reference_id') }}" placeholder="Reference ID">
                            </div>

                            <label class="col-sm-2 control-label">Order Status</label>
                            <div class="col-sm-2">
                                <select class="form-control" name="order_status">
                                    <option value="0" @if (Request::get('order_status') == '0' || Request::get('order_status') == '') selected @endif>Pending</option>
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
                                    <option value="0" @if (Request::get('payment_status') == '0') selected @endif>Pending</option>
                                    <option value="1" @if (Request::get('payment_status') == '1' || Request::get('payment_status') == '') selected @endif>Success</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Date</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="order-date" name="order_date" value="{{ Request::get('order_date') }}">
                            </div>
                        </div>
                        <a href="{{ url('/admin/topup-orders') }}"><button type="button" class="btn btn-primary">Clear</button></a>
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
                                <th width="11%">Payment</th>
                                <th width="15%">Sender</th>
                                <th width="15%">Recipient</th>
                                <th width="20%">User</th>
                                <th width="15%">Date</th>
                                <th width="12%">Action</th>
                            </tr>
                        </thead>
                        <tbody style="font-weight: 400">
                            @foreach ($topup_orders as $topup_order)
                            <tr>
                                <td>{{ $topup_order->id }}</td>
                                <td>
                                    #{{ $topup_order->reference_id }}<br>
                                    {{ 'Rp ' . number_format($topup_order->order_amount) }}<br>
                                    @if ($topup_order->order_status == 0)
                                        Pending
                                    @else
                                        Success
                                    @endif
                                </td>
                                <td>
                                    {{ $topup_order->payment_method }}<br>
                                    @if ($topup_order->payment_status == 0)
                                        Pending
                                    @else
                                        Success
                                    @endif
                                </td>
                                <td>
                                    @if ($topup_order->bank_transfer)
                                        {{ $topup_order->bank_transfer->sender_bank_name }}<br>
                                        {{ $topup_order->bank_transfer->sender_account_name }}<br>
                                        {{ $topup_order->bank_transfer->sender_account_number }}
                                    @endif
                                </td>
                                <td>
                                    @if ($topup_order->bank_transfer)
                                        {{ $topup_order->bank_transfer->recipient_bank->name }}<br>
                                        {{ $topup_order->bank_transfer->recipient_bank->account_name }}<br>
                                        {{ $topup_order->bank_transfer->recipient_bank->account_number }}
                                    @endif
                                </td>
                                <td>
                                    {{ $topup_order->user->first_name . ' ' . $topup_order->user->last_name }}<br>
                                    {{ $topup_order->user->email }}<br>
                                    {{ $topup_order->user->phone_number }}
                                </td>
                                <td>{{ Carbon\Carbon::parse($topup_order->created_at)->format('M d, Y | g.i A') }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#verify-topup-order-{{ $topup_order->id }}" @if ($topup_order->order_status == 1) disabled @endif>Verify</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
                    {{ $topup_orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@foreach ($topup_orders as $topup_order)
<div class="modal fade" id="verify-topup-order-{{ $topup_order->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <form action="{{ url('/admin/topup-orders/verify') }}" method="POST" enctype="multipart/form-data">
            {!! csrf_field() !!}
            {{ method_field('POST') }}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Verify Top Up Order</h4>
                </div>
                <div class="modal-body">
                    Are you sure you want to verify this top up order ?
                    <input type="hidden" name="topup_order_id" value="{{ $topup_order->id }}">
                    <input type="hidden" name="user_id" value="{{ $topup_order->user_id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary @if ($topup_order->order_status == 1) disabled @endif">Verify</button>
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

    $('#order-date').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
    });
});
</script>
@endsection