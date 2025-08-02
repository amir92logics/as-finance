
@if(basicControl()->ecommerce)
    <div class="card mb-3 mb-lg-5">
        <div class="card-header card-header-content-sm-between">
            <h4 class="card-header-title mb-2 mb-sm-0">@lang('Product Sales') <i class="bi-question-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Net sales (gross sales minus discounts and returns) plus taxes and shipping. Includes orders from all sales channels."></i></h4>
            <div class="d-grid d-sm-flex gap-2">

                <button id="js-daterangepicker-predefined" class="btn btn-white btn-sm dropdown-toggle">
                    <i class="bi-calendar-week"></i>
                    <span class="js-daterangepicker-predefined-preview ms-1"></span>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row col-lg-divider" id="salesRevenueChart" style="max-height: 350px">
                <div class="col-lg-9 mb-5 mb-lg-0">
                    <div class="chartjs-custom mb-4" style="max-height: 350px">
                        <canvas id="ecommerce-sales-revenue" style="max-height: 350px"></canvas>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="row">
                        <div class="col-sm-6 col-lg-12">
                            <div class="d-flex justify-content-center flex-column" style="min-height: 9rem;">
                                <h6 class="card-subtitle">Revenue</h6>
                                <span class="d-block display-4 text-dark mb-1 me-3 revenueData">{{ currencyPosition(0) }}</span>
                                <span class="d-block text-success" id="revenuePercent"></span>
                            </div>

                            <hr class="d-none d-lg-block my-0">
                        </div>

                        <div class="col-sm-6 col-lg-12">
                            <div class="d-flex justify-content-center flex-column" style="min-height: 9rem;">
                                <h6 class="card-subtitle">Orders</h6>
                                <span class="d-block display-4 text-dark mb-1 me-3 orderData">{{ 0 }}</span>
                                <span class="d-block text-danger" id="orderPercent"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('css-lib')
        <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/global/css/daterangepicker.css') }}">
    @endpush

    @push('js-lib')
        <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
        <script src="{{ asset('assets/global/js/daterangepicker.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    @endpush

    @push('script')
        <script>
            $(document).on('ready', function () {
                HSCore.components.HSTomSelect.init('.js-select');
                $('.js-daterangepicker').daterangepicker();

                $('.js-daterangepicker-times').daterangepicker({
                    timePicker: true,
                    startDate: moment().startOf('hour'),
                    endDate: moment().startOf('hour').add(32, 'hour'),
                    locale: {
                        format: 'M/DD hh:mm A'
                    }
                });

                var start = moment().subtract(29, 'days');
                var end = moment();

                function cb(start, end) {
                    $('#js-daterangepicker-predefined .js-daterangepicker-predefined-preview').html(start.format('MMM D') + ' - ' + end.format('MMM D, YYYY'));
                    $('#js-daterangepicker-predefined-country .js-daterangepicker-predefined-preview-country').html(start.format('MMM D') + ' - ' + end.format('MMM D, YYYY'));
                }

                $('#js-daterangepicker-predefined').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    }
                }, cb);

                $('#js-daterangepicker-predefined-country').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    }
                }, cb);

                cb(start, end);

                var ctx = document.getElementById('ecommerce-sales-revenue').getContext('2d');
                var salesChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: []
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 100,
                                    color: '#97a4af',
                                    font: {
                                        size: 12,
                                        family: 'Open Sans, sans-serif'
                                    },
                                    padding: 10
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#97a4af',
                                    font: {
                                        size: 12,
                                        family: 'Open Sans, sans-serif'
                                    },
                                    padding: 5
                                },
                                categoryPercentage: 0.5,
                                maxBarThickness: 10
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        hover: {
                            mode: 'nearest',
                            intersect: true
                        }
                    }
                });

                function fetchDataAndUpdateChart() {
                    let selectedStore = $('.js-select').val();
                    let dateRange = $('#js-daterangepicker-predefined').data('daterangepicker');
                    let startDate = dateRange.startDate.format('YYYY-MM-DD');
                    let endDate = dateRange.endDate.format('YYYY-MM-DD');

                    $.ajax({
                        url: '{{ route("admin.get.salesRevenueHistory") }}',
                        method: 'GET',
                        data: {
                            store: selectedStore,
                            start_date: startDate,
                            end_date: endDate
                        },
                        success: function (data) {

                            console.log(data);
                            salesChart.data.labels = data.labels;
                            salesChart.data.datasets = data.datasets;
                            salesChart.update();

                            let revenuePercentage = data.revenuePercentageChange;
                            let revenuePercentageValue = data.revenuePercentageValue;
                            let orderPercentage = data.orderPercentageChange;
                            let orderPercentageValue = data.orderPercentageValue;

                            let totalEarned = data.datasets[0].data.reduce((a, b) => a + b, 0);
                            let totalOrders = data.datasets[1].data.reduce((a, b) => a + b, 0);

                            document.querySelector('.revenueData').innerHTML = currencyPosition(totalEarned);
                            document.querySelector('.orderData').innerHTML = totalOrders;

                            let revenueIconClass = revenuePercentage.includes('-') ? 'bi-graph-down' : 'bi-graph-up';
                            let revenueColorClass = revenuePercentage.includes('-') ? 'text-danger' : 'text-success';
                            let revenuePercentElement = document.getElementById('revenuePercent');
                            revenuePercentElement.className = revenueColorClass;
                            revenuePercentElement.innerHTML = `<i class="${revenueIconClass} me-1"></i> ${currencyPosition(Math.abs(revenuePercentageValue))} (${Math.abs(parseFloat(revenuePercentage)).toFixed(1)}%)`;

                            let orderIconClass = orderPercentage.includes('-') ? 'bi-graph-down' : 'bi-graph-up';
                            let orderColorClass = orderPercentage.includes('-') ? 'text-danger' : 'text-success';
                            let orderPercentElement = document.getElementById('orderPercent');
                            orderPercentElement.className = orderColorClass;
                            orderPercentElement.innerHTML = `<i class="${orderIconClass} me-1"></i> ${Math.abs(orderPercentageValue)} (${Math.abs(parseFloat(orderPercentage)).toFixed(1)}%)`;
                        },
                        error: function (error) {
                            console.error('Error fetching data:', error);
                        }
                    });
                }


                function currencyPosition(amount) {



                    var currencyPosition = @json(basicControl()->is_currency_position);
                    var has_space_between_currency_and_amount = @json(basicControl()->has_space_between_currency_and_amount);
                    var currency_symbol = @json(basicControl()->currency_symbol);
                    var base_currency = @json(basicControl()->base_currency);
                    amount = parseFloat(amount).toFixed(2);
                    if (currencyPosition === 'left' && has_space_between_currency_and_amount) {
                        return currency_symbol + '  ' + amount;
                    } else if (currencyPosition === 'left' && !has_space_between_currency_and_amount) {
                        return currency_symbol + ' ' + amount;
                    } else if (currencyPosition === 'right' && has_space_between_currency_and_amount) {
                        return amount + '  ' + base_currency;
                    } else {
                        return amount + '  ' + base_currency;
                    }
                }

                // Event listeners
                $('.js-select').on('change', fetchDataAndUpdateChart);
                $('#js-daterangepicker-predefined').on('apply.daterangepicker', fetchDataAndUpdateChart);

                // Initial data fetch
                fetchDataAndUpdateChart();
            });


        </script>
    @endpush
@endif
