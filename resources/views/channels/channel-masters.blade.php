@extends('layouts.vertical', ['title' => 'Channel Master', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])
<meta name="csrf-token" content="{{ csrf_token() }}">


@section('css')
    @vite(['node_modules/admin-resources/rwd-table/rwd-table.min.css'])
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa !important;
            color: var(--dark-color) !important;
        }
        /* bar chart css */
        canvas { width: 100% !important; height: 60% !important; }
         .controls { margin-bottom:12px; display:flex; gap:8px; align-items:center; }
         .btn {
           padding:8px 12px; border-radius:6px; border:1px solid #ccc; cursor:pointer; background:#fff;
         }
         .btn.off { opacity:0.45; text-decoration:line-through; }
         .status { margin-top:8px; color:#666; font-size:0.95rem; }
        /* bar chart css end */

        .container {
            max-width: 1200px !important;
            margin-top: 30px !important;
            margin-bottom: 50px !important;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
        }

        .header h4 {
            font-weight: 600;
            margin: 0;
        }

        .metric-value {
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        /* Negative growth (Red) */
        .negative-growth {
            background-color: rgb(255, 0, 0);
            color: rgb(0, 0, 0);
            width: 60px;
            text-align: center;
        }

        /* Zero growth (Yellow) */
        .zero-growth {
            background-color: rgb(255, 196, 0);
            color: rgb(0, 0, 0);
            width: 60px;
            text-align: center;
        }

        /* EXACTLY 100% (Magenta) */
        .exact-100 {
            background-color: #ff00ff;
            color: rgb(0, 0, 0);
            width: 60px;
            text-align: center;
        }

        .search-box {
            max-width: 350px;
            margin-left: auto;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 5px;
            transition: var(--transition);
        }

        .dataTables_wrapper .dataTables_filter input {
            border: none;
            border-radius: 50px;
            padding: 8px 15px;
            margin-left: 10px;
        }

        .table>thead {
            vertical-align: bottom;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        th.sorting {
            color: white !important;
            font-size: 10px;
        }

        /* Rest of your existing styles... */
        /* Keep all your existing styles, just add these new ones below */

        /* DataTables custom styling */
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #333;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em 1em;
            margin: 0 2px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--primary-color);
            color: white !important;
            border: 1px solid var(--primary-color);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e9ecef;
            border: 1px solid #ddd;
        }

        /* Loading indicator */
        .dataTables_processing {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        /* Responsive adjustments for DataTables */
        @media (max-width: 768px) {
            .dataTables_wrapper .dataTables_filter {
                float: none;
                text-align: left;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100%;
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>
    <style>
        /* Right-to-Left Modal Animation */
        .modal.right-to-left .modal-dialog {
            margin: 0;
            right: 0;
            width: 400px;
            max-width: 80%;
            height: 100%;
            transform: translateX(100%);
            transition: transform 0.3s ease-out;
        }

        .modal.right-to-left.show .modal-dialog {
            transform: translateX(0);
        }

        .modal.right-to-left .modal-content {
            height: 100%;
            overflow-y: auto;
            border-radius: 0;
            border: none;
        }

        /* Keep your existing modal styling */
        .modal.right-to-left .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal.right-to-left .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Sticky Dashboard Cards */
        .dashboard-header {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 1030;
            padding-top: 10px;
        }

        /* Scrollable table */
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: auto;
        }

        /* Sticky Table Header */
        thead.sticky-top th {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: #fff;
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.05);
        }

        /* Optional Cleanup */
        table th,
        table td {
            white-space: nowrap;
        }

        .dropdown-search-item {
            padding: 6px 10px;
            cursor: pointer;
        }

        .dropdown-search-item:hover {
            background-color: #eee;
        }

        /* ========== PLAY/PAUSE NAVIGATION BUTTONS ========== */
        .time-navigation-group {
            margin-left: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            overflow: hidden;
            padding: 2px;
            background: #f8f9fa;
            display: inline-flex;
            align-items: center;
        }

        .time-navigation-group button {
            padding: 0;
            border-radius: 50% !important;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 3px;
            transition: all 0.2s ease;
            border: 1px solid #dee2e6;
            background: white;
            cursor: pointer;
        }

        .time-navigation-group button:hover {
            background-color: #f1f3f5 !important;
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .time-navigation-group button:active {
            transform: scale(0.95);
        }

        .time-navigation-group button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .time-navigation-group button i {
            font-size: 1.1rem;
            transition: transform 0.2s ease;
        }

        /* Play button */
        #play-auto {
            color: #28a745;
        }

        #play-auto:hover {
            background-color: #28a745 !important;
            color: white !important;
        }

        /* Pause button */
        #play-pause {
            color: #ffc107;
            display: none;
        }

        #play-pause:hover {
            background-color: #ffc107 !important;
            color: white !important;
        }

        /* Navigation buttons */
        #play-backward,
        #play-forward {
            color: #007bff;
        }

        #play-backward:hover,
        #play-forward:hover {
            background-color: #007bff !important;
            color: white !important;
        }

        /* Button state colors - must come after hover styles */
        #play-auto.btn-success,
        #play-pause.btn-success {
            background-color: #28a745 !important;
            color: white !important;
        }

        #play-auto.btn-warning,
        #play-pause.btn-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        #play-auto.btn-danger,
        #play-pause.btn-danger {
            background-color: #dc3545 !important;
            color: white !important;
        }

        #play-auto.btn-light,
        #play-pause.btn-light {
            background-color: #f8f9fa !important;
            color: #212529 !important;
        }

        /* Ensure hover doesn't override state colors */
        #play-auto.btn-success:hover,
        #play-pause.btn-success:hover {
            background-color: #28a745 !important;
            color: white !important;
        }

        #play-auto.btn-warning:hover,
        #play-pause.btn-warning:hover {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        #play-auto.btn-danger:hover,
        #play-pause.btn-danger:hover {
            background-color: #dc3545 !important;
            color: white !important;
        }

        /* Active state styling */
        .time-navigation-group button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .time-navigation-group button {
                width: 36px;
                height: 36px;
            }

            .time-navigation-group button i {
                font-size: 1rem;
            }
        }

        /* Add to your CSS file or style section */
        .hide-column {
            display: none !important;
        }

        .dataTables_length,
        .dataTables_filter {
            display: none;
        }

        #play-auto.green-btn {
            background-color: green !important;
            color: white;
        }

        #play-auto.red-btn {
            background-color: red !important;
            color: white;
        }

        th small.badge {
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 12px;
        }

        .dataTables_processing {
            top: 150px !important;
            /* Try 80–100px depending on your header height */
            z-index: 1000 !important;
            background: none !important;
            border: none;
        }

        #channelTable {
            width: 100% !important;
            table-layout: fixed;
        }

        #channelTable thead th {
            color: white !important;
            font-size: 10px !important;
        }

        #channelTable th {
            text-transform: none !important;
        }
    </style>
@endsection

