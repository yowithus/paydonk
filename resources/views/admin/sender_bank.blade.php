@extends('admin.index')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Sender Bank List</h3>
            </div>
            <div class="box-body">
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody style="font-weight: 400">
                            @foreach ($sender_banks as $sender_bank)
                            <tr>
                                <td>{{ $sender_bank->id }}</td>
                                <td>{{ $sender_bank->name }}</td>
                                <td>
                                    <img src="{{ asset('/images/banks/'.$sender_bank->image_name) }}" style="height: 20px"/>
                                </td>
                                <td>
                                    <form action="{{ url('admin/sender-banks/'.$sender_bank->id.'/status') }}" method="POST">
                                        {!! csrf_field() !!}
                                        {{ method_field('PATCH') }}
                                        <input type="checkbox" name="status" class="status" data-size="mini" @if ($sender_bank->status > 0) checked @endif ><br>
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