@extends('layouts.app')
@section('title', __('Reports'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['Report', 'Produced By Report']" />
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('Produced By Report') }}</h5>
                        </div>
                        <div class="card-body p-4 row g-3">
                            <div class="col-md-3">
                                <label>Employee</label>
                                <select id="produced_by" class="form-control single-select-clear-field">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Report Type</label>
                                <select id="report_type" class="form-control">
                                    <option value="day">Day-wise</option>
                                    <option value="month">Month-wise</option>
                                    <option value="year">Year-wise</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>From Date</label>
                                <input type="date" id="from_date" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>To Date</label>
                                <input type="date" id="to_date" class="form-control">
                            </div>

                            <div class="col-md-12 mt-3">
                                <button id="generate_report" class="btn btn-primary">Generate Report</button>
                            </div>

                            <div class="col-md-12 mt-4">
                                <canvas id="productionChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
    </div>
    </div>
    <!-- Import Modals -->

@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script>
        document.getElementById('generate_report').addEventListener('click', function () {
            const employeeId = document.getElementById('produced_by').value;
            const reportType = document.getElementById('report_type').value;
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;

            // Simple validation
            if (!employeeId || !reportType || !fromDate || !toDate) {
                iziToast.error({
                    title: 'Error',
                    layout: 2,
                    message: 'Please select all fields: Employee, Report Type, From Date, and To Date.'
                });
                return;
            }

            if (fromDate > toDate) {
                iziToast.error({
                    title: 'Error',
                    layout: 2,
                    message: 'From Date cannot be later than To Date.'
                });
                return;
            }

            fetch(`{{ route('report.produced_by.data') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    produced_by: employeeId,
                    report_type: reportType,
                    from_date: fromDate,
                    to_date: toDate
                })
            })
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(item => item.date);
                    const values = data.map(item => item.total);

                    renderChart(labels, values);
                });
        });


        let chart;
        chart.register(ChartDataLabels);

        function renderChart(labels, data) {
            const ctx = document.getElementById('productionChart').getContext('2d');

            if (chart) chart.destroy();

            Chart.register(ChartDataLabels); // Register plugin

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Produced Quantity',
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        data: data,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            color: '#000',
                            font: {
                                weight: 'bold'
                            },
                            formatter: function (value) {
                                return value;
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels] // Include plugin
            });
        }
    </script>

@endsection