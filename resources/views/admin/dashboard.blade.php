@extends('admin.index')

@section('content')
<div class='row'>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-child"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">New Users</span>
                <span class="info-box-number">5</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-rocket"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Recent Events</span>
                <span class="info-box-number">5</span>
            </div>
        </div>
    </div>

    <div class="clearfix visible-sm-block"></div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-shopping-cart"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Orders Success</span>
                <span class="info-box-number">5</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-hand-peace-o"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Tickets Sold</span>
                <span class="info-box-number">5</span>
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
                    <div class="col-md-8">
                        <p class="text-center">
                            <strong>Sales: 5</strong>
                        </p>
                        <div class="chart">
                            <canvas id="salesChart" style="height: 180px;"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <p class="text-center">
                            <strong>Goal Completion</strong>
                        </p>

                        <div class="progress-group">
                            <span class="progress-text">Acquire New Users</span>
                            <span class="progress-number"><b>5</b>/20</span>

                            <div class="progress sm">
                                <div class="progress-bar progress-bar-aqua" style="width: 20%"></div>
                            </div>
                        </div>
                        <div class="progress-group">
                            <span class="progress-text">Collect Events</span>
                            <span class="progress-number"><b>5</b>/20</span>

                            <div class="progress sm">
                                <div class="progress-bar progress-bar-red" style="width: 20%"></div>
                            </div>
                        </div>
                        <div class="progress-group">
                            <span class="progress-text">Complete Purchase</span>
                            <span class="progress-number"><b>5</b>/20</span>

                            <div class="progress sm">
                                <div class="progress-bar progress-bar-green" style="width: 20%"></div>
                            </div>
                        </div>
                        <div class="progress-group">
                            <span class="progress-text">Sell Tickets</span>
                            <span class="progress-number"><b>5</b>/20</span>

                            <div class="progress sm">
                                <div class="progress-bar progress-bar-yellow" style="width: 20%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <div class="row">
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span>
                            <h5 class="description-header" id="total-revenue"></h5>
                            <span class="description-text">TOTAL REVENUE</span>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>
                            <h5 class="description-header" id="total-cost"></h5>
                            <span class="description-text">TOTAL COST</span>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 20%</span>
                            <h5 class="description-header" id="total-profit"></h5>
                            <span class="description-text">TOTAL PROFIT</span>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="description-block">
                            <span class="description-percentage text-red"><i class="fa fa-caret-down"></i> 18%</span>
                            <h5 class="description-header">50</h5>
                            <span class="description-text">GOAL COMPLETIONS</span>
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
        url: '/admin/statistic',
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
                label: "Digital Goods",
                    fillColor: "rgba(60,141,188,0.9)",
                    strokeColor: "rgba(60,141,188,0.8)",
                    pointColor: "#3b8bba",
                    pointStrokeColor: "rgba(60,141,188,1)",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(60,141,188,1)",
                    data: sales
                }]
            };

            var salesChartOptions = {
                showScale: true,
                scaleShowGridLines: false,
                scaleGridLineColor: "rgba(0,0,0,.05)",
                scaleGridLineWidth: 1,
                scaleShowHorizontalLines: true,
                scaleShowVerticalLines: true,
                bezierCurve: true,
                bezierCurveTension: 0.3,
                pointDot: false,
                pointDotRadius: 4,
                pointDotStrokeWidth: 1,
                pointHitDetectionRadius: 20,
                datasetStroke: true,
                datasetStrokeWidth: 2,
                datasetFill: true,
                legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%=datasets[i].label%></li><%}%></ul>",
                maintainAspectRatio: true,
                responsive: true
            };

            salesChart.Line(salesChartData, salesChartOptions);

            $('#total-revenue').text('Rp '+result.total_revenue.toLocaleString());
            $('#total-cost').text('Rp '+result.total_cost.toLocaleString());
            $('#total-profit').text('Rp '+result.total_profit.toLocaleString());
        }
    });

});
</script>
@endsection