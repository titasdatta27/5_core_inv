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

@endsection

@section('content')
    @include('layouts.shared/page-title', [
        'page_title' => 'Live Pending Master',
        'sub_title' => 'Live Pending master Analysis',
    ])

    <!-- Summary Badges -->
    <div class="row ">
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Live Pending</h5>
                    <span class="badge bg-info" id="livePendingCountBadge">0</span>
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
@endsection

@section('script')
    <!-- 1. Load jQuery FIRST -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- 2. Load Tabulator -->
    <script src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>

    <!-- 3. Then load other dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
        function handleRACheckbox(checkbox, channel) {
            const isChecked = checkbox.checked;

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
                checkbox.checked = !isChecked; // Revert if failed
            });
        }

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
