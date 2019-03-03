@extends('admin.index')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">User List</h3>
                <form action="{{ url('admin/products') }}" method="GET" class="form-horizontal" style="margin-top: 10px;">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Category</label>
                            <div class="col-sm-3">
                                <select class="form-control" name="category">
                                    @foreach($categories as $category)
                                    <option value="{{ $category }}" @if (Request::get('category') == $category) selected @endif>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="col-sm-1 control-label">Type</label>
                            <div class="col-sm-3">
                                <select class="form-control" name="type">
                                    <option value="All" @if (Request::get('type') == 'All') selected @endif>All</option>
                                    <option value="Postpaid" @if (Request::get('type') == 'Postpaid') selected @endif>Postpaid</option>
                                    <option value="Prepaid" @if (Request::get('type') == 'Prepaid') selected @endif>Prepaid</option>
                                </select>
                            </div>
                        </div>
                        <a href="{{ url('/admin/products') }}"><button type="button" class="btn btn-primary">Clear</button></a>
                        <button type="submit" class="btn btn-success">Search</button>
                    </div>
                </form>
            </div>
            <div class="box-body">
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Variant Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Image</th>
                            </tr>
                        </thead>
                        <tbody style="font-weight: 400">
                            @foreach ($products as $product)
                            <tr>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->variant_name }}</td>
                                <td>{{ $product->category }}</td>
                                <td>{{ $product->type }}</td>
                                <td>
                                    <form action="{{ url('admin/products/'.$product->code.'/status') }}" method="POST">
                                        {!! csrf_field() !!}
                                        {{ method_field('PATCH') }}
                                        <input type="checkbox" name="status" class="status" data-size="mini" @if ($product->status > 0) checked @endif ><br>
                                    </form>
                                </td>
                                <td>
                                    <img src="{{ asset($product->image()) }}" style="height: 25px"/>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
                {{ $products->links() }}
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

    $('.pagination').addClass('pagination-sm no-margin pull-right');
});
</script>
@endsection