@extends('admin.index')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Deposit Details</h3>
                <div class="box-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped">
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
                        <tbody>
                            @foreach ($deposit_details as $deposit_detail)
                            <tr>
                                <td>{{ $deposit_detail->id }}</td>
                                <td>{{ $deposit_detail->type }}</td>
                                <td>
                                    {{ $deposit_detail->topup_order->reference_id }}<br>
                                    {{ 'Rp '.number_format($deposit_detail->amount) }}
                                </td>
                                <td>{{ 'Rp '.number_format($deposit_detail->previous_amount) }}</td>
                                <td>{{ 'Rp '.number_format($deposit_detail->current_amount) }}</td>
                                <td>
                                    {{ $deposit_detail->user->first_name . ' ' . $deposit_detail->user->last_name }}<br>
                                    {{ $deposit_detail->user->email }}<br>
                                    {{ $deposit_detail->user->phone_number }}
                                </td>
                                <td>{{ Carbon\Carbon::parse($deposit_detail->created_at)->format('M d, Y | g.i A') }}</td>
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
@endsection

@section('scripts')
<script>
$(function () {
    // status switcher
    $(".status").bootstrapSwitch();
    $(".status").on('switchChange.bootstrapSwitch', function(event, state) {
        $(this).closest('form').submit();
    });
});
</script>
@endsection