@section('content')
    @include('layouts.shared/page-title', [
        'page_title' => 'Channel Master',
        'sub_title' => 'Channel master Analysis',
    ])
    <div class="container-fluid">
        <!-- Header with Title and Search -->
        <!-- <div class="header d-flex align-items-center">
                <div>
                    <h4><i class="bi bi-bar-chart-line me-2"></i> Channel Master Dashboard</h4>
                </div>
            </div> -->

        <div class="col-md-12 mt-0 pt-0 mb-1 pb-1">
            <div class="row justify-content-center align-items-center g-3">
                <!-- play backward forward -->
                <div class="col d-flex align-items-center gap-2 flex-wrap">
                    <div class="btn-group time-navigation-group" role="group" aria-label="Parent navigation">
                        <button id="play-backward" class="btn btn-light rounded-circle" title="Previous parent">
                            <i class="fas fa-step-backward"></i>
                        </button>
                        <button id="play-pause" class="btn btn-light rounded-circle" title="Show all products"
                            style="display: none;">
                            <i class="fas fa-pause"></i>
                        </button>
                        <button id="play-auto" class="btn btn-light rounded-circle" title="Show all products">
                            <i class="fas fa-play"></i>
                        </button>
                        <button id="play-forward" class="btn btn-light rounded-circle" title="Next parent">
                            <i class="fas fa-step-forward"></i>
                        </button>
                    </div>

                    <button id="addChannelBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChannelModal"
                        style="background: linear-gradient(135deg, #4361ee, #3f37c9); border: none;">
                        <i class="fas fa-plus-circle me-2"></i> Add Channel
                    </button>

                    <button id="showSalesGraph" class="btn btn-success" style="background: linear-gradient(135deg, #06d6a0, #118ab2); border: none;"> 📈 Show Daily Sales Graph</button>

                    <div class="d-inline-flex align-items-center ms-2">
                        <div class="badge bg-primary text-white px-3 py-2 me-2"
                            style="font-size: 1rem; border-radius: 8px;">
                            0 SOLD - 0
                        </div>
                        <div class="badge bg-primary text-white px-3 py-2" style="font-size: 1rem; border-radius: 8px;">
                            SOLD - 0
                        </div>
                    </div>
                </div>

                <div class="col-auto">
                    <div class="dropdown-search-container" style="position: relative;">
                        <input type="text" class="form-control form-control-sm channel-search"
                            placeholder="Search Channel" id="channelSearchInput">
                        <div class="dropdown-search-results" id="channelSearchDropdown"
                            style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #ccc; display: none; max-height: 200px; overflow-y: auto;">
                        </div>
                    </div>
                </div>

            </div>
        </div>

         <div id="chartWrap">
            <div class="row">
                <div class="col-md-3">
                    <div class="controls">
                         <button id="toggleL30" class="btn" style="background-color: #53afed;">L30</button>
                         <button id="toggleL60" class="btn" style="background-color: #feac5b;">L60</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="from_date">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="to_date">
                </div>
                <div class="col-md-2">
                    <button class="badge bg-primary text-white px-3 py-2" style="font-size: 1rem; border-radius: 8px;">Sales Graph</button>
                </div>
            </div>
            <!-- top chart summary calculation -->
             <div class="row text-center">
                            <div class="col">
                                <p class="text-muted mt-3">Sales</p>
                                <h3 class=" mb-0">
                                    <span id="total_sales">0</span>
                                </h3>
                            </div>
                            <div class="col">
                                <p class="text-muted mt-3">Profit Margin</p>
                                <h3 class=" mb-0">
                                    <span id="profit_margin">0% </span>
                                </h3>
                            </div>
                            <div class="col">
                                <p class="text-muted mt-3">ROI</p>
                                <h3 class=" mb-0">
                                    <span id="sales_roi">0%</span>
                                </h3>
                            </div>
                            <!-- <div class="col">
                                <p class="text-muted mt-3">Customers</p>
                                <h3 class=" mb-0">
                                    <span>3k</span>
                                </h3>
                            </div> -->
                        </div>
                        <br>
         <canvas id="barChart"></canvas>
        <div id="status" class="status">Loading data…</div>
        </div>

        <!-- Add Channel Modal (Left to Right Slide-in) -->
        <div class="modal fade right-to-left" id="addChannelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-side">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Channel</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="channelForm">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="channelName" class="form-label">Channel Name</label>
                                    <input type="text" class="form-control" id="channelName" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="channelUrl" class="form-label">Sheet Link</label>
                                <input type="url" class="form-control" id="channelUrl">
                            </div>

                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <input type="text" class="form-control" id="type">
                            </div>

                            <!-- <hr> -->
                            <!-- <h6 class="mb-3">Performance Metrics</h6> -->

                            <!-- <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="l60Sales" class="form-label">L-60 Sales</label>
                                    <input type="number" class="form-control" id="l60Sales">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="l30Sales" class="form-label">L30 Sales</label>
                                    <input type="number" class="form-control" id="l30Sales">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="growth" class="form-label">Growth</label>
                                    <input type="number" class="form-control" id="growth" step="0.01">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="l60Orders" class="form-label">L60 Orders</label>
                                    <input type="number" class="form-control" id="l60Orders">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="l30Orders" class="form-label">L30 Orders</label>
                                    <input type="number" class="form-control" id="l30Orders">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gprofit" class="form-label">Gprofit%</label>
                                    <input type="number" class="form-control" id="gprofit" step="0.01">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="groi" class="form-label">G Roi%</label>
                                    <input type="number" class="form-control" id="groi" step="0.01">
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="updateCheck" checked>
                                        <label class="form-check-label" for="updateCheck">Update</label>
                                    </div>
                                </div>
                            </div> -->
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveChannelBtn">Save Channel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Channel Modal -->
        <div class="modal fade" id="editChannelModal" tabindex="-1" aria-labelledby="editChannelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <form id="editChannelForm">
                <div class="modal-header">
                <h5 class="modal-title" id="editChannelModalLabel">Edit Channel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                <input type="hidden" id="editChannelId" name="id">
                <input type="hidden" id="originalChannel" name="original_channel">

                <div class="mb-3">
                    <label for="editChannelName" class="form-label">Channel Name</label>
                    <input type="text" class="form-control" id="editChannelName" name="channel_name" readonly>
                </div>

                <div class="mb-3">
                    <label for="editChannelUrl" class="form-label">Sheet URL</label>
                    <input type="text" class="form-control" id="editChannelUrl" name="sheet_url" required>
                </div>

                <div class="mb-3">
                    <label for="editType" class="form-label">Type</label>
                    <input type="text" class="form-control" id="editType" name="type" required>
                </div>

                <div class="mb-3">
                    <label for="editpercentage" class="form-label">Channel Percentage</label>
                    <input type="text" class="form-control" id="editpercentage" name="type" required>
                </div>

                </div>
                <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Channel</button>
                </div>
            </form>
            </div>
        </div>
        </div>


        <div id="customLoader" style="display: flex; justify-content: center; align-items: center; height: 300px;">
            <div class="spinner-border text-info" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <span class="ms-2">Loading datatable, please wait...</span>
        </div>

        {{-- <button id="showSalesGraph" class="btn btn-primary mb-3">📈 Show Sales Trend</button> --}}

        <!-- Hidden chart area -->
        <div id="salesTrendChart" style="width: 100%; height: 500px; display: none;"></div>

        {{-- <div class="col-md-12 mt-3">
            <div id="channelSalesChart" style="width: 100%; height: 400px; display: none; background: #f8f9fa; border-radius: 10px; padding: 10px;"></div>
        </div> --}}

        {{-- <div>
            <label>
                <input type="checkbox" id="showSales" checked> Sales Wise (L30 & L60)
            </label>
            <label style="margin-left:20px;">
                <input type="checkbox" id="showOrders" checked> Orders wise (L30 & L60)
            </label>
            <label style="margin-left:20px;">
                <input type="checkbox" id="showGProfit" checked> GProfit wise (L30 & L60)
            </label>
            <label style="margin-left:20px;">
                <input type="checkbox" id="showGRoi" checked> GRoi wise (L30 & L60)
            </label>
        </div>

        <div class="mb-4">
            
            <div id="channelSalesChart" style="width: 100%; height: 400px;"></div>
        </div> --}}


        <!-- Table Container -->
        <div class="table-container" id="channelTableWrapper" style="display: none;">
            <div class="table-responsive" style="max-height: 500px; overflow: auto;">
                <table class="table table-hover table-striped mb-0" id="channelTable">
                    <thead class="table sticky-top">
                        <tr>
                            <th>Channel</th>
                            <th>R&A</th>
                            {{-- <th>Link</th> --}}
                            <th>Sheet Link</th>
                            {{-- <th class="text-center align-middle">
                                <small id="l60SalesCountBadge" class="badge bg-dark text-white mb-1"
                                    style="font-size: 13px;">
                                    ₹ 0
                                </small><br>
                                L-60 Sales
                            </th> --}}
                            <th class="text-center align-middle">
                                <small id="l30SalesCountBadge" class="badge bg-dark text-white mb-1"
                                    style="font-size: 13px;">
                                    ₹ 0
                                </small><br>
                                L30 Sales
                            </th>
                            <th class="text-center align-middle">
                                <small id="growthPercentageBadge" class="badge bg-dark text-white mb-1"
                                    style="font-size: 13px;">
                                    0%
                                </small><br>
                                Growth
                            </th>
                            {{-- <th class="text-center align-middle">
                                <small id="l60OrdersCountBadge" class="badge bg-dark text-white mb-1"
                                    style="font-size: 13px;">
                                    0
                                </small><br>
                                L60 Orders
                            </th> --}}
                            <th class="text-center align-middle">
                                <small id="l30OrdersCountBadge" class="badge bg-dark text-white mb-1"
                                    style="font-size: 13px;">
                                    0
                                </small><br>
                                L30 Orders
                            </th>
                            <th class="text-center align-middle">
                                <small id="gprofitPercentage" class="badge bg-dark text-white mb-1"
                                    style="font-size: 13px;">
                                    0%
                                </small><br>
                                Gprofit%
                            </th>
                            <th class="text-center align-middle">
                                <small id="groiPercentageBadge" class="badge bg-dark text-white mb-1"
                                    style="font-size: 13px;">
                                    0%
                                </small><br>
                                G ROI%
                            </th>
                            {{-- <th>Red Margin</th> --}}
                            <th>Percentage</th>
                            <th>NR</th>
                            <th>type</th>
                            <th>Listing Counts</th>
                            <th>W/Ads</th>
                            {{-- <th>0 Sold SKU Count</th>
                            <th>Sold Sku Count</th>
                            <th>Brand Registry</th> --}}
                            <th>Update</th>
                            <th>Ac Health</th>
                            <th class="text-white">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Advanced Info Modal -->
        <div class="modal fade" id="advancedInfoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Channel Metrics Summary</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="compact-metrics">
                            <div class="metric-row">
                                <div class="metric-item">
                                    <i class="bi bi-pencil-square edit-icon" onclick="editMetric(this)"></i>
                                    <div class="metric-value">159</div>
                                    <input type="text" class="metric-input" value="159" style="display: none;">
                                </div>
                                <div class="metric-item">
                                    <i class="bi bi-pencil-square edit-icon" onclick="editMetric(this)"></i>
                                    <div class="metric-value">189</div>
                                    <input type="text" class="metric-input" value="189" style="display: none;">
                                </div>
                                <div class="metric-item">
                                    <i class="bi bi-pencil-square edit-icon" onclick="editMetric(this)"></i>
                                    <div class="metric-value">340</div>
                                    <input type="text" class="metric-input" value="340" style="display: none;">
                                </div>
                                <div class="metric-item">
                                    <i class="bi bi-pencil-square edit-icon" onclick="editMetric(this)"></i>
                                    <div class="metric-value">760</div>
                                    <input type="text" class="metric-input" value="760" style="display: none;">
                                </div>
                                <div class="metric-item">
                                    <i class="bi bi-pencil-square edit-icon" onclick="editMetric(this)"></i>
                                    <div class="metric-value">230</div>
                                    <input type="text" class="metric-input" value="230" style="display: none;">
                                </div>
                                <div class="metric-item">
                                    <i class="bi bi-pencil-square edit-icon" onclick="editMetric(this)"></i>
                                    <div class="metric-value">600</div>
                                    <input type="text" class="metric-input" value="230" style="display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="saveChanges()">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- 1. Load jQuery FIRST -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- 2. Then load DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- 3. Then load other dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>

    <script>
        // Use jQuery.noConflict() if needed
        var jq = jQuery.noConflict(true);

        // Global variables
        let originalChannelData = [];
        let allChannelData = [];
        let table;
        let isPlaying = false;
        let currentChannelIndex = 0;
        let uniqueChannels = [];
        let uniqueChannelRows = [];
        let selectedChannel = '';
        let tableData = []; 

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

                    // Add to totals
                    l60SalesTotal += l60Sales;
                    l30SalesTotal += l30Sales;
                    l60OrdersTotal += l60Orders;
                    l30OrdersTotal += l30Orders;

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
            const l60Badge = document.getElementById('l60SalesCountBadge');
            const l30Badge = document.getElementById('l30SalesCountBadge');
            const l60OrdersBadge = document.getElementById('l60OrdersCountBadge');
            const l30OrdersBadge = document.getElementById('l30OrdersCountBadge');
            const growthBadge = document.getElementById('growthPercentageBadge');
            const gprofitBadge = document.getElementById('gprofitPercentage');
            const groiBadge = document.getElementById('groiPercentageBadge');

            if (l60Badge) l60Badge.textContent = Math.round(l60SalesTotal).toLocaleString('en-US');
            if (l30Badge) l30Badge.textContent = Math.round(l30SalesTotal).toLocaleString('en-US');
            if (l60OrdersBadge) l60OrdersBadge.textContent = Math.round(l60OrdersTotal).toLocaleString('en-US');
            if (l30OrdersBadge) l30OrdersBadge.textContent = Math.round(l30OrdersTotal).toLocaleString('en-US');
            if (growthBadge) growthBadge.textContent = growthTotal.toFixed(0) + '%';

            // Calculate G profit and G roi using totalPft, totalL30Sales, totalCogs
            let totalPft = 0;
            let totalCogs = 0;
            let totalL30Sales = 0;
            data.forEach(function(row) {
                
                // const pft = parseNumber(row['Gprofit%']);
                // totalPft += pft;
                const cogs = parseNumber(row['cogs']);
                
                totalCogs += cogs;
                // const l30Sales = parseNumber(row['L30 Sales']);
                // totalL30Sales += l30Sales;

                const l30Sales = parseNumber(row['L30 Sales'] || 0);
                const gprofitPercent = parseNumber(row['Gprofit%'] || 0);

                // convert % → absolute profit amount for this row
                const profitAmount = (gprofitPercent / 100) * l30Sales;
                

                totalPft += profitAmount;
                totalL30Sales += l30Sales;
                $("#total_sales").html('$'+Number(totalL30Sales).toLocaleString('en-US'));
            });
            let gProfit = totalL30Sales !== 0 ? (totalPft / totalL30Sales)* 100 : null;
            let gRoi = totalCogs !== 0 ? (totalPft / totalCogs)* 100 : null;
            if (gprofitBadge) {
                gprofitBadge.textContent = gProfit !== null ? gProfit.toFixed(1) + '%' : 'N/A';
            }
            // if (gprofitBadge) gprofitBadge.textContent = gProfit !== null ? gProfit.toFixed(1) + '%' : 'N/A';
            if (groiBadge) groiBadge.textContent = gRoi !== null ? gRoi.toFixed(1) + '%' : 'N/A';

            $("#profit_margin").html(gProfit.toFixed(1)+'%');
            $("#sales_roi").html(gRoi.toFixed(1)+'%');
        }

        // Initialize DataTable
        // function initializeDataTable() {         //without gprofit sort
        //     try {
        //         if (!jq('#channelTable').length) {
        //             console.error('Table element not found');
        //             return null;
        //         }

        //         // If already initialized, destroy cleanly so we can rebuild
        //         if (jq.fn.DataTable.isDataTable('#channelTable')) {
        //             jq('#channelTable').DataTable().clear().destroy();
        //             // Remove previous header thead that DataTables might have created
        //             jq('#channelTable').empty();
        //         }

        //         // --- helpers ---
        //         const toNum = (v, def = 0) => {
        //             const n = parseFloat(String(v).replace(/,/g, ''));
        //             return Number.isFinite(n) ? n : def;
        //         };
        //         const pick = (obj, keys, def = '') => {
        //             for (const k of keys) {
        //                 const v = obj[k];
        //                 if (v !== undefined && v !== null && v !== '') return v;
        //             }
        //             return def;
        //         };
        //         const pctFix = (v) => {
        //             let n = toNum(v, 0);
        //             // If it's a fraction (<=1), convert to %
        //             if (Math.abs(n) <= 1) n = n * 100;
        //             // clamp weird values
        //             if (!Number.isFinite(n)) n = 0;
        //             return n;
        //         };

        //         let table = jq('#channelTable').DataTable({
        //             processing: true,
        //             serverSide: false,
        //             ordering: false,
        //             searching: true,
        //             pageLength: 50,
        //             destroy: true,
        //             ajax: {
        //                 url: '/channels-master-data',
        //                 type: "GET",
        //                 data: function (d) {
        //                     jq('#customLoader').hide();
        //                     jq('#channelTableWrapper').show();
        //                     d.channel     = jq('#channelSearchInput').val(); // Amazon / eBay / etc.
        //                     d.search      = jq('#searchInput').val();
        //                     d.sort_by     = window.currentSortColumn || null;
        //                     d.sort_order  = window.currentSortOrder || null;
        //                 },
        //                dataSrc: function (json) {
        //                     if (!json || !json.data) return [];

        //                     // drawChannelChart(json.data);

        //                     json.data.sort((a, b) => {
        //                         const aVal = parseFloat(String(a['L30 Sales'] || a['l30_sales'] || 0).replace(/,/g, '')) || 0;
        //                         const bVal = parseFloat(String(b['L30 Sales'] || b['l30_sales'] || 0).replace(/,/g, '')) || 0;
        //                         return bVal - aVal; // high → low
        //                     });

        //                     // Normalize every row to ONE schema so columns line up
        //                     return json.data.map(item => {
        //                         // console.log('Raw item:', item);

        //                         // Make sure to include exact keys sent by controller
        //                         const l60Sales = toNum(pick(item, ['L-60 Sales', 'L60 Sales', 'l60_sales', 'A_L60', 'l60sales'], 0), 0);
        //                         const l30Sales = toNum(pick(item, ['L30 Sales', 'l30_sales', 'T_Sale_l30', 'l30sales'], 0), 0);

        //                         const l60Orders = toNum(pick(item, ['L60 Orders','l60_orders', 'A_L60_orders', 'l60orders'], 0), 0);
        //                         const l30Orders = toNum(pick(item, ['L30 Orders','l30_orders', 'A_L30_orders', 'l30orders'], 0), 0);

        //                         let growth = pick(item, ['growth', 'Growth'], null);
        //                         // if (growth === null || growth === '' || !Number.isFinite(toNum(growth))) {
        //                         //     growth = l30Orders > 0 ? ((l30Orders - l60Orders) / l30Orders) * 100 : 0;
        //                         // } else {
        //                         //     growth = pctFix(growth);
        //                         // }
        //                         // if (!Number.isFinite(growth)) growth = 0;

        //                         let gprofit = pctFix(pick(item, ['gprofit_percentage', 'PFT_percentage', 'gprofit', 'Gprofit%'], 0));
        //                         let groi    = pctFix(pick(item, ['G Roi', 'g_roi_percentage', 'roi'], 0));
        //                         let gprofitL60  = pctFix(pick(item, ['Gprofitl60'], 0));
        //                         let cogs = pctFix(pick(item, ['cogs'], 0));

        //                         return {
        //                             'Channel': pick(item, ['channel', 'Channel', 'Channel '], ''),  // use per-row channel
        //                             'Link': pick(item, ['link', 'url', 'URL LINK', 'url_link'], ''),
        //                             'sheet_link': pick(item, ['sheet_link', 'sheet_url', 'sheet'], ''),
        //                             'R&A': toNum(pick(item, ['ra', 'R&A', 'R_and_A'], 0), 0),
        //                             'L-60 Sales': l60Sales,
        //                             'L30 Sales': l30Sales,
        //                             'Growth': growth,
        //                             'L60 Orders': l60Orders,
        //                             'L30 Orders': l30Orders,
        //                             'Gprofit%': gprofit,
        //                             'GprofitL30': gprofit,
        //                             'GprofitL60': gprofitL60,
        //                             'G ROI%': groi,
        //                             'Red Margin': toNum(pick(item, ['red_margin', 'Total_pft', 'total_pft'], 0), 0),
        //                             'NR': toNum(pick(item, ['nr','NR'], 0), 0),
        //                             'type': pick(item, ['type'], ''),
        //                             'Listing Counts': toNum(pick(item, ['listing_counts', 'listed_count', 'list_count'], 0), 0),
        //                             'W/Ads': toNum(pick(item, ['w_ads', 'W/Ads','with_ads', 'ads'], 0), 0),
        //                             '0 Sold SKU Count': toNum(pick(item, ['zero_sku', 'zero_sku_count', 'zero_sold_sku'], 0), 0),
        //                             'Sold Sku Count': toNum(pick(item, ['sold_sku', 'sold_sku_count'], 0), 0),
        //                             'Brand Registry': toNum(pick(item, ['brand_registry', 'brandregistry'], 0), 0),
        //                             'Update': toNum(pick(item, ['update_flag', 'update','Update'], 0), 0),
        //                             'Ac Health': pick(item, ['account_health', 'ac_health', 'accounthealth'], ''),
        //                             'Channel Percentage': pctFix(pick(item, ['channel_percentage'], 0), 0),
        //                             'cogs': cogs,
                                    
        //                         };
                                
        //                     });
        //                 },

        //                 error: function (xhr, error, thrown) {
        //                     console.log("AJAX error:", error, thrown);
        //                 }
        //             },
        //             columns: [
        //                 // { data: 'Channel' },
        //                 {
        //                     data: 'Channel',
        //                     render: function(data, type, row) {
        //                         if (!data) return '';

        //                         const channelName = data.trim().toLowerCase();
        //                         const routeMap = {
        //                             'amazon': '/overall-amazon',
        //                             'amazon fba': '/overall-amazon-fba',
        //                             'ebay': '/ebay',
        //                             'ebay': '/ebay',
        //                             'ebaytwo': '/ebayTwoAnalysis',
        //                             'ebaythree': '/ebayThreeAnalysis',
        //                             'temu': '/temu',
        //                             'macys': '/macys',
        //                             'wayfair': '/Wayfair',
        //                             'reverb': '/reverb',
        //                             'shopify b2c': '/shopifyB2C',
        //                             'doba': '/doba',
        //                             'walmart': '/walmartAnalysis',
        //                             'bestbuy usa': '/bestbuyusa-analytics',
        //                             'shein': '/sheinAnalysis',
        //                             'tiktok shop': '/tiktokAnalysis',
        //                             'aliexpress': '/aliexpressAnalysis',
        //                         };

        //                         const routeUrl = routeMap[channelName];

        //                         if (routeUrl) {
        //                             return `<a href="${routeUrl}" target="_blank" style="color: #007bff; text-decoration: underline;">${data}</a>`;
        //                         } else {
        //                             return `<div class="d-flex align-items-center"><span>${data}</span></div>`;
        //                         }
        //                     }
        //                 },
        //                 { data: 'R&A', render: function (v, t, row) {
        //                         const checked = toNum(v) === 1 ? 'checked' : '';
        //                         return `<input type="checkbox" class="ra-checkbox" data-channel="${row['Channel']}" ${checked}>`;
        //                     }
        //                 },
        //                 // {
        //                 //     data: 'sheet_link',
        //                 //     render: function(data, type, row) {
        //                 //         const sheetLink = data || '';
        //                 //         const channelName = row['Channel'] || '';
        //                 //         return `
        //                 //             <div style="display:flex; align-items:center; gap:6px;">
        //                 //                 <input type="text"
        //                 //                     class="form-control form-control-sm sheet-link-input"
        //                 //                     value="${sheetLink}"
        //                 //                     data-channel="${channelName}"
        //                 //                     placeholder="Enter Link"
        //                 //                     style="min-width: 10px;" />
        //                 //                 ${sheetLink ? `<a href="${sheetLink}" target="_blank" class="btn btn-sm btn-success">🔗</a>` : ''}
        //                 //             </div>
        //                 //         `;
        //                 //     }
        //                 // },      
        //                 {
        //                     data: 'sheet_link',
        //                     render: function(data, type, row) {
        //                         const sheetLink = data || '';
        //                         const channelName = row['Channel'] || '';
        //                         return `
        //                             <div style="display:flex; align-items:center; gap:6px;">
        //                                 ${sheetLink ? `<a href="${sheetLink}" target="_blank" class="btn btn-sm btn-success">🔗</a>` : ''}
        //                             </div>
        //                         `;
        //                     }
        //                 },      
        //                 // { data: 'L-60 Sales', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 // { data: 'L30 Sales',  render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 {
        //                     data: 'L30 Sales',
        //                     render: function(data, type, row) {
        //                         const n = parseFloat(String(data).replace(/,/g, '')) || 0;

        //                         // For sorting: return numeric value (DataTables internal)
        //                         if (type === 'sort' || type === 'type') return n;

        //                         // Display formatted
        //                         return `<span class="metric-value">${n.toLocaleString('en-US')}</span>`;
        //                     },
        //                     // --- NEW: always sort descending before displaying ---
        //                     createdCell: function(td, cellData, rowData, row, col) {
        //                         // no action needed here
        //                     }
        //                 },

        //                 {
        //                     data: 'Growth',
        //                     render: function (v) {
        //                         const n = pctFix(v);
        //                         if (!Number.isFinite(n)) return '-';
        //                         let bg = '', color = 'black';
        //                         if (n < 0)              { bg = '#ff0000'; color = 'white'; }
        //                         else if (n < 10)        { bg = '#ffff00'; }
        //                         else if (n < 20)        { bg = '#00ffff'; }
        //                         else if (n < 50)        { bg = '#00ff00'; }
        //                         else                    { bg = '#ff00ff'; color = 'white'; }
        //                         return `<span style="background:${bg};color:${color};padding:2px 6px;border-radius:4px;">${Math.round(n)}%</span>`;
        //                     }
        //                 },
                        
        //                 // { data: 'L60 Orders', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 { data: 'L30 Orders', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },

        //                 // {
        //                 //     data: 'Gprofit%',
        //                 //     render: function (v) {
        //                 //         const n = pctFix(v);
        //                 //         let bg = '', color = 'black';
        //                 //         if (n < 25)            { bg = '#ff0000'; color = 'white'; }
        //                 //         else if (n < 33)       { bg = '#00ff00'; }
        //                 //         else                   { bg = '#ff00ff'; color = 'white'; }
        //                 //         return `<span style="background:${bg};color:${color};padding:2px 6px;border-radius:4px;">${Math.round(n)}%</span>`;
        //                 //     }
        //                 // },
        //                 {
        //                     data: 'Gprofit%',
        //                     render: function (v) {
        //                         const n = pctFix(v);
        //                         let bg = '', color = 'white';

        //                         if (n < 15) {
        //                             bg = '#ff0000'; // Red
        //                         } else if (n >= 15 && n < 25) {
        //                             bg = '#ffff00'; // Yellow
        //                             color = 'black';
        //                         } else if (n >= 25 && n < 40) {
        //                             bg = '#00ff00'; // Green
        //                             color = 'black';
        //                         } else {
        //                             bg = '#8000ff'; // Purple
        //                         }

        //                         return `<span style="background:${bg};color:${color};padding:2px 6px;border-radius:4px;">${Math.round(n)}%</span>`;
        //                     }
        //                 },
        //                 {
        //                     data: 'G ROI%',
        //                     render: function (v) {
        //                         const n = pctFix(v);
        //                         let bg = '', color = 'black';
        //                         if (n <= 50) { bg = '#ff0000'; color = 'white'; }
        //                         return `<span style="background:${bg};color:${color};padding:2px 6px;border-radius:4px;">${Math.round(n)}%</span>`;
        //                     }
        //                 },
        //                 // { data: 'Red Margin', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 { data: 'Channel Percentage', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 // {
        //                 //     data: 'Channel Percentage',
        //                 //     render: function (v, t, row) {
        //                 //         return `<input type="integer" class="form-control form-control-sm channel-percentage-input"
        //                 //                     value="${v || ''}" data-channel="${row['Channel']}"  style="min-width: 100px;" placeholder="Enter Percentage">`;
        //                 //     }
        //                 // },
        //                 {
        //                     data: 'NR',
        //                     render: function (v, t, row) {
        //                         const checked = toNum(v) === 1 ? 'checked' : '';
        //                         return `<input type="checkbox" class="checkbox-nr" data-channel="${row['Channel']}" ${checked}>`;
        //                     }
        //                 },
        //                 {
        //                     data: 'type',
        //                     render: function (v, t, row) {
        //                         return `<span style="display:inline-block; min-width:100px;">${v ? v : '-'}</span>`;
        //                     }
        //                 },

        //                 // {
        //                 //     data: 'type',
        //                 //     render: function (v, t, row) {
        //                 //         return `<input type="text" class="form-control form-control-sm type-input"
        //                 //                     value="${v || ''}" data-channel="${row['Channel']}"  style="min-width: 100px;" placeholder="Enter Type">`;
        //                 //     }
        //                 // },
        //                 { data: 'Listing Counts', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 {
        //                     data: 'W/Ads',
        //                     render: function (v, t, row) {
        //                         const checked = toNum(v) === 1 ? 'checked' : '';
        //                         return `<input type="checkbox" class="checkbox-wads" data-channel="${row['Channel']}" ${checked}>`;
        //                     }
        //                 },
        //                 // { data: '0 Sold SKU Count', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 // { data: 'Sold Sku Count',   render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 // { data: 'Brand Registry',   render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
        //                 {
        //                     data: 'Update',
        //                     render: function (v, t, row) {
        //                         const checked = toNum(v) === 1 ? 'checked' : '';
        //                         return `<input type="checkbox" class="checkbox-update" data-channel="${row['Channel']}" ${checked}>`;
        //                     }
        //                 },
        //                 {
        //                     data: 'Ac Health',
        //                     render: function (data) {
        //                         return data ? `<a href="${data}" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a>` : '';
        //                     }
        //                 },
        //                 {
        //                     data: null,
        //                     render: function (_d, _t, row, meta) {
        //                         return `
        //                         <div class="d-flex justify-content-center">
        //                             <button class="btn btn-sm btn-outline-primary edit-btn me-1"
        //                                     title="Edit" data-index="${meta.row}" data-channel="${row['Channel'] || ''}">
        //                             <i class="fas fa-edit"></i>
        //                             </button>
        //                             <button class="btn btn-sm btn-outline-danger delete-btn" title="Archive">
        //                             <i class="fa fa-archive"></i>
        //                             </button>
        //                         </div>`;
        //                     }
        //                 }
        //             ],
        //             responsive: true,
        //             language: {
        //                 processing: "Loading data, please wait...",
        //                 emptyTable: "",
        //                 zeroRecords: "",
        //             }
        //         });

        //         return table;
        //     } catch (e) {
        //         console.error('Error initializing DataTable:', e);
        //         return null;
        //     }
        // }

        
        function initializeDataTable() {
            try {
                if (!jq('#channelTable').length) {
                    console.error('Table element not found');
                    return null;
                }

                // If already initialized, destroy cleanly so we can rebuild
                if (jq.fn.DataTable.isDataTable('#channelTable')) {
                    jq('#channelTable').DataTable().clear().destroy();
                    jq('#channelTable').empty();
                }

                // --- helpers ---
                const toNum = (v, def = 0) => {
                    const n = parseFloat(String(v).replace(/,/g, ''));
                    return Number.isFinite(n) ? n : def;
                };
                const pick = (obj, keys, def = '') => {
                    for (const k of keys) {
                        const v = obj[k];
                        if (v !== undefined && v !== null && v !== '') return v;
                    }
                    return def;
                };
                const pctFix = (v) => {
                    let n = toNum(v, 0);
                    if (Math.abs(n) <= 1) n = n * 100;
                    if (!Number.isFinite(n)) n = 0;
                    return n;
                };

                // --- define columns (same order as you're using) ---
                const columns = [
                    {
                        data: 'Channel',
                        render: function(data, type, row) {
                            if (!data) return '';
                            const channelName = data.trim().toLowerCase();
                            const routeMap = {
                                'amazon': '/overall-amazon',
                                'amazon fba': '/overall-amazon-fba',
                                'ebay': '/ebay',
                                'ebaytwo': '/ebayTwoAnalysis',
                                'ebaythree': '/ebayThreeAnalysis',
                                'temu': '/temu',
                                'macys': '/macys',
                                'wayfair': '/Wayfair',
                                'reverb': '/reverb',
                                'shopify b2c': '/shopifyB2C',
                                'doba': '/doba',
                                'walmart': '/walmartAnalysis',
                                'bestbuy usa': '/bestbuyusa-analytics',
                                'shein': '/sheinAnalysis',
                                'tiktok shop': '/tiktokAnalysis',
                                'aliexpress': '/aliexpressAnalysis',
                            };
                            const routeUrl = routeMap[channelName];
                            return routeUrl
                                ? `<a href="${routeUrl}" target="_blank" style="color: #007bff; text-decoration: underline;">${data}</a>`
                                : `<div class="d-flex align-items-center"><span>${data}</span></div>`;
                        }
                    },
                    {
                        data: 'R&A',
                        render: function (v, t, row) {
                            const checked = toNum(v) === 1 ? 'checked' : '';
                            return `<input type="checkbox" class="ra-checkbox" data-channel="${row['Channel']}" ${checked}>`;
                        }
                    },
                    {
                        data: 'sheet_link',
                        render: function(data, type, row) {
                            const sheetLink = data || '';
                            return `<div style="display:flex; align-items:center; gap:6px;">${sheetLink ? `<a href="${sheetLink}" target="_blank" class="btn btn-sm btn-success">🔗</a>` : ''}</div>`;
                        }
                    },
                    // L30 Sales column (IMPORTANT: this returns numeric when type === 'sort' so ordering works)
                    {
                        data: 'L30 Sales',
                        render: function (data, type) {
                            const n = toNum(data);
                            if (type === 'sort' || type === 'type') return n; // numeric for sorting
                            return `<span class="metric-value">${n.toLocaleString('en-US')}</span>`;
                        }
                    },
                    {
                        data: 'Growth',
                        render: function (v) {
                            const n = pctFix(v);
                            if (!Number.isFinite(n)) return '-';
                            let bg = '', color = 'black';
                            if (n < 0)              { bg = '#ff0000'; color = 'white'; }
                            else if (n < 10)        { bg = '#ffff00'; }
                            else if (n < 20)        { bg = '#00ffff'; }
                            else if (n < 50)        { bg = '#00ff00'; }
                            else                    { bg = '#ff00ff'; color = 'white'; }
                            return `<span style="background:${bg};color:${color};padding:2px 6px;border-radius:4px;">${Math.round(n)}%</span>`;
                        }
                    },
                    { data: 'L30 Orders', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },

                    // Gprofit% column: numeric sort enabled
                    {
                        data: 'Gprofit%',
                        render: function (v, type) {
                            const n = pctFix(v);
                            if (type === 'sort' || type === 'type') return n; // numeric for sorting
                            let bg = '', color = 'white';
                            if (n < 15) { bg = '#ff0000'; }
                            else if (n >= 15 && n < 25) { bg = '#ffff00'; color = 'black'; }
                            else if (n >= 25 && n < 40) { bg = '#00ff00'; color = 'black'; }
                            else { bg = '#8000ff'; }
                            return `<span style="background:${bg};color:${color};padding:2px 6px;border-radius:4px;">${Math.round(n)}%</span>`;
                        }
                    },

                    {
                        data: 'G ROI%',
                        render: function (v) {
                            const n = pctFix(v);
                            let bg = '', color = 'black';
                            if (n <= 50) { bg = '#ff0000'; color = 'white'; }
                            return `<span style="background:${bg};color:${color};padding:2px 6px;border-radius:4px;">${Math.round(n)}%</span>`;
                        }
                    },
                    { data: 'Channel Percentage', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
                    {
                        data: 'NR',
                        render: function (v, t, row) {
                            const checked = toNum(v) === 1 ? 'checked' : '';
                            return `<input type="checkbox" class="checkbox-nr" data-channel="${row['Channel']}" ${checked}>`;
                        }
                    },
                    {
                        data: 'type',
                        render: function (v) {
                            return `<span style="display:inline-block; min-width:100px;">${v ? v : '-'}</span>`;
                        }
                    },
                    { data: 'Listing Counts', render: v => `<span class="metric-value">${toNum(v).toLocaleString('en-US')}</span>` },
                    {
                        data: 'W/Ads',
                        render: function (v, t, row) {
                            const checked = toNum(v) === 1 ? 'checked' : '';
                            return `<input type="checkbox" class="checkbox-wads" data-channel="${row['Channel']}" ${checked}>`;
                        }
                    },
                    {
                        data: 'Update',
                        render: function (v, t, row) {
                            const checked = toNum(v) === 1 ? 'checked' : '';
                            return `<input type="checkbox" class="checkbox-update" data-channel="${row['Channel']}" ${checked}>`;
                        }
                    },
                    {
                        data: 'Ac Health',
                        render: function (data) {
                            return data ? `<a href="${data}" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a>` : '';
                        }
                    },
                    {
                        data: null,
                        render: function (_d, _t, row, meta) {
                            return `
                                <div class="d-flex justify-content-center">
                                    <button class="btn btn-sm btn-outline-primary edit-btn me-1" title="Edit" data-index="${meta.row}" data-channel="${row['Channel'] || ''}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" title="Archive"><i class="fa fa-archive"></i></button>
                                </div>`;
                        }
                    }
                ];

                // Find indexes dynamically so you don't have to guess numbers
                const l30Index = columns.findIndex(c => c.data === 'L30 Sales');
                const gprofitIndex = columns.findIndex(c => c.data === 'Gprofit%');

                // initialize DataTable with ordering enabled
                const table = jq('#channelTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ordering: true,
                    // don't rely solely on this index number in config; we'll enforce order on initComplete too
                    order: (l30Index >= 0) ? [[l30Index, 'desc']] : [], 
                    searching: true,
                    pageLength: 50,
                    destroy: true,
                    ajax: {
                        url: '/channels-master-data',
                        type: "GET",
                        data: function (d) {
                            jq('#customLoader').hide();
                            jq('#channelTableWrapper').show();
                            d.channel     = jq('#channelSearchInput').val();
                            d.search      = jq('#searchInput').val();
                            d.sort_by     = window.currentSortColumn || null;
                            d.sort_order  = window.currentSortOrder || null;
                        },
                        dataSrc: function (json) {
                            if (!json || !json.data) return [];

                            return json.data.map(item => {
                                const l60Sales = toNum(pick(item, ['L-60 Sales', 'L60 Sales', 'l60_sales', 'A_L60', 'l60sales'], 0), 0);
                                const l30Sales = toNum(pick(item, ['L30 Sales', 'l30_sales', 'T_Sale_l30', 'l30sales'], 0), 0);

                                const l60Orders = toNum(pick(item, ['L60 Orders','l60_orders', 'A_L60_orders', 'l60orders'], 0), 0);
                                const l30Orders = toNum(pick(item, ['L30 Orders','l30_orders', 'A_L30_orders', 'l30orders'], 0), 0);

                                let growth = pick(item, ['growth', 'Growth'], null);
                                let gprofit = pctFix(pick(item, ['gprofit_percentage', 'PFT_percentage', 'gprofit', 'Gprofit%'], 0));
                                let groi    = pctFix(pick(item, ['G Roi', 'g_roi_percentage', 'roi'], 0));
                                let gprofitL60  = pctFix(pick(item, ['Gprofitl60'], 0));
                                let cogs = pctFix(pick(item, ['cogs'], 0));

                                return {
                                    'Channel': pick(item, ['channel', 'Channel', 'Channel '], ''),
                                    'Link': pick(item, ['link', 'url', 'URL LINK', 'url_link'], ''),
                                    'sheet_link': pick(item, ['sheet_link', 'sheet_url', 'sheet'], ''),
                                    'R&A': toNum(pick(item, ['ra', 'R&A', 'R_and_A'], 0), 0),
                                    'L-60 Sales': l60Sales,
                                    'L30 Sales': l30Sales,
                                    'Growth': growth,
                                    'L60 Orders': l60Orders,
                                    'L30 Orders': l30Orders,
                                    'Gprofit%': gprofit,
                                    'GprofitL30': gprofit,
                                    'GprofitL60': gprofitL60,
                                    'G ROI%': groi,
                                    'Red Margin': toNum(pick(item, ['red_margin', 'Total_pft', 'total_pft'], 0), 0),
                                    'NR': toNum(pick(item, ['nr','NR'], 0), 0),
                                    'type': pick(item, ['type'], ''),
                                    'Listing Counts': toNum(pick(item, ['listing_counts', 'listed_count', 'list_count'], 0), 0),
                                    'W/Ads': toNum(pick(item, ['w_ads', 'W/Ads','with_ads', 'ads'], 0), 0),
                                    '0 Sold SKU Count': toNum(pick(item, ['zero_sku', 'zero_sku_count', 'zero_sold_sku'], 0), 0),
                                    'Sold Sku Count': toNum(pick(item, ['sold_sku', 'sold_sku_count'], 0), 0),
                                    'Brand Registry': toNum(pick(item, ['brand_registry', 'brandregistry'], 0), 0),
                                    'Update': toNum(pick(item, ['update_flag', 'update','Update'], 0), 0),
                                    'Ac Health': pick(item, ['account_health', 'ac_health', 'accounthealth'], ''),
                                    'Channel Percentage': pctFix(pick(item, ['channel_percentage'], 0), 0),
                                    'cogs': cogs,
                                };
                            });
                        },
                        error: function (xhr, error, thrown) {
                            console.log("AJAX error:", error, thrown);
                        }
                    },
                    columns: columns,
                    responsive: true,

                    // ensure default sort is applied after table init (fixes timing/responsive issues)
                    initComplete: function () {
                        try {
                            if (l30Index >= 0) {
                                // set primary default order to L30 Sales descending
                                this.api().order([[l30Index, 'desc']]).draw(false);
                            }
                        } catch (err) {
                            console.warn('Could not set default order:', err);
                        }
                    },

                    language: {
                        processing: "Loading data, please wait...",
                        emptyTable: "",
                        zeroRecords: "",
                    }
                });

                return table;
            } catch (e) {
                console.error('Error initializing DataTable:', e);
                return null;
            }
        }



        function drawSalesTrendChart() {
            fetch('/sales-trend-data')
                .then(response => response.json())
                .then(json => {
                    const chartDataFromAPI = json.chartData || [];
                    if (!chartDataFromAPI.length) return alert("No sales data found");

                    // Prepare chart data with separate tooltips for each series
                    const chartArray = [
                        [
                            'Date', 
                            'L30 Sales', { role: 'tooltip', p: { html: true } },
                            // 'L60 Sales', { role: 'tooltip', p: { html: true } },
                            'GProfit (%)', { role: 'tooltip', p: { html: true } }
                        ]
                    ];

                    chartDataFromAPI.forEach(row => {
                        const l30 = Math.round(row.l30_sales);
                        // const l60 = Math.round(row.l60_sales);
                        const gprofit = Math.round(row.gprofit);

                        const tooltipL30 = `<div style="padding:5px"><strong>${row.date}</strong><br/>L30 Sales: ${l30}</div>`;
                        // const tooltipL60 = `<div style="padding:5px"><strong>${row.date}</strong><br/>L60 Sales: ${l60}</div>`;
                        const tooltipGProfit = `<div style="padding:5px"><strong>${row.date}</strong><br/>GProfit: ${gprofit}%</div>`;

                        chartArray.push([
                            row.date,
                            l30, tooltipL30,
                            // l60, tooltipL60,
                            gprofit, tooltipGProfit
                        ]);
                    });

                    google.charts.load('current', { packages: ['corechart'] });
                    google.charts.setOnLoadCallback(() => {
                        const data = google.visualization.arrayToDataTable(chartArray);

                        const options = {
                            title: '📈 Daily Sales Trend (L30 vs GProfit %)',
                            legend: { position: 'bottom', textStyle: { fontSize: 12 } },
                            focusTarget: 'datum',
                            tooltip: { isHtml: true },
                            hAxis: {
                                title: 'Date',
                                textStyle: { fontSize: 10 },
                                slantedText: true,
                                slantedTextAngle: 45
                            },
                            vAxes: {
                                0: { title: 'Sales ($)', textStyle: { color: '#1E88E5' } },
                                1: { title: 'GProfit (%)', textStyle: { color: '#43A047' } }
                            },
                            series: {
                                0: { targetAxisIndex: 0, color: '#1E88E5', lineWidth: 3, pointSize: 5, pointShape: 'circle' },
                                // 1: { targetAxisIndex: 0, color: '#FF7043', lineWidth: 3, pointSize: 5, pointShape: 'circle', lineDashStyle: [4, 4] },
                                2: { targetAxisIndex: 1, color: '#43A047', lineWidth: 3, pointSize: 5, pointShape: 'circle' }
                            },
                            chartArea: { left: 70, top: 50, width: '85%', height: '65%' },
                            backgroundColor: 'transparent'
                        };

                        const chartDiv = document.getElementById('salesTrendChart');
                        chartDiv.style.display = 'block';

                        const chart = new google.visualization.LineChart(chartDiv);
                        chart.draw(data, options);

                        window.addEventListener('resize', () => chart.draw(data, options));
                    });
                })
                .catch(err => console.error('Error fetching chart data:', err));
        }


        document.getElementById('showSalesGraph').addEventListener('click', function() {
            const chartDiv = document.getElementById('salesTrendChart');
            if (chartDiv.style.display === 'none') {
                drawSalesTrendChart();
                this.textContent = " 📈 Hide Daily Sales Graph";
            } else {
                chartDiv.style.display = 'none';
                this.textContent = "📈 Show Daily Sales Graph";
            }
        });


        function updatePlayButtonColor() {
            try {
                if (!table || !table.rows) return;

                const visibleRow = table.rows({
                    search: 'applied'
                }).nodes().to$();
                if (!visibleRow || !visibleRow.length) return;

                const raCheckbox = visibleRow.find('.ra-checkbox');
                if (raCheckbox.length) {
                    const isChecked = raCheckbox.prop('checked');
                    console.log('Checkbox checked (updatePlayButtonColor):', isChecked);

                    const playPauseBtn = jq('#play-pause');
                    if (playPauseBtn.length) {
                        playPauseBtn
                            .removeClass('btn-light btn-success btn-danger')
                            .addClass(isChecked ? 'btn-success' : 'btn-danger')
                            .css('color', 'white');
                    }
                }
            } catch (e) {
                console.error('Error in updatePlayButtonColor:', e);
            }
        }

        // Play/Pause functionality
        function startPlayback() {
            try {
                console.log('Starting playback...');

                if (!originalChannelData.length) {
                    console.error('No channel data available');
                    return;
                }

                // Get unique channels
                uniqueChannels = [...new Set(originalChannelData.map(item => item['Channel ']?.trim()))].filter(Boolean);
                console.log('Found unique channels:', uniqueChannels);

                uniqueChannelRows = uniqueChannels.map(channel => {
                    return originalChannelData.find(item => item['Channel ']?.trim() === channel);
                });

                if (uniqueChannelRows.length === 0) {
                    console.error('No unique channels found');
                    return;
                }

                currentChannelIndex = 0;
                isPlaying = true;

                // Configure table for single-channel view
                table.page.len(1).draw();
                table.search('').columns().search('').draw();

                // Show first channel
                showCurrentChannel();

                // Update UI
                const playAutoBtn = document.getElementById('play-auto');
                const playPauseBtn = document.getElementById('play-pause');

                if (playAutoBtn) playAutoBtn.style.display = 'none';
                if (playPauseBtn) playPauseBtn.style.display = 'block';

                table.column(1).visible(true);

                console.log('Playback started. Current channel:', uniqueChannelRows[currentChannelIndex]['Channel ']);

                setTimeout(() => {
                    updatePlayButtonColor();
                }, 500);
            } catch (e) {
                console.error('Error in startPlayback:', e);
            }
        }

        function stopPlayback() {
            try {
                console.log('Stopping playback...');
                isPlaying = false;

                // Restore table to normal view
                table.clear().rows.add(originalChannelData).draw();
                table.page.len(25).draw();

                // Update UI
                const playAutoBtn = document.getElementById('play-auto');
                const playPauseBtn = document.getElementById('play-pause');

                if (playAutoBtn) playAutoBtn.style.display = 'block';
                if (playPauseBtn) playPauseBtn.style.display = 'none';

                table.column(1).visible(false);
            } catch (e) {
                console.error('Error in stopPlayback:', e);
            }
        }

        function showCurrentChannel() {
            try {
                if (!isPlaying || !uniqueChannelRows.length) return;

                const currentRow = uniqueChannelRows[currentChannelIndex];
                console.log('Showing channel:', currentRow['Channel ']);

                if (currentRow) {
                    // Clear table and add only the current row
                    table.clear().rows.add([currentRow]).draw();

                    // Update search input to show current channel
                    const searchInput = document.getElementById('channelSearchInput');
                    if (searchInput) {
                        searchInput.value = currentRow['Channel ']?.trim() || '';
                    }

                    // Scroll to the top of the table
                    const tableContainer = document.getElementById('channelTable')?.parentElement;
                    if (tableContainer) {
                        tableContainer.scrollTop = 0;
                    }

                    setTimeout(() => {
                        updatePlayButtonColor();
                    }, 500);
                }
            } catch (e) {
                console.error('Error in showCurrentChannel:', e);
            }
        }

        function nextChannel() {
            try {
                if (!isPlaying) return;

                if (currentChannelIndex < uniqueChannelRows.length - 1) {
                    currentChannelIndex++;
                    console.log('Moving to next channel. New index:', currentChannelIndex);
                    showCurrentChannel();
                } else {
                    console.log('Reached end of channel list');
                    stopPlayback();
                }
            } catch (e) {
                console.error('Error in nextChannel:', e);
            }
        }

        function previousChannel() {
            try {
                if (!isPlaying) return;

                if (currentChannelIndex > 0) {
                    currentChannelIndex--;
                    console.log('Moving to previous channel. New index:', currentChannelIndex);
                    showCurrentChannel();
                }
            } catch (e) {
                console.error('Error in previousChannel:', e);
            }
        }

        // Sorting toggle logic
        let currentSortColumn = 'Channel ';
        let currentSortOrder = 'asc';

        function setupSorting() {
            try {
                let sortDirection = 'asc';

                const toggleSortBtn = document.getElementById('toggleSort');
                const sortDirectionText = document.getElementById('sortDirectionText');
                const sortMetricSelect = document.getElementById('sortMetric');

                if (toggleSortBtn && sortDirectionText) {
                    toggleSortBtn.addEventListener('click', function() {
                        sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';
                        sortDirectionText.textContent = sortDirection === 'asc' ? 'Low to High' : 'High to Low';
                        const metricIndex = parseInt(document.getElementById('sortMetric').value);
                        table.order([metricIndex, sortDirection]).draw();
                    });
                }

                if (sortMetricSelect) {
                    sortMetricSelect.addEventListener('change', function() {
                        const metricIndex = parseInt(this.value);
                        table.order([metricIndex, sortDirection]).draw();
                    });
                }

                // Initial sort
                if (sortMetricSelect) {
                    sortMetricSelect.value = '3';
                }
                table.order([3, sortDirection]).draw();
            } catch (e) {
                console.error('Error in setupSorting:', e);
            }
        }

        // Dropdown functionality
        function populateChannelDropdown(searchTerm = '') {
            try {
                const channelData = originalChannelData.map(row => row['Channel ']);
                const uniqueChannels = [...new Set(channelData)].filter(ch => ch && ch.trim() !== '');
                uniqueChannels.sort();

                const lowerSearch = searchTerm.toLowerCase();

                const sortedChannels = uniqueChannels.sort((a, b) => {
                    const aMatch = a.toLowerCase().includes(lowerSearch);
                    const bMatch = b.toLowerCase().includes(lowerSearch);
                    if (aMatch && !bMatch) return -1;
                    if (!aMatch && bMatch) return 1;
                    return a.localeCompare(b);
                });

                const dropdown = document.getElementById('channelSearchDropdown');
                if (!dropdown) return;

                dropdown.innerHTML = '';

                uniqueChannels.forEach(channel => {
                    const item = document.createElement('div');
                    item.className = 'dropdown-search-item';
                    item.dataset.value = channel;
                    item.textContent = channel;
                    dropdown.appendChild(item);
                });

                dropdown.style.display = 'block';
            } catch (e) {
                console.error('Error in populateChannelDropdown:', e);
            }
        }

        window.csrfToken = '{{ csrf_token() }}';

        // Initialize when DOM is ready
        jq(document).ready(function() {
            try {

                // First load the data
                jq.ajax({
                    url: '/channels-master-data',
                    type: "GET",
                    success: function(json) {
                        originalChannelData = json.data;

                        // Now initialize the DataTable
                        table = initializeDataTable();

                        if (!table) {
                            console.error('Failed to initialize DataTable');
                            return;
                        }

                        // Set up event handlers
                        const playAutoBtn = document.getElementById('play-auto');
                        const playPauseBtn = document.getElementById('play-pause');
                        const playForwardBtn = document.getElementById('play-forward');
                        const playBackwardBtn = document.getElementById('play-backward');
                        const channelSearchInput = document.getElementById('channelSearchInput');
                        const channelSearchDropdown = document.getElementById('channelSearchDropdown');

                        if (playAutoBtn) {
                            playAutoBtn.addEventListener('click', startPlayback);
                        }

                        if (playPauseBtn) {
                            playPauseBtn.addEventListener('click', stopPlayback);
                        }

                        if (playForwardBtn) {
                            playForwardBtn.addEventListener('click', nextChannel);
                        }

                        if (playBackwardBtn) {
                            playBackwardBtn.addEventListener('click', previousChannel);
                        }

                        // Hide pause button initially
                        if (playPauseBtn) {
                            playPauseBtn.style.display = 'none';
                        }

                        // Hide R&A column initially
                        table.column(1).visible(false);

                        // Setup sorting
                        setupSorting();

                        // Setup dropdown
                        if (channelSearchInput) {
                            channelSearchInput.addEventListener('focus', function() {
                                populateChannelDropdown();
                            });

                            channelSearchInput.addEventListener('input', function() {
                                const val = this.value.trim();
                                if (val === '') {
                                    table.column(0).search('').draw();
                                }
                                populateChannelDropdown(val);
                            });
                        }

                        if (channelSearchDropdown) {
                            channelSearchDropdown.addEventListener('click', function(e) {
                                if (e.target.classList.contains('dropdown-search-item')) {
                                    const selectedChannel = e.target.dataset.value;
                                    const searchInput = document.getElementById(
                                        'channelSearchInput');
                                    if (searchInput) {
                                        searchInput.value = selectedChannel;
                                    }
                                    this.style.display = 'none';
                                    table.column(0).search(selectedChannel, true, false).draw();
                                }
                            });
                        }

                        // Hide dropdown if clicked outside
                        document.addEventListener('click', function(e) {
                            if (!e.target.closest('.dropdown-search-container')) {
                                const dropdown = document.getElementById(
                                    'channelSearchDropdown');
                                if (dropdown) {
                                    dropdown.style.display = 'none';
                                }
                            }
                        });

                        // Update totals when table is filtered/searched
                        table.on('draw', function() {
                            var data = table.rows({
                                search: 'applied'
                            }).data().toArray();
                            updateAllTotals(data);
                        });

                        //update sheet link
                        $(document).on('change', '.sheet-link-input', function () {
                            const channel = $(this).data('channel'); // changed
                            const sheetLink = $(this).val();

                            $.ajax({
                                url: '/channel-master/update-sheet-link',
                                method: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    channel: channel,
                                    sheet_link: sheetLink
                                },
                                success: function (res) {
                                    if (res.status === 'success') {
                                        console.log('Sheet link updated');
                                    }
                                },
                                error: function (err) {
                                    console.error('Failed to update sheet link', err);
                                }
                            });
                        });

                        // Save "type" value on change
                        jq(document).on('change', '.type-input', function () {
                            const channel = jq(this).data('channel')?.trim();
                            const newValue = jq(this).val()?.trim();

                            if (!channel) return;

                            jq.ajax({
                                url: '/update-channel-type',
                                type: 'POST',
                                data: {
                                    channel: channel,
                                    type: newValue
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (response) {
                                    if (response.success) {
                                        console.log('Type updated successfully');
                                    } else {
                                        console.warn('Update failed:', response.message);
                                    }
                                },
                                error: function (xhr) {
                                    console.error('Error updating type:', xhr.responseText);
                                }
                            });
                        });


                        // Save "Channel Percentage" value on change
                        jq(document).on('change', '.channel-percentage-input', function () {
                            const channel = jq(this).data('channel')?.trim();
                            const newValue = jq(this).val()?.trim();

                            if (!channel) return;

                            jq.ajax({
                                url: '/update-channel-percentage',
                                type: 'POST',
                                data: {
                                    channel: channel,
                                    channel_percentage: newValue
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (response) {
                                    if (response.success) {
                                        console.log('Channel Percentage updated successfully');
                                    } else {
                                        console.warn('Update failed:', response.message);
                                    }
                                },
                                error: function (xhr) {
                                    console.error('Error updating type:', xhr.responseText);
                                }
                            });
                        });


                         //store data with form
                        jq('#addChannelModal .btn-primary').on('click', function () {
                            const channelName = $('#channelName').val().trim();
                            const channelUrl = $('#channelUrl').val().trim();
                            const type = $('#type').val().trim();

                            if (!channelName || !channelUrl || !type) {
                                alert("Both Channel Name and URL are required.");
                                return;
                            }

                            $.ajax({
                                url: '/channel_master/store',
                                method: 'POST',
                                data: {
                                    channel: channelName,
                                    sheet_link: channelUrl,
                                    type: type,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (res.success) {
                                        $('#addChannelModal').modal('hide');
                                        $('#channelForm')[0].reset();
                                        
                                        // table.ajax.reload();
                                        location.reload();
                                    } else {
                                        alert("Error: " + (res.message || 'Something went wrong.'));
                                    }
                                },
                                error: function () {
                                    alert("Error submitting form.");
                                }
                            });
                        });


                        // open edit modal
                        jq(document).on('click', '.edit-btn', function () {
                            
                            const rowData = table.row($(this).closest('tr')).data();
                            console.log(rowData,'rr');
                            
                            if (!rowData) {
                                console.error('No row data found');
                                return;
                            }
                            
                            // Normalize keys
                            // const id = rowData.id || '';
                            const channel = rowData["Channel "]?.trim() || rowData["Channel"] || '';
                            const sheetUrl = rowData["sheet_link"] || rowData["URL LINK"] || rowData["url"] || '';
                            const type = rowData["type"]?.trim() || rowData["type"] || '';
                            const percentage = rowData["Channel Percentage"];

                            // Populate modal fields
                            // $('#editChannelId').val(id);
                            $('#editChannelName').val(channel);
                            $('#editChannelUrl').val(sheetUrl);
                            $('#editType').val(type);
                            $('#editpercentage').val(percentage);
                            $('#originalChannel').val(channel);


                            // Show modal
                            $('#editChannelModal').modal('show');
                        });


                        //update channel data
                        $('#editChannelForm').on('submit', function (e) {
                            e.preventDefault();

                            // const id = $('#editChannelId').val().trim();
                            const channel = $('#editChannelName').val().trim();
                            const sheetUrl = $('#editChannelUrl').val().trim();
                            const type = $('#editType').val().trim();
                            const percentage = $('#editpercentage').val().trim();
                            const originalChannel = $('#originalChannel').val().trim();
                            

                            if (!channel || !sheetUrl) {
                                alert("Both Channel Name and Sheet URL are required.");
                                return;
                            }

                            $.ajax({
                                url: `/channel_master/update`,
                                method: 'POST',
                                data: {
                                    channel: channel,
                                    sheet_url: sheetUrl,
                                    type: type,
                                    channel_percentage: percentage,
                                    original_channel: originalChannel,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (res.success) {
                                        $('#editChannelModal').modal('hide');
                                        $('#editChannelForm')[0].reset();
                                        location.reload(); // Refresh page to reflect changes
                                    } else {
                                        alert("Error: " + (res.message || 'Update failed.'));
                                    }
                                },
                                error: function () {
                                    alert("Something went wrong while updating.");
                                }
                            });
                        });

                        $(document).on('change', '.checkbox-nr, .checkbox-wads, .checkbox-update', function () {
                            const channel = $(this).data('channel');
                            const field = $(this).hasClass('checkbox-nr') ? 'nr' :
                                        $(this).hasClass('checkbox-wads') ? 'w_ads' :
                                        'update';
                            const value = $(this).is(':checked') ? 1 : 0;

                            $.ajax({
                                url: '/channels-master/toggle-flag',
                                method: 'POST',
                                data: {
                                    channel: channel,
                                    field: field,
                                    value: value
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (!res.success) {
                                        alert('Failed to update: ' + res.message);
                                    }
                                },
                                error: function () {
                                    alert('Server error while updating checkbox.');
                                }
                            });
                        });


                        // Handle checkbox changes
                        jq(document).on('change', '.ra-checkbox', function() {
                            const checkbox = this;
                            const isChecked = checkbox.checked;

                            const row = jq(checkbox).closest('tr');
                            const rowData = table.row(row).data();
                            const channel = rowData['Channel '] || null;

                            if (!channel) {
                                console.error('Channel not found in row data.');
                                return;
                            }

                            const confirmMsg =
                                `Are you sure you want to ${isChecked ? 'check' : 'uncheck'} the R&A box for "${channel}"?`;
                            if (!confirm(confirmMsg)) {
                                checkbox.checked = !isChecked;
                                return;
                            }

                            fetch('/update-checkbox', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': window.csrfToken,
                                    },
                                    body: JSON.stringify({
                                        channel: channel,
                                        checked: isChecked
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    console.log('Laravel proxy response:', data);
                                    alert('Google Sheet updated successfully');
                                })
                                .catch(err => {
                                    console.error('Error:', err);
                                    alert('Error updating checkbox in Google Sheet');
                                    checkbox.checked = !isChecked;
                                });
                        });
                    },
                    error: function(xhr, error, thrown) {
                        console.error('Error loading data:', error, thrown);
                    }
                });
            } catch (e) {
                console.error('Error in document ready:', e);
            }
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(async function(){
  const fetchUrl = '/sales-trend-data';
  const statusEl = document.getElementById('status');
  const ctx = document.getElementById('barChart').getContext('2d');
  const btnL30 = document.getElementById('toggleL30');
  const btnL60 = document.getElementById('toggleL60');

  let enabled = { l30: true, l60: false };
  let rawChartData = [];
  let chart = null;

  function setStatus(msg, isError = false) {
    statusEl.textContent = msg;
    statusEl.style.color = isError ? '#b00020' : '#666';
  }

  function updateButtons() {
    btnL30.classList.toggle('off', !enabled.l30);
    btnL60.classList.toggle('off', !enabled.l60);
  }

  function renderChart() {
    // Filter to show only rows where at least one enabled series has data
    const filtered = rawChartData.filter(row => {
      const hasL30 = enabled.l30 && Number(row.l30_sales) > 0;
      const hasL60 = enabled.l60 && Number(row.l60_sales) > 0;
      return hasL30 || hasL60;
    });

    if (filtered.length === 0) {
      if (chart) chart.destroy();
      setStatus('No data to display with current filters.');
      return;
    }

    const labels = filtered.map(r => r.date);
    const l30Data = filtered.map(r => Number(r.l30_sales ?? 0));
    const l60Data = filtered.map(r => Number(r.l60_sales ?? 0));

    const datasets = [];
    
    if (enabled.l30) {
      datasets.push({
        label: 'L30',
        data: l30Data,
        backgroundColor: 'rgba(54, 162, 235, 0.85)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1,
        borderRadius: 4
      });
    }
    
    if (enabled.l60) {
      datasets.push({
        label: 'L60',
        data: l60Data,
        backgroundColor: 'rgba(255, 159, 64, 0.85)',
        borderColor: 'rgba(255, 159, 64, 1)',
        borderWidth: 1,
        borderRadius: 4
      });
    }

    if (chart) chart.destroy();

    chart = new Chart(ctx, {
      type: 'bar',
      data: { labels, datasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { 
          mode: 'index', 
          intersect: false 
        },
        plugins: {
          legend: { 
            position: 'top',
            labels: {
              padding: 15,
              font: { size: 12 }
            }
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                const value = context.parsed.y ?? context.parsed;
                return context.dataset.label + ': $' + Number(value).toLocaleString('en-US', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            }
          }
        },
        scales: {
          x: {
            title: { 
              display: true, 
              text: 'Date',
              font: { size: 12, weight: 'bold' }
            },
            grid: { display: false },
            ticks: { 
              maxRotation: 45,
              minRotation: 45,
              autoSkip: true, 
              maxTicksLimit: 30,
              font: { size: 10 }
            }
          },
          y: {
            title: { 
              display: true, 
              text: 'Sales ($)',
              font: { size: 12, weight: 'bold' }
            },
            beginAtZero: true,
            grid: { color: 'rgba(0, 0, 0, 0.05)' },
            ticks: { 
              callback: (value) => '$' + Number(value).toLocaleString()
            }
          }
        }
      }
    });

    setStatus(`Showing ${filtered.length} dates — L30: ${enabled.l30 ? 'ON' : 'OFF'}, L60: ${enabled.l60 ? 'ON' : 'OFF'}`);
  }

  try {
    const res = await fetch(fetchUrl, { credentials: 'same-origin' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const payload = await res.json();
    rawChartData = Array.isArray(payload.chartData) ? payload.chartData : [];

    if (rawChartData.length === 0) {
      setStatus('No data available for the selected period.');
      return;
    }

    updateButtons();
    renderChart();
  } catch (err) {
    console.error(err);
    setStatus('Failed to load chart data — check console and API route.', true);
    return;
  }

  btnL30.addEventListener('click', () => {
    enabled.l30 = !enabled.l30;
    updateButtons();
    renderChart();
  });

  btnL60.addEventListener('click', () => {
    enabled.l60 = !enabled.l60;
    updateButtons();
    renderChart();
  });
})();
</script>

@endsection
