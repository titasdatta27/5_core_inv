@extends('layouts.vertical', ['title' => 'Live Pending Master', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('css')
    @vite(['node_modules/admin-resources/rwd-table/rwd-table.min.css'])
    <link href="https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        .chart-icon {
            cursor: pointer;
            color: #007bff;
            font-size: 18px;
        }
        .chart-icon:hover {
            color: #0056b3;
        }
        .modal-lg {
            max-width: 800px;
        }
        .date-filter-container {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .date-filter-container input {
            flex: 1;
        }
        
        /* Sticky Header */
        .row.mb-4 {
            position: sticky;
            top: 70px;
            z-index: 999;
            background: #f8f9fa;
            padding: 15px 0;
            margin-left: 0;
            margin-right: 0;
        }
        
        /* Beautiful Cards Styling */
        .stats-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            background: white;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        
        .stats-card.card-info {
            border-left: 4px solid #667eea;
        }
        
        .stats-card.card-success.positive {
            border-left: 4px solid #38ef7d;
        }
        
        .stats-card.card-success.negative {
            border-left: 4px solid #f45c43;
        }
        
        .stats-card .card-body {
            padding: 20px;
            position: relative;
        }
        
        .stats-card .card-title {
            color: #666;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stats-card .badge {
            font-size: 26px;
            font-weight: 700;
            padding: 8px 16px;
            background: #f8f9fa !important;
            border-radius: 8px;
        }
        
        .stats-card.card-info .badge {
            color: #667eea !important;
        }
        
        .stats-card.card-success.positive .badge {
            color: #38ef7d !important;
        }
        
        .stats-card.card-success.negative .badge {
            color: #f45c43 !important;
        }
        
        .stats-card .card-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 40px;
            opacity: 0.15;
        }
        
        .stats-card.card-info .card-icon {
            color: #667eea;
        }
        
        .stats-card.card-success.positive .card-icon {
            color: #38ef7d;
        }
        
        .stats-card.card-success.negative .card-icon {
            color: #f45c43;
        }
    </style>

@endsection

@section('content')
    @include('layouts.shared/page-title', [
        'page_title' => 'Live Pending Master',
        'sub_title' => 'Live Pending master Analysis',
    ])

    <!-- Summary Badges -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card card-info">
                <div class="card-body text-center">
                    <i class="fas fa-clock card-icon"></i>
                    <h5 class="card-title">Live Pending</h5>
                    <span class="badge" id="livePendingCountBadge">0</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card card-success {{ ($todayUpdates ?? 0) >= 0 ? 'positive' : 'negative' }}">
                <div class="card-body text-center">
                    <i class="fas {{ ($todayUpdates ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} card-icon"></i>
                    <h5 class="card-title">Total Diff ({{ date('d M Y') }})</h5>
                    <span class="badge" id="todayUpdatesBadge">
                        {{ ($todayUpdates ?? 0) >= 0 ? '+' : '' }}{{ $todayUpdates ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">

      

        <div id="customLoader" style="display: flex; justify-content: center; align-items: center; height: 300px;">
            <div class="spinner-border text-info" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <span class="ms-2">Loading datatable, please wait...</span>
        </div>



        <!-- Table Container -->
        <div class="table-container" id="channelTableWrapper"  style="display: none;">
            <div id="channelTable"></div>
        </div>

      
    </div>

    <!-- Chart Modal -->
    <div class="modal fade" id="chartModal" tabindex="-1" aria-labelledby="chartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chartModalLabel">Live Pending Trend - <span id="channelNameDisplay"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" id="chartSummary" style="display: none;">
                        <strong>Summary:</strong> <span id="summaryText"></span>
                    </div>
                    <div class="date-filter-container">
                        <label>Start Date:</label>
                        <input type="date" id="startDateFilter" class="form-control">
                        <label>End Date:</label>
                        <input type="date" id="endDateFilter" class="form-control">
                        <button class="btn btn-primary" onclick="applyDateFilter()">Apply Filter</button>
                        <button class="btn btn-secondary" onclick="clearDateFilter()">Clear</button>
                    </div>
                    <canvas id="livePendingChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- 1. Load jQuery FIRST -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- 2. Load Tabulator -->
    <script src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>

    <!-- 3. Then load other dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js Data Labels Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
        window.totalSkuCount = {{ $totalSkuCount }};
        window.zeroInvCount = {{ $zeroInvCount }};
    </script>


    <script>
        // Use jQuery.noConflict() if needed
        var jq = jQuery.noConflict(true);

        // Global variables
        let originalChannelData = [];
        let table;
        let selectedExec = '';

        // Debounce function to limit function calls
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Debounced table reload
        const debouncedTableReload = debounce(() => table.setData(), 300);

        // Handle R&A checkbox change
        

        // Enhanced number parsing function
        function parseNumber(value) {
            if (value === null || value === undefined || value === '' ||
                value === '#DIV/0!' || value === 'N/A') return 0;
            if (typeof value === 'number') return value;
            const cleaned = String(value).replace(/[^0-9.-]/g, '');
            return parseFloat(cleaned) || 0;
        }

        // Function to update all summary cards
        function updateAllTotals(data) {

            let l60SalesTotal = 0;
            let l30SalesTotal = 0;
            let l60OrdersTotal = 0;
            let l30OrdersTotal = 0;
            let growthValues = [];
            let gprofitValues = [];
            let groiValues = [];
            let livePendingTotal = 0;

            data.forEach(row => {
                try {
                    // Process sales - try multiple possible column names
                    const l60Sales = parseNumber(
                        row['L-60 Sales'] || row['L40 Sales'] || row['L40-Dollar'] || row['L40-Dollars'] || 0
                    );
                    const l30Sales = parseNumber(
                        row['L30 Sales'] || row['L30-Dollar'] || row['L30-Dollars'] || 0
                    );

                    // Process orders - try multiple possible column names
                    const l60Orders = parseNumber(
                        row['L60 Orders'] || row['L40-Dollars'] || 0
                    );
                    const l30Orders = parseNumber(
                        row['L30 Orders'] || row['L30-Dollars'] || 0
                    );

                    // Process metrics
                    const growth = parseNumber(row['Growth'] || 0);
                    const gprofit = parseNumber(row['Gprofit%'] || row['Growth%'] || 0);
                    const groi = parseNumber(row['G Roi%'] || row['G.Rents'] || 0);

                    // Process live pending
                    const livePending = parseNumber(row['Live Pending'] || 0);

                    // Add to totals
                    l60SalesTotal += l60Sales;
                    l30SalesTotal += l30Sales;
                    l60OrdersTotal += l60Orders;
                    l30OrdersTotal += l30Orders;
                    livePendingTotal += livePending;

                    // Collect for averages
                    if (!isNaN(growth)) growthValues.push(growth);
                    if (!isNaN(gprofit)) gprofitValues.push(gprofit);
                    if (!isNaN(groi)) groiValues.push(groi);

                } catch (e) {
                    console.error("Error processing row:", row, e);
                }
            });

            let growthTotal = 0;
            if (l30SalesTotal !== 0) {
                growthTotal = ((l30SalesTotal - l60SalesTotal) / l60SalesTotal) * 100;
            }

            // Update the cards with formatted values
            jq('#l60SalesCountBadge').text(Math.round(l60SalesTotal).toLocaleString('en-US'));
            jq('#l30SalesCountBadge').text(Math.round(l30SalesTotal).toLocaleString('en-US'));
            jq('#l60OrdersCountBadge').text(Math.round(l60OrdersTotal).toLocaleString('en-US'));
            jq('#l30OrdersCountBadge').text(Math.round(l30OrdersTotal).toLocaleString('en-US'));
            jq('#growthPercentageBadge').text(growthTotal.toFixed(0) + '%');
           
            // Calculate averages for percentages
            const calculateAverage = arr => arr.length ?
                (arr.reduce((a, b) => a + b, 0) / arr.length * 100) : 0;

            // jq('#growthPercentageBadge').text(Math.round(calculateAverage(growthValues)) + '%');
            jq('#gprofitPercentage').text(Math.round(calculateAverage(gprofitValues)) + '%');
            jq('#groiPercentageBadge').text(Math.round(calculateAverage(groiValues)) + '%');

            // Update live pending badge
            jq('#livePendingCountBadge').text(Math.round(livePendingTotal).toLocaleString('en-US'));

        }

       

        function initializeDataTable() {
            return new Tabulator("#channelTable", {
                data: @json($data),
                layout: "fitData",
                pagination: true,
                paginationSize: 50,
                footer: true,
                frozen: true,
                columns: [{
                        title: "SL",
                        headerSort: false,
                        frozen: true,
                        formatter: function(cell) {
                            return cell.getRow().getPosition(true);
                        }
                    },
                    {
                        title: "Channel",
                        field: "Channel ",
                        formatter: function(cell) {
                            const data = cell.getValue();
                            if (!data) return '';
                            const channelName = data.trim().toLowerCase();
                            const routeMap = {
                                'amazon': '/amazon-zero-view',
                                'ebay': '/ebay-zero-view',
                                'ebaytwo': '/zero-ebay2',
                                'ebaythree': '/zero-ebay3',
                                'ebayvariation': '/zero-ebayvariation',
                                'temu': '/temu-zero-view',
                                'macys': '/macys-zero-view',
                                'wayfair': '/Wayfair-zero-view',
                                'reverb': '/reverb/zero/view',
                                'shopifyb2c': '/shopifyB2C-zero-view',
                                'doba': '/zero-doba',
                                'walmart': '/zero-walmart',
                                'aliexpress': '/zero-aliexpress',
                                'tiktokshop': '/zero-tiktokshop',
                                'shein': '/zero-shein',
                                'faire': '/zero-faire',
                                'mercariwship': '/zero-mercariwship',
                                'fbmarketplace': '/zero-fbmarketplace',
                                'business5core': '/zero-business5core',
                                'pls': '/zero-pls',
                                'auto ds': '/zero-autods',
                                'mercariwoship': '/zero-mercariwoship',
                                'tiendamia': '/zero-tiendamia',
                                'syncee': '/zero-syncee',
                                'fbshop': '/zero-fbshop',
                                'instagramshop': '/zero-instagramshop',
                                'yamibuy': '/zero-yamibuy',
                                'dhgate': '/zero-dhgate',
                                'bestbuyusa': '/zero-bestbuyusa',
                                'swgearexchange': '/zero-swgearexchange',
                                'shopifywholesale/ds': '/zero-shopifywholesale',
                            };

                            const routeUrl = routeMap[channelName];

                            if (routeUrl) {
                                return `<a href="${routeUrl}" class="channel-name" target="_blank" style="color: #007bff; text-decoration: underline;">${data}</a>`;
                            } else {
                                return `<div class="d-flex align-items-center channel-name"><span>${data}</span></div>`;
                            }
                        }
                    },
                    {
                        title: "R&A",
                        field: "R&A",
                        visible: false,
                        formatter: function(cell) {
                            const isChecked = cell.getValue() ? 'checked' : '';
                            const channel = cell.getRow().getData()['Channel '];
                            return `<div class="ra-edit-container d-flex align-items-center">
                                <input type="checkbox" class="ra-checkbox" ${isChecked} onclick="handleRACheckbox(this, '${channel}')">
                            </div>`;
                        }
                    },
                    {
                        title: "Live Pending",
                        field: "Live Pending",
                        bottomCalc: "sum",
                        formatter: function(cell) {
                            return `<span class="live-pending" data-row="${cell.getRow().getPosition()}">${cell.getValue() ?? 0}</span>`;
                        }
                    },
                    {
                        title: "Updated Today",
                        field: "Updated Today",
                        headerSort: false,
                        hozAlign: "center",
                        formatter: function(cell) {
                            const isUpdated = cell.getValue();
                            if (isUpdated) {
                                return '<span style="color: green; font-size: 18px;">✓</span>';
                            } else {
                                return '<span style="color: red; font-size: 18px;">❌</span>';
                            }
                        }
                    },
                    {
                        title: "Diff",
                        field: "Diff",
                        hozAlign: "center",
                        bottomCalc: "sum",
                        formatter: function(cell) {
                            const diff = cell.getValue() ?? 0;
                            let color = 'black';
                            let symbol = '';
                            
                            if (diff > 0) {
                                color = 'green';
                                symbol = '+';
                            } else if (diff < 0) {
                                color = 'red';
                            }
                            
                            return `<span style="color: ${color}; font-weight: bold;">${symbol}${diff}</span>`;
                        }
                    },
                    {
                        title: "Trend",
                        headerSort: false,
                        formatter: function(cell) {
                            const channel = cell.getRow().getData()['Channel '];
                            return `<i class="fas fa-chart-line chart-icon" onclick="showChart('${channel}')"></i>`;
                        }
                    }
                ]
            });
        }


      
        //search
        jq(document).ready(function() {

            // Search Channel
            jq('#searchInput').on('input', debouncedTableReload);
        });


        // Dropdown functionality
        function populateChannelDropdown(searchTerm = '') {
            const channelData = originalChannelData.map(row => row['Channel ']);
            const uniqueChannels = [...new Set(channelData)].filter(ch => ch && ch.trim() !== '');

            const lowerSearch = searchTerm.toLowerCase();

            // Filter & sort: matched channels first
            const sortedChannels = uniqueChannels
                .filter(channel => channel.toLowerCase().includes(lowerSearch))
                .sort((a, b) => a.localeCompare(b));

            const $dropdown = jq('#channelSearchDropdown');
            $dropdown.empty();

            sortedChannels.forEach(channel => {
                $dropdown.append(`<div class="dropdown-search-item" data-value="${channel}">${channel}</div>`);
            });

            $dropdown.toggle(sortedChannels.length > 0); // Show only if matches
        }


        function populateExecDropdown(searchTerm = '') {
            const execData = originalChannelData.map(row => row['Exec']);
            const uniqueExecs = [...new Set(execData)].filter(exec => exec && exec.trim() !== '');
            uniqueExecs.sort();

            const lowerSearch = searchTerm.toLowerCase();

            // Sort: matched items first
            const sortedExecs = uniqueExecs.sort((a, b) => {
                const aMatch = a.toLowerCase().includes(lowerSearch);
                const bMatch = b.toLowerCase().includes(lowerSearch);
                if (aMatch && !bMatch) return -1;
                if (!aMatch && bMatch) return 1;
                return a.localeCompare(b); // fallback alphabetical
            });

            const $dropdown = jq('#execSearchDropdown');
            $dropdown.empty();

            uniqueExecs.forEach(exec => {
                $dropdown.append(`<div class="dropdown-search-item" data-value="${exec}">${exec}</div>`);
            });

            $dropdown.show();
        }

        jq('#channelSearchInput').on('input', function() {
            const val = jq(this).val().trim();
            console.log('[channelSearchInput] Input value:', val);
            populateChannelDropdown(val); // Pass the search term to sort dropdown

            if (val === '') {
                table.clearFilter(); // Clear the search if input is cleared
            }
        });

        jq('#execSearchInput').on('input', function() {
            const val = jq(this).val().trim();
            if (val === '') {
                table.clearFilter(); // Clear filter if empty
            }
            populateExecDropdown(val); // Pass the search term to sort dropdown
        });


        // jq('#execSearchInput').on('focus', function() {
        //     populateChannelDropdown(jq(this).val().trim());
        // });

        window.csrfToken = '{{ csrf_token() }}';

        // Chart related variables
        let currentChart = null;
        let currentChannelName = '';
        let chartModal = null;

        // Show chart function
        window.showChart = function(channelName) {
            currentChannelName = channelName;
            jq('#channelNameDisplay').text(channelName);
            
            // Clear date filters
            jq('#startDateFilter').val('');
            jq('#endDateFilter').val('');
            
            // Load chart data
            loadChartData(channelName);
            
            // Show modal
            if (!chartModal) {
                chartModal = new bootstrap.Modal(document.getElementById('chartModal'));
            }
            chartModal.show();
        };

        // Load chart data
        function loadChartData(channelName, startDate = null, endDate = null) {
            console.log('Loading chart data for:', channelName);
            const params = new URLSearchParams({
                channel: channelName
            });
            
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);

            const url = `/api/channel-chart-data?${params.toString()}`;
            console.log('Fetching from URL:', url);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log('Received data:', data);
                    if (!data.dates || data.dates.length === 0) {
                        jq('#chartSummary').html('<strong>No data available for this channel</strong>').show();
                        return;
                    }
                    renderChart(data.dates, data.counts);
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                    jq('#chartSummary').html('<strong class="text-danger">Error loading chart data</strong>').show();
                });
        }

        // Render chart
        function renderChart(dates, counts) {
            console.log('Rendering chart with dates:', dates, 'and counts:', counts);
            const ctx = document.getElementById('livePendingChart').getContext('2d');
            
            // Calculate summary
            if (dates.length > 0) {
                const firstCount = counts[0];
                const lastCount = counts[counts.length - 1];
                const difference = lastCount - firstCount;
                const percentChange = firstCount !== 0 ? ((difference / firstCount) * 100).toFixed(2) : 0;
                const changeText = difference >= 0 ? `increased by ${difference}` : `decreased by ${Math.abs(difference)}`;
                const arrow = difference >= 0 ? '↑' : '↓';
                const colorClass = difference >= 0 ? 'text-success' : 'text-danger';
                
                jq('#summaryText').html(
                    `From <strong>${dates[0]}</strong> (${firstCount}) to <strong>${dates[dates.length - 1]}</strong> (${lastCount}): ` +
                    `<span class="${colorClass}">${arrow} ${changeText} (${percentChange}%)</span>`
                );
                jq('#chartSummary').show();
            } else {
                jq('#chartSummary').hide();
            }
            
            // Destroy previous chart if exists
            if (currentChart) {
                currentChart.destroy();
            }
            
            console.log('Creating new chart...');
            currentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Live Pending Count',
                        data: counts,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1,
                        fill: true,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: 'rgb(75, 192, 192)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    layout: {
                        padding: {
                            top: 35
                        }
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            align: function(context) {
                                return 'top';
                            },
                            offset: 10,
                            backgroundColor: function(context) {
                                if (context.dataIndex === 0) {
                                    return 'rgba(75, 192, 192, 0.9)';
                                }
                                const currentValue = context.dataset.data[context.dataIndex];
                                const prevValue = context.dataset.data[context.dataIndex - 1];
                                const diff = currentValue - prevValue;
                                return diff >= 0 ? 'rgba(40, 167, 69, 0.9)' : 'rgba(220, 53, 69, 0.9)';
                            },
                            borderRadius: 5,
                            color: 'white',
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            padding: 8,
                            formatter: function(value, context) {
                                if (context.dataIndex === 0) {
                                    // First point - only show count
                                    return value;
                                }
                                // Subsequent points - show count and difference
                                const prevValue = context.dataset.data[context.dataIndex - 1];
                                const diff = value - prevValue;
                                const arrow = diff >= 0 ? '↑' : '↓';
                                return value + '\n' + arrow + Math.abs(diff);
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Count: ' + context.parsed.y;
                                },
                                afterLabel: function(context) {
                                    if (context.dataIndex > 0) {
                                        const prevCount = context.dataset.data[context.dataIndex - 1];
                                        const currentCount = context.parsed.y;
                                        const diff = currentCount - prevCount;
                                        return diff >= 0 ? `↑ +${diff} from previous day` : `↓ ${diff} from previous day`;
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Count'
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        }

        // Apply date filter
        window.applyDateFilter = function() {
            const startDate = jq('#startDateFilter').val();
            const endDate = jq('#endDateFilter').val();
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
            
            if (startDate > endDate) {
                alert('Start date must be before end date');
                return;
            }
            
            loadChartData(currentChannelName, startDate, endDate);
        };

        // Clear date filter
        window.clearDateFilter = function() {
            jq('#startDateFilter').val('');
            jq('#endDateFilter').val('');
            loadChartData(currentChannelName);
        };

        // Initialize when DOM is ready
        jq(document).ready(function() {
            // Set data
            originalChannelData = @json($data);

            // Initialize the table
            table = initializeDataTable();
            
            // Hide loader and show table
            $('#customLoader').hide();
            $('#channelTableWrapper').show();
            
            // Update totals when table data is loaded
            table.on('tableBuilt', function() {
                updateAllTotals(table.getData());
            });
            
            table.on('dataFiltered', function(filters, rows) {
                updateAllTotals(rows.map(row => row.getData()));
            });
        });
    </script>
@endsection
