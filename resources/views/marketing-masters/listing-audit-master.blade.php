@extends('layouts.vertical', ['title' => 'Listing Audit Master', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])
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
            /* position: fixed; */
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
            /* Adjust as needed */
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

    .dataTables_length, .dataTables_filter{
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

    #channelTable input.form-control {
        min-width: 100px;
        padding: 6px 10px;
        font-size: 14px;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
        font-size: 5rem; /* Slightly larger font */
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        width: 100%;
        text-align: center;
    }

    .dataTables_processing {
        top: 100px !important;  /* Try 80–100px depending on your header height */
        z-index: 1000 !important;
        background: none !important;
        border: none;
    }


    
</style>
@endsection

@section('content')
    @include('layouts.shared/page-title', [
        'page_title' => 'Listing Audit Master',
        'sub_title' => 'Listing Audit master Analysis',
    ])
    <div class="container-fluid">
        
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

                    <a href="/export-listing-audit-csv" class="btn btn-primary"
                    style="background: linear-gradient(135deg, #4361ee, #3f37c9);">
                    <i class="mdi mdi-download me-1"></i> Download
                    </a>
                </div>

                <div class="col-auto">
                    <div class="dropdown-search-container" style="position: relative;">
                        <input type="text" class="form-control form-control-sm channel-search" placeholder="Search Channel" id="channelSearchInput">
                        <div class="dropdown-search-results" id="channelSearchDropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #ccc; display: none; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="customLoader" style="display: flex; justify-content: center; align-items: center; height: 300px;">
            <div class="spinner-border text-info" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <span class="ms-2">Loading datatable, please wait...</span>
        </div>
        
        <!-- Table Container -->
        <div class="table-container"  id="channelTableWrapper" style="display: none;">
        
            <div class="table-responsive" style="max-height: 500px; overflow: auto;">
                <table class="table table-hover table-striped mb-0" id="channelTable">
                    <thead  class="table sticky-top">
                        <tr>
                            <th>SL</th>
                            <th>Channel</th>
                            <th>R&A</th>
                            <th>Link</th>
                            <th>Not Listed</th>
                            <th>Not Live</th>
                            <th>Category Issue</th>
                            <th>Attr Not Filled</th>
                            <th>A+ Issue</th>
                            <th>Video Issue</th>
                            <th>Title Issue</th>
                            <th>Images</th>
                            <th>Description</th>
                            <th>Bullet Points</th>
                        </tr>
                    </thead>
                    <tbody>
                    <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
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
        let selectedExec = '';

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
            jq('#l60SalesCountBadge').text(Math.round(l60SalesTotal).toLocaleString('en-US'));
            jq('#l30SalesCountBadge').text(Math.round(l30SalesTotal).toLocaleString('en-US'));
            jq('#l60OrdersCountBadge').text(Math.round(l60OrdersTotal).toLocaleString('en-US'));
            jq('#l30OrdersCountBadge').text(Math.round(l30OrdersTotal).toLocaleString('en-US'));
            jq('#growthPercentageBadge').text(growthTotal.toFixed(0) + '%');
            // jq('#groiPercentageBadge').text(Math.round(calculateAverage(groiValues)) + '%');
            // jq('#l60SalesCountBadge').text('₹ ' + Math.round(l60SalesTotal).toLocaleString('en-IN'));
            // jq('#l60SalesCount').text(Math.round(l60SalesTotal).toLocaleString('en-US'));
            // jq('#l30SalesCount').text(Math.round(l30SalesTotal).toLocaleString('en-US'));
            // jq('#l60OrdersCount').text(Math.round(l60OrdersTotal).toLocaleString('en-US'));
            // jq('#l30OrdersCount').text(Math.round(l30OrdersTotal).toLocaleString('en-US'));
            
            // Calculate averages for percentages
            const calculateAverage = arr => arr.length ? 
                (arr.reduce((a, b) => a + b, 0) / arr.length * 100) : 0;
            
            // jq('#growthPercentageBadge').text(Math.round(calculateAverage(growthValues)) + '%');
            jq('#gprofitPercentage').text(Math.round(calculateAverage(gprofitValues)) + '%');
            jq('#groiPercentageBadge').text(Math.round(calculateAverage(groiValues)) + '%');
            
        }

        // Initialize DataTable
        function initializeDataTable() {
            // console.log('Initializing DataTable...');
            return jq('#channelTable').DataTable({
                processing: true,
                serverSide: false,
                ordering: true,
                searching: true,
                pageLength: 25,
                order: [[1, 'asc']],
                ajax: {
                    url: '/show-list-audit-master-data',
                    type: "GET",
                    data: function(d) {
                        $('#customLoader').hide();
                        $('#channelTableWrapper').show();
                        d.channel = jq('#channelSearchInput').val()?.toLowerCase()?.trim();
                        // d.exec = jq('#execSearchInput').val();
                        d.search = jq('#searchInput').val();
                        d.sort_by = jq('#sort_by').val();
                        d.sort_order = jq('#sort_order').val();
                        d.sort_by = currentSortColumn;
                        d.sort_order = currentSortOrder;
                    },
                    dataSrc: function(json) {
                         originalChannelData = json.data || [];

                        allChannelData = json.data;
                        if (!originalChannelData.length) {
                            originalChannelData = json.data;
                        }

                       return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.log("AJAX error:", error, thrown);
                    }
                },
                columns: [{
                        data: null,
                        title: 'SL',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'Channel ',
                        render: function(data, type, row) {
                            if (!data) return '';

                            const channelName = data.trim().toLowerCase();
                            // console.log(channelName, 'channels');
                             
                            // return `<div class="d-flex align-items-center channel-name"><span>${data}</span></div>`;
                            const routeMap = {
                                'amazon': '/listing-audit-amazon',
                                // 'amazon fba': '/overall-amazon-fba',
                                'ebay': '/listing-audit-ebay',
                                // 'temu': '/temu',
                                // 'macys': '/macys',
                                // 'wayfair': '/Wayfair',
                                // 'reverb': '/reverb',
                                // 'shopify b2c': '/shopifyB2C',
                                // 'doba ': '#',
                                // add more routes if needed
                            };

                            const routeUrl = routeMap[channelName];

                            if (routeUrl) {
                                return `<a href="${routeUrl}" target="_blank" style="color: #007bff; text-decoration: underline;">${data}</a>`;
                            } else {
                                return `<div class="d-flex align-items-center"><span>${data}</span></div>`;
                            }
                        }
                    },
                    
                    {
                        data: 'R&A',
                        visible: false,
                        render: function(data, type, row) {
                            const isChecked = data ? 'checked' : '';
                            return `<div class="ra-edit-container d-flex align-items-center">
                                    <input type="checkbox" class="ra-checkbox" ${isChecked}>
                                </div>`;
                        }
                    },
                    
                    {
                        data: 'URL LINK',
                        render: function(data, type, row) {
                           const safeUrl = data ?? '';
                            if (!data) return '';
                            return `
                                <a href="${data}" target="_blank">
                                    <i class="bi bi-box-arrow-up-right link-icon"></i>
                                </a>
                                <span class="hidden-url d-none">${data}</span>
                            `;

                        }
                    },
                    { data: 'not_listed', title: 'Not Listed' },
                    { data: 'not_live', title: 'Not Live' },
                    { data: 'category_issue', title: 'Category Issue' },
                    { data: 'attr_not_filled', title: 'Attr Not Filled' },
                    { data: 'a_plus_issue', title: 'A+ Issue' },
                    { data: 'video_issue', title: 'Video Issue' },
                    { data: 'title_issue', title: 'Title Issue' },
                    { data: 'images', title: 'Images' },
                    { data: 'description', title: 'Description' },
                    { data: 'bullet_points', title: 'Bullet Points' },
                ],
                drawCallback: function(settings) {
                    let api = this.api();
                    api.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });

                },
                language: {
                    processing: "Loading data, please wait...",
                    emptyTable: "",
                    zeroRecords: "",  
                },
                responsive: true,
                initComplete: function() {
                    // console.log('DataTable initialized successfully');
                    // Add buttons to DOM
                   
                }
            });

        }

        //store amazon listing audit count
        jq(document).ready(function () {
            // Step 1: Trigger the controller to store Amazon listing audit summary
            $.ajax({
                url: '/store-list-audit-amazon-data',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log('Audit summary stored successfully:', response);
                    
                    // Step 2: After storing, reload DataTable so it shows fresh summary
                    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#channelTable')) {
                        $('#channelTable').DataTable().ajax.reload();
                    }
                },
                error: function (xhr) {
                    console.error('Failed to store audit summary:', xhr.responseText);
                }
            });
        });


        // store eBay listing audit data 
        jq(document).ready(function () {
            $.ajax({
                url: '/store-list-audit-ebay-data',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log('Audit summary stored successfully:', response);
                    
                    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#channelTable')) {
                        $('#channelTable').DataTable().ajax.reload();
                    }
                },
                error: function (xhr) {
                    console.error('Failed to store audit summary:', xhr.responseText);
                }
            });
        });


        //search
        jq(document).ready(function () {

            // Search Channel
            jq('#searchInput').on('input', function () {
                table.ajax.reload();
            });
        });

        //sort
        let currentSortColumn = 'Channel ';
        let currentSortOrder = 'asc';

        jq('#channelMasterTable thead').on('click', 'th', function () {
            const columnIndex = $(this).index();
            const colName = table.settings().init().columns[columnIndex].data;

            currentSortOrder = (currentSortColumn === colName && currentSortOrder === 'asc') ? 'desc' : 'asc';
            currentSortColumn = colName;

            table.ajax.reload();
        });

        
        function updatePlayButtonColor() {
            const visibleRow = table.rows({ search: 'applied' }).nodes().to$();
            const raCheckbox = visibleRow.find('.ra-checkbox');

            if (raCheckbox.length) {
                const isChecked = raCheckbox.prop('checked');
                console.log('Checkbox checked (updatePlayButtonColor):', isChecked);

                jq('#play-pause')
                    .removeClass('btn-light btn-success btn-danger')
                    .addClass(isChecked ? 'btn-success' : 'btn-danger')
                    .css('color', 'white');
            } else {
                console.warn('No checkbox found in visible row!');
            }
        }

        // Play/Pause functionality
        function startPlayback() {
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
            table.page.len(1).draw(); // Show only 1 row per page
            table.search('').columns().search('').draw(); // Clear all filters

            // Show first channel
            showCurrentChannel();

            // Update UI
            jq('#play-auto').hide();
            jq('#play-pause').show();
            table.column(2).visible(true);

            console.log('Playback started. Current channel:', uniqueChannelRows[currentChannelIndex]['Channel ']);

            setTimeout(() => {
                updatePlayButtonColor(); // this ensures correct color on first play
            }, 500); 

        }

        function stopPlayback() {
            console.log('Stopping playback...');
            isPlaying = false;

            // Restore table to normal view
            table.clear().rows.add(originalChannelData).draw();
            table.page.len(25).draw(); // Show default number of rows

            // Update UI
            jq('#play-pause').hide();
            jq('#play-auto').show();
            table.column(2).visible(false);
        }

        function showCurrentChannel() {


            if (!isPlaying || !uniqueChannelRows.length) return;

            const currentRow = uniqueChannelRows[currentChannelIndex];
            console.log('Showing channel:', currentRow['Channel ']);

            if (currentRow) {
                // Clear table and add only the current row
                table.clear().rows.add([currentRow]).draw();

                // Update search input to show current channel
                jq('#channelSearchInput').val(currentRow['Channel ']?.trim());

                // Scroll to the top of the table
                jq('#channelTable').parent().scrollTop(0);

                setTimeout(() => {
                    updatePlayButtonColor(); // ensures color updates on navigation
                }, 500);
            }
        }

        function nextChannel() {
            if (!isPlaying) return;

            if (currentChannelIndex < uniqueChannelRows.length - 1) {
                currentChannelIndex++;
                console.log('Moving to next channel. New index:', currentChannelIndex);
                showCurrentChannel();
            } else {
                console.log('Reached end of channel list');
                stopPlayback();
            }
        }

        function previousChannel() {
            if (!isPlaying) return;

            if (currentChannelIndex > 0) {
                currentChannelIndex--;
                console.log('Moving to previous channel. New index:', currentChannelIndex);
                showCurrentChannel();
            }
        }

        // Sorting toggle logic
        function setupSorting() {
            let sortDirection = 'asc'; // Default: Low to High

            jq('#toggleSort').on('click', function() {
                sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';
                jq('#sortDirectionText').text(sortDirection === 'asc' ? 'Low to High' : 'High to Low');
                const metricIndex = parseInt(jq('#sortMetric').val());
                table.order([metricIndex, sortDirection]).draw();
            });

            jq('#sortMetric').on('change', function() {
                const metricIndex = parseInt(jq(this).val());
                table.order([metricIndex, sortDirection]).draw();
            });

            // Initial sort
            jq('#sortMetric').val('4'); // Default to L-60 Sales (column index 4)
            table.order([4, sortDirection]).draw();
        }

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
                table.search('').draw(); // Clear the search if input is cleared
            }
        });

        jq('#execSearchInput').on('input', function() {
            const val = jq(this).val().trim();
             if (val === '') {
                table.column(1).search('').draw(); // Clear filter if empty
            }
            populateExecDropdown(val); // Pass the search term to sort dropdown
        });
        

        // jq('#execSearchInput').on('focus', function() {
        //     populateChannelDropdown(jq(this).val().trim());
        // });

         window.csrfToken = '{{ csrf_token() }}';

        // Initialize when DOM is ready
        jq(document).ready(function() {
            // console.log('Document ready - initializing...');

            // First load the data
            jq.ajax({
                url: '/channels-master-data',
                type: "GET",
                success: function(json) {
                    originalChannelData = json.data;

                    // Now initialize the DataTable
                    table = initializeDataTable();


                    jq(document).on('change', '.ra-checkbox', function () {
                        const checkbox = this;
                        const isChecked = checkbox.checked;

                        const table = jq('#channelTable').DataTable();
                        const row = jq(checkbox).closest('tr');
                        const rowData = table.row(row).data();
                        const channel = rowData['Channel '] || null;

                        if (!channel) {
                            console.error('Channel not found in row data.');
                            return;
                        }

                        // Ask for confirmation
                        const confirmMsg = `Are you sure you want to ${isChecked ? 'check' : 'uncheck'} the R&A box for "${channel}"?`;
                        if (!confirm(confirmMsg)) {
                            checkbox.checked = !isChecked; // Revert change if cancelled
                            return;
                        }

                        // Send to Laravel proxy
                        fetch('/update-ra-checkbox', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken, // from your blade
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
                            checkbox.checked = !isChecked; // Revert if failed
                        });
                    });




                    // Set up event handlers
                    jq('#play-auto').on('click', startPlayback);
                    jq('#play-pause').on('click', stopPlayback);
                    jq('#play-forward').on('click', nextChannel);
                    jq('#play-backward').on('click', previousChannel);

                    // Hide pause button initially
                    jq('#play-pause').hide();

                    // Hide R&A column initially
                    table.column(2).visible(false);

                    // Setup sorting
                    setupSorting();

                    // Setup dropdowns
                    jq('#channelSearchInput').on('focus', function() {

                        populateChannelDropdown();
                    });

                    jq('#channelSearchDropdown').on('click', '.dropdown-search-item', function() {
                        const selectedChannel = jq(this).data('value').toString().trim();
                        console.log('[Dropdown Click] Selected:', selectedChannel);
                        jq('#channelSearchInput').val(selectedChannel);
                        jq('#channelSearchDropdown').hide();

                        table.search(selectedChannel).draw();
                        // table.column(0).search(selectedChannel, true, false).draw();
                    });

                    jq('#channelSearchInput').on('input', function() {
                        const val = jq(this).val().trim();
                        if (val === '') {
                            table.column(0).search('').draw();
                        }
                    });

                    jq('#execSearchInput').on('focus', function() {
                        populateExecDropdown();
                    });

                    jq('#execSearchDropdown').on('click', '.dropdown-search-item', function() {
                        const selected = jq(this).data('value').toString().trim();
                        selectedExec = selected;
                        jq('#execSearchInput').val(selected);
                        jq('#execSearchDropdown').hide();
                        table.ajax.reload();
                    });

                    jq('#execSearchInput').on('input', function() {
                        const val = jq(this).val().trim();
                        if (val === '') {
                            selectedExec = '';
                            table.ajax.reload();
                        }
                    });

                    // Hide dropdown if clicked outside
                    jq(document).on('click', function(e) {
                        if (!jq(e.target).closest('.dropdown-search-container').length) {
                            jq('#channelSearchDropdown').hide();
                            // jq('#execSearchDropdown').hide();
                        }
                    });

                    // Update totals when table is filtered/searched
                    table.on('draw', function() {
                        var data = table.rows({search: 'applied'}).data().toArray();
                        updateAllTotals(data);
                    });

                },
                error: function(xhr, error, thrown) {
                    console.error('Error loading data:', error, thrown);
                }
            });
            
        });
    </script>
@endsection
