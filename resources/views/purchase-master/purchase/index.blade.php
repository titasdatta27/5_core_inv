@extends('layouts.vertical', ['title' => 'Purchase'])
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<style>
    /* Pagination styling */
    .tabulator .tabulator-footer .tabulator-paginator .tabulator-page {
        padding: 8px 16px;
        margin: 0 4px;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .tabulator .tabulator-footer .tabulator-paginator .tabulator-page:hover {
        background: #e0eaff;
        color: #2563eb;
    }

    .tabulator .tabulator-footer .tabulator-paginator .tabulator-page.active {
        background: #2563eb;
        color: white;
    }
</style>
@endsection
@section('content')
@include('layouts.shared.page-title', ['page_title' => 'Purchase', 'sub_title' => 'Purchase'])

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div class="input-group" style="max-width: 320px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="purchase-search" class="form-control border-start-0" placeholder="Search VO Number, Supplier name...">
                    </div>
                    <div class="input-group" style="max-width: 320px;">
                        <input type="date" id="po-date-filter" class="form-control" placeholder="Filter by Purchase Date">
                    </div>
                    <button class="btn btn-danger d-none" id="delete-selected-btn">
                        <i class="fas fa-trash-alt me-1"></i> Delete Selected
                    </button>
                    <div class="d-flex flex-wrap gap-2">
                        <button id="add-new-row" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createPurchaseModal">
                            <i class="fas fa-plus-circle me-1"></i> Create Purchase
                        </button>
                    </div>
                </div>
                <div id="purchase-table"></div>
            </div>
        </div>
    </div>
</div>

{{-- purchase add modal --}}
<div class="modal fade" id="createPurchaseModal" tabindex="-1" aria-labelledby="createPurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered shadow-none">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="createPurchaseModalLabel">
                    <i class="fas fa-file-invoice me-2"></i> Create Purchase
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="purchaseForm" method="POST" action="{{ route('purchase.store') }}" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body">
                    {{-- Purchase Header Section --}}
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Voucher Number</label>
                            <input type="text" class="form-control" name="vo_number" value="{{ $voNumber }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select" name="supplier" id="supplierSelect" required>
                                <option value="" disabled selected>Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" name="warehouse" required>
                                <option value="" disabled selected>Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Product Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-nowrap" id="productTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Parent</th>
                                    <th>SKU</th>
                                    <th>QTY</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="productRowsWrapper">
                                <tr class="default-row">
                                    <td><input type="text" class="form-control" name="parent[]" readonly></td>
                                    <td><input type="text" class="form-control" name="sku[]" required></td>
                                    <td><input type="number" class="form-control qty-input" name="qty[]" min="0"></td>
                                    <td><input type="number" class="form-control rate-input" name="rate[]" min="0" step="0.01"></td>
                                    <td><input type="number" class="form-control amount-input" name="amount[]" readonly></td>
                                    <td class="text-center text-muted">—</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <td colspan="1"></td>
                                    <th><span id="qtyTotal">0</span></th>
                                    <td></td>
                                    <th><span id="amountTotal">0.00</span></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addProductRowBtn">
                            <i class="fas fa-plus-circle me-1"></i> Add Purchase Row
                        </button>
                    </div>
                </div>

                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- purchase items modal --}}
<!-- Items Modal -->
<div class="modal fade" id="itemsModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Voucher Item Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle text-center" id="modal-items-table">
            <thead class="table-light">
              <tr>
                <th>S.No</th>
                <th>Parent</th>
                <th>SKU</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
