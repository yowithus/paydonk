@extends('admin.index')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Balance Details</h3>
                <form action="{{ url('admin/balance-details') }}" method="GET" class="form-horizontal" style="margin-top: 10px;">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Email</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="email" value="{{ Request::get('email') }}" placeholder="Email">
                            </div>

                            <label class="col-sm-2 control-label">Phone Number</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="phone_number" value="{{ Request::get('phone_number') }}" placeholder="Phone Number">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Date</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="date" name="date" value="{{ Request::get('date') }}" required>
                            </div>
                        </div>
                        <a href="{{ url('/admin/balance-details') }}"><button type="button" class="btn btn-primary">Clear</button></a>
                        <button type="submit" class="btn btn-success">Search</button>
                    </div>
                </form>
            </div>
            <div class="box-body">
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Order</th>
                                <th>Previous Amount</th>
                                <th>Current Amount</th>
                                <th>User</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody style="font-weight: 400">
                            @foreach ($balance_details as $balance_detail)
                            <tr>
                                <td>{{ $balance_detail->id }}</td>
                                <td>{{ $balance_detail->type }}</td>
                                <td>
                                    #{{ $balance_detail->order->reference_id }}
                                    <br>
                                    {{ 'Rp '.number_format($balance_detail->amount) }}
                                </td>
                                <td>{{ 'Rp '.number_format($balance_detail->previous_amount) }}</td>
                                <td>{{ 'Rp '.number_format($balance_detail->current_amount) }}</td>
                                <td>
                                    {{ $balance_detail->user->first_name . ' ' . $balance_detail->user->last_name }}<br>
                                    {{ $balance_detail->user->email }}<br>
                                    {{ $balance_detail->user->phone_number }}
                                </td>
                                <td>{{ Carbon\Carbon::parse($balance_detail->created_at)->format('M d, Y | g.i A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
                    {{ $balance_details->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
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