@extends('admin.index')

@section('content')
<div class="row">
    <div class="col-md-12">
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

                <h3 class="box-title">Order List</h3>
                <form action="{{ url('admin/orders') }}" method="GET" class="form-horizontal" style="margin-top: 10px;">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Order</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="reference_id" value="{{ Request::get('reference_id') }}" placeholder="Reference ID">
                            </div>

                            <label class="col-sm-1 control-label">Status</label>
                            <div class="col-sm-3">

                                <select class="form-control" name="status">
                                    <option value="All" @if (Request::get('status') == 'All') selected @endif>All</option>
                                    @foreach ($statuses as $status_key => $status_val)
                                        @if ($status_key == 'voided') @continue @endif
                                        <option value="{{ $status_val }}" 
                                        @if (Request::get('status') == $status_val) selected
                                        @elseif (Request::get('status') == '' && $status_key == 'pending_verification') selected 
                                        @endif>{{ trans("state.$status_key") }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Email</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="email" value="{{ Request::get('email') }}" placeholder="Email">
                            </div>

                            <label class="col-sm-1 control-label">Date</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="date" name="date" value="{{ Request::get('date') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Product</label>
                            <div class="col-sm-3">
                                <select class="form-control" name="product_category">
                                    <option value="" @if (Request::get('product_category') == '') selected @endif>All</option>
                                    @foreach ($product_categories as $category)
                                    <option value="{{ $category }}" @if (Request::get('product_category') == $category) selected @endif>{{ $category }}</option>
                                    @endforeach
                                </select>
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
                                <th>Order</th>
                                <th>Promo</th>
                                <th>Payment</th>
                                <th>Product</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody style="font-weight: 400">
                            @foreach ($orders as $order)
                            <tr>
                                <td>
                                    #{{ $order->reference_id }}<br>
                                    {{ 'Rp ' . number_format($order->order_amount, 0, '', '.') }}<br>
                                    {{ trans('state.' . array_search($order->status, $statuses)) }}

                                    @if ($order->status == $statuses['cancelled'])
                                    karena: <br>{{ $order->cancellation_reason }}
                                    @endif

                                    @if ($order->refund)
                                    <br>Refunded: {{ 'Rp ' . number_format($order->refund->amount, 0, '', '.') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($order->promo)
                                    {{ $order->promo->code }}<br>
                                    {{ 'Rp ' . number_format($order->discount_amount, 0, '', '.') }}
                                    @endif
                                </td>
                                <td>
                                    {{ 'Rp ' . number_format($order->payment_amount, 0, '', '.') }}<br>
                                    {{ $order->payment_method }}<br>

                                    @if ($order->bank_transfer)
                                        <table>
                                            <tr>
                                                <td width="55%">
                                                    {{ $order->bank_transfer->sender_bank->name }}<br>
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
                                    {{ $order->product->category }}<br>
                                    {{ $order->product->name }}<br>
                                    @if ($order->product->variant_name) {{ $order->product->variant_name }}<br>@endif
                                    {{ $order->customer_number }}
                                </td>
                                <td>
                                    {{ $order->user->first_name . ' ' . $order->user->last_name }}<br>
                                    {{ $order->user->email }}<br>
                                    {{ $order->user->phone_number }}
                                </td>
                                <td>{{ Carbon\Carbon::parse($order->created_at)->format('M d, Y | g.i A') }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#verify-order-{{ $order->id }}" @if ($order->status != $statuses['pending_verification']) disabled @endif>Verify</button><br>

                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#cancel-order-{{ $order->id }}" @if ($order->status == $statuses['completed'] || $order->status == $statuses['cancelled']) disabled @endif>Cancel</button><br>

                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#refund-order-{{ $order->id }}" @if ($order->status == $statuses['completed'] || $order->refund) disabled @endif>Refund</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
                    {{ $orders->links() }}
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

@foreach ($orders as $order)
<div class="modal fade" id="cancel-order-{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <form action="{{ url('/admin/orders/cancel') }}" method="POST" enctype="multipart/form-data">
            {!! csrf_field() !!}
            {{ method_field('POST') }}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Cancel Order</h4>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel this order ? <br><br>
                    <div class="form-group">
                        <input type="text" class="form-control" name="cancellation_reason" value="" placeholder="Reason">
                    </div>
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

@foreach ($orders as $order)
<div class="modal fade" id="refund-order-{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <form action="{{ url('/admin/orders/refund') }}" method="POST" enctype="multipart/form-data">
            {!! csrf_field() !!}
            {{ method_field('POST') }}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Refund Order</h4>
                </div>
                <div class="modal-body">
                    Are you sure you want to refund this order ? <br><br>
                    <div class="form-group">
                        <input type="number" class="form-control" name="refund_amount" value="" placeholder="Refund amount">
                    </div>
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Refund</button>
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

    $('#date').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
    });
});
</script>
@endsection