<script src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = new Tabulator("#purchase-table", {
        ajaxURL: "/purchase-data/list",
        ajaxConfig: "GET",
        layout: "fitColumns",
        pagination: true,
        paginationSize: 50,
        paginationMode: "local",
        movableColumns: false,
        resizableColumns: true,
        height: "500px",
        columns: [
            {
                formatter: "rowSelection",
                titleFormatter: "rowSelection",
                hozAlign: "center",
                headerSort: false,
                width: 50
            },
            {
                title: "S.No",
                formatter: "rownum",
                hozAlign: "center",
                width: 80,
                headerHozAlign: "center",
            },
            { 
                title: "Voucher Number",
                field:"vo_number",
                hozAlign: "center",
                headerHozAlign: "center",
            },
            { 
                title: "Purchase Date",
                field:"purchase_date",
                hozAlign: "center",
                headerHozAlign: "center",
            },
            { 
                title: "Supplier Name",
                field:"supplier_name",
                hozAlign: "center",
                headerHozAlign: "center",
            },
            { 
                title: "Warehouse Name",
                field:"warehouse_name",
                hozAlign: "center",
                headerHozAlign: "center",
            },
            {
                title: "Items",
                formatter: function() {
                    return "<button class='btn btn-sm btn-primary'><i class='fas fa-eye me-1'></i>View</button>";
                },
                cellClick: function(e, cell) {
                    let rowData = cell.getRow().getData();
                    showItemsModal(rowData.items);
                },
                hozAlign: "center",
                headerHozAlign: "center",
            }
        ]
    });
    
    table.on("rowSelectionChanged", function(data, rows){
        if(data.length > 0){
            $('#delete-selected-btn').removeClass('d-none');
        } else {
            $('#delete-selected-btn').addClass('d-none');
        }
    });

    document.getElementById("purchase-search").addEventListener("input", function (e) {
        const keyword = e.target.value.toLowerCase();

        table.setFilter([
            [
                { field: "vo_number", type: "like", value: keyword },
                { field: "supplier_name", type: "like", value: keyword },
            ]
        ]);
    });
    
    $('#delete-selected-btn').on('click', function() {
        const selectedData = table.getSelectedData();

        if (selectedData.length === 0) {
            alert('Please select at least one record to delete.');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${selectedData.length} selected records?`)) {
            return;
        }

        const ids = selectedData.map(row => row.id);

        $.ajax({
            url: '/purchase/delete',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                ids: ids
            },
            success: function(response) {
                table.deleteRow(ids);
                updateApprovalPendingCount();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    // Date filter
    document.getElementById("po-date-filter").addEventListener("change", function (e) {
        const selectedDate = e.target.value;
        if (selectedDate) {
            table.setFilter("purchase_date", "=", selectedDate);
        } else {
            table.clearFilter(true); // clear all filters
        }
    });

    const productTableBody = document.getElementById('productRowsWrapper');
    const addRowBtn = document.getElementById('addProductRowBtn');
    const qtyTotalEl = document.getElementById('qtyTotal');
    const amountTotalEl = document.getElementById('amountTotal');
    const supplierSelect = document.getElementById('supplierSelect');

    // Function to calculate amount for a row
    function calculateRowAmount(row) {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const rate = parseFloat(row.querySelector('.rate-input')?.value) || 0;
        row.querySelector('.amount-input').value = (qty * rate).toFixed(2);
    }

    // Function to update total QTY and Amount
    function updateTotals() {
        let totalQty = 0;
        let totalAmount = 0;

        document.querySelectorAll('#productRowsWrapper tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
            const amount = parseFloat(row.querySelector('.amount-input')?.value) || 0;
            totalQty += qty;
            totalAmount += amount;
        });

        qtyTotalEl.textContent = totalQty;
        amountTotalEl.textContent = totalAmount.toFixed(2);
    }

    // Function to create new product row
    function createProductRow() {
        return `
            <tr>
                <td><input type="text" class="form-control" name="parent[]" readonly></td>
                <td><input type="text" class="form-control" name="sku[]" required></td>
                <td><input type="number" class="form-control qty-input" name="qty[]" min="0"></td>
                <td><input type="number" class="form-control rate-input" name="rate[]" min="0" step="0.01"></td>
                <td><input type="number" class="form-control amount-input" name="amount[]" readonly></td>
                <td class="text-center">
                    <i class="fas fa-trash-alt text-danger delete-product-row-btn" style="cursor: pointer;"></i>
                </td>
            </tr>
        `;
    }

    // Function to create a row from item object (for supplier load)
    function createRowFromItem(item) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="parent[]" value="${item.parent ?? ''}" readonly></td>
                <td><input type="text" class="form-control" name="sku[]" value="${item.sku ?? ''}" required></td>
                <td><input type="number" class="form-control qty-input" name="qty[]" value="${item.qty ?? 0}" min="0"></td>
                <td><input type="number" class="form-control rate-input" name="rate[]" value="${item.price ?? 0}" min="0" step="0.01"></td>
                <td><input type="number" class="form-control amount-input" name="amount[]" value="${(item.qty * item.price).toFixed(2)}" readonly></td>
                <td class="text-center">
                    <i class="fas fa-trash-alt text-danger delete-product-row-btn" style="cursor: pointer;"></i>
                </td>
            </tr>
        `;
    }

    // On supplier change, fetch existing items
    supplierSelect.addEventListener('change', function () {
        const supplierId = this.value;
        if (!supplierId) return;

        fetch(`/purchase-orders/items-by-supplier/${supplierId}`)
            .then(response => response.json())
            .then(data => {
                productTableBody.innerHTML = ''; // Clear existing rows

                if (data.length === 0) {
                    productTableBody.innerHTML = createProductRow();
                } else {
                    data.forEach(item => {
                        productTableBody.insertAdjacentHTML('beforeend', createRowFromItem(item));
                        const newRow = productTableBody.lastElementChild;
                        calculateRowAmount(newRow);
                    });
                    updateTotals(); 
                }
                updateTotals();
            })
            .catch(err => {
                console.error('Error loading supplier items:', err);
                productTableBody.innerHTML = createProductRow(); // fallback
            });
    });

    // Auto calculate on input
    productTableBody.addEventListener('input', function (e) {
        const target = e.target;
        const row = target.closest('tr');

        // Qty/Rate change: recalculate
        if (target.classList.contains('qty-input') || target.classList.contains('rate-input')) {
            calculateRowAmount(row);
            updateTotals();
        }

        // SKU change: fetch parent
        if (target.name === 'sku[]') {
            const sku = target.value.trim();

            if (sku.length > 0) {
                fetch(`/product-master/get-parent/${sku}`)
                    .then(res => res.json())
                    .then(data => {
                        row.querySelector('input[name="parent[]"]').value = data.parent ?? '';
                    })
                    .catch(() => {
                        row.querySelector('input[name="parent[]"]').value = '';
                    });
            } else {
                row.querySelector('input[name="parent[]"]').value = '';
            }
        }
    });


    // Add new row
    addRowBtn.addEventListener('click', function () {
        const rowHTML = createProductRow();
        productTableBody.insertAdjacentHTML('beforeend', rowHTML);

        const newRow = productTableBody.lastElementChild;
        calculateRowAmount(newRow);   
        updateTotals();               
    });


    // Delete row (except default)
    productTableBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-product-row-btn')) {
            const row = e.target.closest('tr');
            if (!row.classList.contains('default-row')) {
                row.remove();
                updateTotals();
            }
        }
    });

    function showItemsModal(itemsJson) {
        const items = JSON.parse(itemsJson);
        const tbody = document.querySelector('#modal-items-table tbody');
        tbody.innerHTML = '';

        let fetchPromises = items.map((item, index) => {
            return fetch(`/product-master/get-parent/${item.sku}`)
                .then(res => res.json())
                .then(data => {
                    const parent = data.parent ?? '';
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${parent}</td>
                            <td>${item.sku}</td>
                            <td>${item.qty ?? ''}</td>
                            <td>${item.price ?? ''}</td>
                            <td>${item.amount ?? ''}</td>
                        </tr>
                    `);
                })
                .catch(() => {
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${index + 1}</td>
                            <td></td>
                            <td>${item.sku}</td>
                            <td>${item.qty ?? ''}</td>
                            <td>${item.price ?? ''}</td>
                            <td>${item.amount ?? ''}</td>
                        </tr>
                    `);
                });
        });

        Promise.all(fetchPromises).then(() => {
            $('#itemsModal').modal('show');
        });
    }



    // Initial totals
    updateTotals();
});
</script>
@endsection
