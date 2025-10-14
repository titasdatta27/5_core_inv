<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKU Match</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            /* max-width: 600px; */
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 32px 24px;
        }
        h1 {
            text-align: center;
            margin-bottom: 32px;
            color: #333;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            /* min-width: 75rem; Increased min-width for better column spacing */
            font-size: 16px;
        }
        th, td {
            padding: 16px 14px; /* More padding for better readability */
            text-align: left;
            font-weight: 500;
        }
        th {
            background: #f1f5f9;
            color: #222;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        tr:hover {
            background: #e0f2fe;
        }
        select.custom-dropdown {
            padding: 8px 12px;
            border: 1.5px solid #38bdf8;
            border-radius: 6px;
            background: #f0f9ff;
            color: #0c4a6e;
            font-weight: 600;
            font-size: 15px;
            transition: border 0.2s;
            outline: none;
            min-width: 260px;
            box-shadow: 0 1px 4px rgba(56,189,248,0.08);
        }
        select.custom-dropdown:focus {
            border: 2px solid #0ea5e9;
            background: #e0f2fe;
        }
        .sortable {
            cursor: pointer;
            user-select: none;
        }
        .sorted-asc::after {
            content: " ▲";
        }
        .sorted-desc::after {
            content: " ▼";
        }
        @media (max-width: 700px) {
            .container {
                padding: 12px 4px;
                max-width: 98vw;
            }
            th, td {
                padding: 10px 6px;
                font-size: 15px;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function sortTable(table, col, type = 'string') {
                const tbody = table.tBodies[0];
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const dir = table.getAttribute('data-sort-dir') === 'asc' ? 'desc' : 'asc';
                table.setAttribute('data-sort-dir', dir);
                rows.sort((a, b) => {
                    let aText = a.children[col].innerText.trim();
                    let bText = b.children[col].innerText.trim();
                    if(type === 'string') {
                        aText = aText.toLowerCase();
                        bText = bText.toLowerCase();
                        if(aText < bText) return dir === 'asc' ? -1 : 1;
                        if(aText > bText) return dir === 'asc' ? 1 : -1;
                        return 0;
                    }
                    if(type === 'number') {
                        return dir === 'asc'
                            ? parseFloat(aText) - parseFloat(bText)
                            : parseFloat(bText) - parseFloat(aText);
                    }
                    return 0;
                });
                rows.forEach(row => tbody.appendChild(row));
            }

            document.querySelectorAll('.sortable').forEach(function (th, idx) {
                th.addEventListener('click', function () {
                    const table = th.closest('table');
                    sortTable(table, idx, th.dataset.type || 'string');
                    // Remove sort indicators from all headers
                    table.querySelectorAll('.sortable').forEach(header => {
                        header.classList.remove('sorted-asc', 'sorted-desc');
                    });
                    // Add indicator to current
                    th.classList.add(table.getAttribute('data-sort-dir') === 'asc' ? 'sorted-asc' : 'sorted-desc');
                });
            });

            // Update button logic
            document.querySelectorAll('.update-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const row = btn.closest('tr');
                    const ebayListingSku = row.children[0].innerText.trim();
                    const dropdown = row.querySelector('select.custom-dropdown');
                    const selectedSku = dropdown ? dropdown.value : null;

                    if (!selectedSku) {
                        alert('Please select a Product Master SKU.');
                        return;
                    }

                    if (confirm(`Are you sure you want to update eBay Listing SKU "${ebayListingSku}" to "${selectedSku}"?`)) {
                        fetch("{{ route('sku-match.update') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                ebay_listing_sku: ebayListingSku,
                                product_master_sku: selectedSku
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success){
                                alert('SKU updated successfully!');
                                location.reload();
                            } else {
                                alert('Failed to update SKU.');
                            }
                        })
                        .catch(() => alert('Error updating SKU.'));
                    }
                });
            });

            // Initialize Select2 for all custom-dropdowns
            $('select.custom-dropdown').select2({
                minimumResultsForSearch: Infinity, // Hides the search box
                width: '100%'
            });

            document.querySelectorAll('.sku-select').forEach(function(select) {
                $(select).select2({
                    placeholder: "Select Product Master SKU",
                    allowClear: true,
                    width: 'resolve'
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Ebaylisting SKUs</h1>
        <div class="table-responsive">
            <table class="table" data-sort-dir="asc">
                <thead>
                    <tr>
                        <th class="sortable" data-type="string">Ebaylisting SKU</th>
                        <th class="sortable" data-type="string">Product Master SKU</th>
                        <th class="sortable" data-type="string">Case-Insensitive Match</th>
                        <th class="sortable" data-type="string">Assign Product Master SKU</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shopifySkus as $sku)
                        @php
                            $matched = $productMasterSkus->firstWhere('sku', $sku->sku);
                        @endphp
                        <tr>
                            <td>{{ $sku->sku }}</td>
                            <td>{{ $matched ? $matched->sku : '-' }}</td>
                            <td>-</td>
                            <td>
                                @if(!$matched)
                                    @php
                                        $shopifySkuNoSpace = str_replace(' ', '', $sku->sku);
                                        $similarSkus = $productMasterSkus->filter(function($pmSku) use ($shopifySkuNoSpace) {
                                            $pmSkuNoSpace = str_replace(' ', '', $pmSku->sku);
                                            return stripos($pmSkuNoSpace, $shopifySkuNoSpace) !== false;
                                        });
                                    @endphp
                                    <select class="custom-dropdown sku-select" name="product_master_sku" style="width:100%;">
                                        <option value="">Select Product Master SKU</option>
                                        @foreach($productMasterSkus as $pmSku)
                                            <option value="{{ $pmSku->sku }}">{{ $pmSku->sku }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!$matched)
                                    <button type="button" class="update-btn" style="padding:6px 18px;background:#38bdf8;color:#fff;border:none;border-radius:5px;font-weight:600;cursor:pointer;">
                                        Update
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>