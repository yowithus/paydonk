@extends('admin.index')

@section('content')
<div class='row'>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-child"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">New Users</span>
                <span class="info-box-number">{{ $users_count }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-rocket"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Top Ups</span>
                <span class="info-box-number">{{ $topup_orders_count }}</span>
            </div>
        </div>
    </div>

    <div class="clearfix visible-sm-block"></div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-shopping-cart"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Orders Success</span>
                <span class="info-box-number">{{ $orders_count }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-hand-peace-o"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">GMV</span>
                <span class="info-box-number">Rp {{ number_format($total_revenue, 0, '', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Monthly Recap Report</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-wrench"></i>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#">Action</a></li>
                            <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Separated link</a></li>
                         </ul>
                    </div>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart">
                            <canvas id="salesChart" style="height: 200px;"></canvas>
                        </div>
                        <div class="chart">
                            <canvas id="salesChartCategory" style="height: 200px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('plugins/adminlte/plugins/chartjs/Chart.min.js') }}"></script>
<script>
$(function () {

    $.ajax({
        url: '/admin/monthly-statistic',
        type: 'GET',
        success: function(result) {
            var dates = [];
            var sales = [];

            var start_date  = moment().subtract(30, 'days');
            var end_date    = moment();

            while (start_date <= end_date) {
                start_date_s = start_date.format('YYYY-MM-DD');
                start_date_e = start_date.format('DD MMM');

                dates.push(start_date_e);
                sales.push((result.sales[start_date_s]) ? parseInt(result.sales[start_date_s]) : 0);

                start_date.add(1, 'days');
            }

            var salesChartCanvas = $("#salesChart").get(0).getContext("2d");
            var salesChart = new Chart(salesChartCanvas);

            var salesChartData = {
                labels: dates,
                datasets: [{
                    label: 'Digital Products',
                    data: sales,
                    fillColor: '#00a65a'
                }]
            };

            var salesChartOptions = {
                showScale: true,
                pointDot: false,
                responsive: true
            };

            salesChart.Line(salesChartData, salesChartOptions);
        }
    });

    $.ajax({
        url: '/admin/category-statistic',
        type: 'GET',
        success: function(result) {
            var categories = result.categories;
            var sales = [];

            for (category of categories) {
                sales.push(result.sales[category]);
            }

            var salesChartCanvas = $('#salesChartCategory').get(0).getContext('2d');
            var salesChart = new Chart(salesChartCanvas);

            var salesChartData = {
                labels: categories,
                datasets: [{
                    label: 'Digital Products',
                    data: sales,
                    fillColor: [
                        '#f56954',
                        '#00a65a',
                        '#f39c12',
                        '#00c0ef',
                        '#3c8dbc',
                        '#d2d6de'
                    ]
                }]
            };

            var salesChartOptions = {
                showScale: true,
                scaleShowGridLines: false,
                responsive: true
            };

            salesChart.Bar(salesChartData, salesChartOptions);
        }
    });

});
</script>
@endsection