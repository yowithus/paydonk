@extends('admin.index')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Recipient Bank List</h3>
            </div>
            <div class="box-body">
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th>Image</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody style="font-weight: 400">
                            @foreach ($recipient_banks as $recipient_bank)
                            <tr>
                                <td>{{ $recipient_bank->id }}</td>
                                <td>{{ $recipient_bank->name }}</td>
                                <td>{{ $recipient_bank->account_name }}</td>
                                <td>{{ $recipient_bank->account_number }}</td>
                                <td>
                                    <img src="{{ asset('/images/banks/'.$recipient_bank->image_name) }}" style="height: 20px"/>
                                </td>
                                <td>
                                    <form action="{{ url('admin/recipient-banks/'.$recipient_bank->id.'/status') }}" method="POST">
                                        {!! csrf_field() !!}
                                        {{ method_field('PATCH') }}
                                        <input type="checkbox" name="status" class="status" data-size="mini" @if ($recipient_bank->status > 0) checked @endif ><br>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    $('.status').bootstrapSwitch();
    $('.status').on('switchChange.bootstrapSwitch', function(event, state) {
        $(this).closest('form').submit();
    });
});
</script>
@endsection