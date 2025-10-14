@extends('layouts.vertical', ['title' => 'Movement Analysis', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css" rel="stylesheet">
    <style>
        /* Header styling */
        .tabulator .tabulator-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .tabulator .tabulator-header .tabulator-col {
            text-align: center;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
            padding: 12px 8px;
        }
        .tabulator-row {
            background-color: #ffffff !important; /* default white for all rows */
        }
        /* Cell styling */
        .tabulator .tabulator-cell {
            text-align: center;
            padding: 12px 8px;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }
        
        /* Row hover effect */
        .tabulator-row:hover {
            background-color: rgba(0,0,0,.075) !important;
        }
        
        /* Parent row styling */
        .parent-row {
            background-color: #DFF0FF !important;
            font-weight: 600;
        }
        
        /* Pagination styling */
        .tabulator-footer {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }

        
    </style>
@endsection

@section('content')
@include('layouts.shared.page-title', ['page_title' => 'Movement Analysis', 'sub_title' => 'Movement Analysis'])
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Movement Analysis</h4>
                </div>

                <div class="row mb-4 d-flex align-items-center justify-content-between">
                    <div class="col-md-4">
                        <div class="btn-group time-navigation-group" role="group">
                            <button id="play-backward" class="btn btn-light rounded-circle shadow-sm me-2" title="Previous parent">
                                <i class="fas fa-step-backward"></i>
                            </button>
                            <button id="play-pause" class="btn btn-light rounded-circle shadow-sm me-2" style="display: none;" title="Pause">
                                <i class="fas fa-pause"></i>
                            </button>
                            <button id="play-auto" class="btn btn-primary rounded-circle shadow-sm me-2" title="Play">
                                <i class="fas fa-play"></i>
                            </button>
                            <button id="play-forward" class="btn btn-light rounded-circle shadow-sm" title="Next parent">
                                <i class="fas fa-step-forward"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="search-input" class="form-label fw-semibold">Search</label>
                        <input type="text" id="search-input" class="form-control" placeholder="Search suppliers...">
                    </div>
                </div>

                <div id="movement-tabulator"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>
<script>
    const groupedSkuData = @json($groupedDataJson);
    let table;

    function buildTabulator(data) {
        table = new Tabulator("#movement-tabulator", {
            height: "500px",
            layout: "fitDataFill",
            pagination: true,
            paginationSize: 50,
            data: data,
            columns: [
                // {title: "#", formatter: "rownum", width: 60},
                {title: "Parent", field: "parent"},
                {title: "SKU", field: "sku"},
                {title: "INV", field: "INV", hozAlign: "right"},
                // {title: "Total Month", field: "total_months"},
                {title: "Avg M", field: "monthly_average"},
                {title: "MOQ", field: "moq"},
                {title: "MSL", field: "msl"},
                {
                    title: "TOTAL INV AMT", 
                    field: "lp", 
                    hozAlign: "right",
                    formatter: function(cell) {
                        let inv = cell.getRow().getData().INV || 0;
                        let lp = cell.getValue() || 0;
                        return (inv * lp).toFixed(2);
                    }
                },
                {title: "OV L30", field: "L30", hozAlign: "right"},
                {title: "DIL", formatter: (cell) => {
                    let l30 = cell.getRow().getData().L30 || 0;
                    let inv = cell.getRow().getData().INV || 1;
                    return (l30 / inv).toFixed(2) + "%";
                }},
                ...["jan","feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"].map(m => ({title: m.toUpperCase(), field: `months.${m}`})),
                {title: "Total", field: "total"},
                {
                    title: "Monthly Total", 
                    field: "monthly_average",
                    hozAlign: "right",
                    formatter: function(cell) {
                        let monthly = cell.getValue() || 0;
                        let lp = cell.getRow().getData().lp || 0;
                        return (monthly )
                        return (monthly * lp).toFixed(0);
                    }
                },
                {
                    title: "TOTAL MSL AMT", 
                    field: "msl",
                    hozAlign: "right",
                    formatter: function(cell) {
                        let msl = cell.getValue() || 0;
                        let lp = cell.getRow().getData().lp || 0;
                        return (msl * lp).toFixed(2);
                    }
                },
                {
                    title: "S-MSL", field: "s_msl", editor: "input",
                    cellEdited: function(cell) {
                        const data = cell.getRow().getData();
                        $.post('/update-smsl', {
                            sku: data.sku,
                            parent: data.parent,
                            column: 's_msl',
                            value: cell.getValue(),
                            _token: '{{ csrf_token() }}'
                        });
                    }
                }
            ],
            rowFormatter: function(row) {
                if ((row.getData().sku || '').toUpperCase().startsWith('PARENT ')) {
                    row.getElement().classList.add("parent-row");
                }
            },
        });
    }

    function fetchMovementData() {
        $.get('/movement-analysis-data-view', function(res) {
            let tableData = res.data ?? res;
            buildTabulator(tableData);
        });
    }

    $(document).ready(function() {
        fetchMovementData();

        $('#search-input').on('input', function () {
            const keyword = $(this).val().toLowerCase();
            table.setFilter([[
                {field: "parent", type: "like", value: keyword},
                {field: "sku", type: "like", value: keyword},
            ]]);
        });

        // Playback controls (if needed)
        const parentKeys = Object.keys(groupedSkuData);
        let currentIndex = 0;
        let isPlaying = false;

        function renderGroup(parentKey) {
            let rows = groupedSkuData[parentKey] || [];
            buildTabulator(rows);
        }

        $('#play-auto').click(() => {
            isPlaying = true;
            currentIndex = 0;
            renderGroup(parentKeys[currentIndex]);
            $('#play-pause').show();
            $('#play-auto').hide();
        });

        $('#play-forward').click(() => {
            if (!isPlaying) return;
            currentIndex = (currentIndex + 1) % parentKeys.length;
            renderGroup(parentKeys[currentIndex]);
        });

        $('#play-backward').click(() => {
            if (!isPlaying) return;
            currentIndex = (currentIndex - 1 + parentKeys.length) % parentKeys.length;
            renderGroup(parentKeys[currentIndex]);
        });

        $('#play-pause').click(() => {
            isPlaying = false;
            fetchMovementData();
            $('#play-auto').show();
            $('#play-pause').hide();
        });
    });
</script>
@endsection
