@extends('layouts.vertical', ['title' => 'Listing Audit Wayfair', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========== TABLE STRUCTURE ========== */
        .table-container {
            overflow-x: auto;
            overflow-y: visible;
            position: relative;
            max-height: 600px;
        }

        .custom-resizable-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .custom-resizable-table th,
        .custom-resizable-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            position: relative;
            white-space: nowrap;
            overflow: visible !important;
        }

        .custom-resizable-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            user-select: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* ========== RESIZABLE COLUMNS ========== */
        .resize-handle {
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            height: 100%;
            background: rgba(0, 0, 0, 0.1);
            cursor: col-resize;
            z-index: 100;
        }

        .resize-handle:hover,
        .resize-handle.resizing {
            background: rgba(0, 0, 0, 0.3);
        }

        /* ========== TOOLTIP SYSTEM ========== */
        .tooltip-container {
            position: relative;
            display: inline-block;
            margin-left: 8px;
        }

        .tooltip-icon {
            cursor: pointer;
            transform: translateY(1px);
        }

        .tooltip {
            z-index: 9999 !important;
            pointer-events: none;
        }

        .tooltip-inner {
            transform: translate(-5px, -5px) !important;
            max-width: 300px;
            padding: 6px 10px;
            font-size: 13px;
        }

        .bs-tooltip-top .tooltip-arrow {
            bottom: 0;
        }

        .bs-tooltip-top .tooltip-arrow::before {
            transform: translateX(5px) !important;
            border-top-color: var(--bs-tooltip-bg);
        }

        /* ========== COLOR CODED CELLS ========== */
        .dil-percent-cell {
            padding: 8px 4px !important;
        }

        .dil-percent-value {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }

        .dil-percent-value.red {
            background-color: #dc3545;
            color: white;
        }

        .dil-percent-value.blue {
            background-color: #3591dc;
            color: white;
        }

        .dil-percent-value.yellow {
            background-color: #ffc107;
            color: #212529;
        }

        .dil-percent-value.green {
            background-color: #28a745;
            color: white;
        }

        .dil-percent-value.pink {
            background-color: #e83e8c;
            color: white;
        }

        .dil-percent-value.gray {
            background-color: #6c757d;
            color: white;
        }

        /* ========== TABLE CONTROLS ========== */
        .table-controls {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }

        /* ========== SORTING ========== */
        .sortable {
            cursor: pointer;
        }

        .sortable:hover {
            background-color: #f1f1f1;
        }

        .sort-arrow {
            display: inline-block;
            margin-left: 5px;
        }

        /* ========== PARENT ROWS ========== */
        .parent-row {
            background-color: rgba(69, 233, 255, 0.1) !important;
        }

        /* ========== SKU TOOLTIPS ========== */
        .sku-tooltip-container {
            position: relative;
            display: inline-block;
        }

        .sku-tooltip {
            visibility: hidden;
            width: auto;
            min-width: 120px;
            background-color: #fff;
            color: #333;
            text-align: left;
            border-radius: 4px;
            padding: 8px;
            position: absolute;
            z-index: 1001;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
            white-space: nowrap;
        }

        .sku-tooltip-container:hover .sku-tooltip {
            visibility: visible;
            opacity: 1;
        }

        .sku-link {
            padding: 4px 0;
            white-space: nowrap;
        }

        .sku-link a {
            color: #0d6efd;
            text-decoration: none;
        }

        .sku-link a:hover {
            text-decoration: underline;
        }

        /* ========== DROPDOWNS ========== */
        .custom-dropdown {
            position: relative;
            display: inline-block;
        }

        .custom-dropdown-menu {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .custom-dropdown-menu.show {
            display: block;
        }

        .column-toggle-item {
            padding: 8px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .column-toggle-item:hover {
            background-color: #f8f9fa;
        }

        .column-toggle-checkbox {
            margin-right: 8px;
        }

        /* ========== LOADER ========== */
        .card-loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 100;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 0.25rem;
        }

        .loader-content {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .loader-text {
            margin-top: 15px;
            font-weight: 500;
            color: #333;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* ========== CARD BODY ========== */
        .card-body {
            position: relative;
        }

        /* ========== SEARCH DROPDOWNS ========== */
        .dropdown-search-container {
            position: relative;
        }

        .dropdown-search-results {
            position: absolute;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .dropdown-search-item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .dropdown-search-item:hover {
            background-color: #f8f9fa;
        }

        .no-results {
            color: #6c757d;
            font-style: italic;
        }

        /* ========== STATUS INDICATORS ========== */
        .status-circle {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
            border: 1px solid #fff;
        }

        .status-circle.default {
            background-color: #6c757d;
        }

        .status-circle.red {
            background-color: #dc3545;
        }

        .status-circle.yellow {
            background-color: #ffc107;
        }

        .status-circle.blue {
            background-color: #007bff;
        }

        .status-circle.green {
            background-color: #28a745;
        }

        .status-circle.pink {
            background-color: #e83e8c;
        }

        /* ========== FILTER CONTROLS ========== */
        .d-flex.flex-wrap.gap-2 {
            gap: 0.5rem !important;
            margin-bottom: 1rem;
        }

        .btn-sm i.fas {
            margin-right: 5px;
        }

        .manual-dropdown-container {
            position: relative;
            display: inline-block;
        }

        .manual-dropdown-container .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            min-width: 160px;
            padding: 5px 0;
            margin: 2px 0 0;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, .15);
            border-radius: 4px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
        }

        .manual-dropdown-container.show .dropdown-menu {
            display: block;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 8px 16px;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
        }

        .dropdown-item:hover {
            color: #16181b;
            text-decoration: none;
            background-color: #f8f9fa;
        }

        /* ========== MODAL SYSTEM ========== */
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1050;
            overflow: hidden;
            outline: 0;
            pointer-events: none;
        }

        .custom-modal.show {
            display: block;
        }

        .custom-modal-dialog {
            position: fixed;
            width: auto;
            min-width: 850px;
            max-width: 90vw;
            margin: 1.75rem auto;
            pointer-events: auto;
            z-index: 1051;
            transition: transform 0.3s ease-out;
            background-color: white;
            border-radius: 0.3rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .custom-modal-content {
            pointer-events: auto;
        }

        .custom-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            border-top-left-radius: 0.3rem;
            border-top-right-radius: 0.3rem;
            background-color: #f8f9fa;
        }

        .custom-modal-title {
            margin-bottom: 0;
            line-height: 1.5;
            font-size: 1.25rem;
        }

        .custom-modal-close {
            padding: 0;
            background-color: transparent;
            border: 0;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: #000;
            text-shadow: 0 1px 0 #fff;
            opacity: 0.5;
            cursor: pointer;
        }

        .custom-modal-close:hover {
            opacity: 0.75;
        }

        .custom-modal-body {
            position: relative;
            flex: 1 1 auto;
            padding: 1rem;
            overflow-y: auto;
            max-height: 70vh;
        }

        /* Multiple Modal Stacking */
        .custom-modal:nth-child(1) .custom-modal-dialog {
            top: 20px;
            right: 20px;
            z-index: 1051;
        }

        .custom-modal:nth-child(2) .custom-modal-dialog {
            top: 40px;
            right: 40px;
            z-index: 1052;
        }

        .custom-modal:nth-child(3) .custom-modal-dialog {
            top: 60px;
            right: 60px;
            z-index: 1053;
        }

        .custom-modal:nth-child(4) .custom-modal-dialog {
            top: 80px;
            right: 80px;
            z-index: 1054;
        }

        .custom-modal:nth-child(5) .custom-modal-dialog {
            top: 100px;
            right: 100px;
            z-index: 1055;
        }

        /* For more than 5 modals - dynamic calculation */
        .custom-modal:nth-child(n+6) .custom-modal-dialog {
            top: calc(100px + (var(--modal-offset) * 20px));
            right: calc(100px + (var(--modal-offset) * 20px));
            z-index: calc(1055 + var(--modal-offset));
        }

        /* Animations */
        @keyframes modalSlideIn {
            from {
                transform: translateX(30px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .custom-modal.show .custom-modal-dialog {
            animation: modalSlideIn 0.3s ease-out;
        }

        .custom-modal-backdrop.show {
            display: block;
            animation: modalFadeIn 0.15s linear;
        }

        /* Body scroll lock */
        body.custom-modal-open {
            overflow: hidden;
            padding-right: 15px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .custom-modal-dialog {
                min-width: 95vw;
                max-width: 95vw;
                margin: 0.5rem auto;
            }

            .custom-modal:nth-child(1) .custom-modal-dialog,
            .custom-modal:nth-child(2) .custom-modal-dialog,
            .custom-modal:nth-child(3) .custom-modal-dialog,
            .custom-modal:nth-child(4) .custom-modal-dialog,
            .custom-modal:nth-child(5) .custom-modal-dialog,
            .custom-modal:nth-child(n+6) .custom-modal-dialog {
                top: 10px;
                right: 10px;
                left: 10px;
                margin: 0 auto;
            }
        }

        /* Status color overlays */
        .custom-modal .card.card-bg-red {
            background: linear-gradient(135deg, rgba(245, 0, 20, 0.69), rgba(255, 255, 255, 0.85));
            border-color: rgba(220, 53, 70, 0.72);
        }

        .custom-modal .card.card-bg-green {
            background: linear-gradient(135deg, rgba(3, 255, 62, 0.424), rgba(255, 255, 255, 0.85));
            border-color: rgba(40, 167, 69, 0.3);
        }

        .custom-modal .card.card-bg-yellow {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.15), rgba(255, 255, 255, 0.85));
            border-color: rgba(255, 193, 7, 0.3);
        }

        .custom-modal .card.card-bg-blue {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.15), rgba(255, 255, 255, 0.85));
            border-color: rgba(0, 123, 255, 0.3);
        }

        .custom-modal .card.card-bg-pink {
            background: linear-gradient(135deg, rgba(232, 62, 140, 0.15), rgba(255, 255, 255, 0.85));
            border-color: rgba(232, 62, 141, 0.424);
        }

        .custom-modal .card.card-bg-gray {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.15), rgba(255, 255, 255, 0.85));
            border-color: rgba(108, 117, 125, 0.3);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .custom-modal.show .custom-modal-dialog {
            animation: slideInRight 0.3s ease-out;
        }

        /* Close All button */
        #close-all-modals {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1060;
        }

        .custom-modal-dialog {
            position: fixed !important;
            top: 20px;
            right: 20px;
            margin: 0 !important;
            transform: none !important;
            cursor: move;
        }

        .custom-modal-header {
            cursor: move;
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

        /*popup modal style*/

        .choose-file {
            background-color: #ff6b2c;
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            width: 100%;
            display: block;
            transition: background-color 0.3s;
        }

        .choose-file:hover {
            background-color: #e65c1e;
        }

        .modal-content {
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 600;
        }

        .form-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        option[value="Todo"] {
            background-color: #2196f3;
        }

        option[value="Not Started"] {
            background-color: #ffff00;
            color: #000;
        }

        option[value="Working"] {
            background-color: #ff00ff;
        }

        option[value="In Progress"] {
            background-color: #f1c40f;
            color: #000;
        }

        option[value="Monitor"] {
            background-color: #5c6bc0;
        }

        option[value="Done"] {
            background-color: #00ff00;
            color: #000;
        }

        option[value="Need Help"] {
            background-color: #e91e63;
        }

        option[value="Review"] {
            background-color: #ffffff;
            color: #000;
        }

        option[value="Need Approval"] {
            background-color: #d4ff00;
            color: #000;
        }

        option[value="Dependent"] {
            background-color: #ff9999;
        }

        option[value="Approved"] {
            background-color: #ffeb3b;
            color: #000;
        }

        option[value="Hold"] {
            background-color: #ffffff;
            color: #000;
        }

        option[value="Rework"] {
            background-color: #673ab7;
        }

        option[value="Urgent"] {
            background-color: #f44336;
        }

        option[value="Q-Task"] {
            background-color: #ff00ff;
        }

        /*only for scouth view*/
        /* Add this to your CSS */
        /* Scouth Products View Specific Styling */
        div.custom-modal-content h5.custom-modal-title:contains("Scouth products view Details")+.custom-modal-body {
            padding: 15px;
            overflow: auto;
        }

        .scouth-header {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .scouth-header-item {
            font-weight: bold;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .scouth-table-container {
            display: flex;
            flex-direction: column;
            gap: 0;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .scouth-table-header {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .scouth-table-row {
            display: flex;
            border-bottom: 1px solid #dee2e6;
            background: white;
        }

        .scouth-table-row:last-child {
            border-bottom: none;
        }

        .scouth-table-cell {
            padding: 10px 12px;
            min-width: 120px;
            flex: 1;
            border-right: 1px solid #dee2e6;
            word-break: break-word;
        }

        .scouth-table-cell:last-child {
            border-right: none;
        }

        .scouth-table-header .scouth-table-cell {
            font-weight: bold;
            color: #495057;
        }

        .scouth-table-row:hover {
            background-color: #f1f1f1;
        }

        .image-thumbnail {
            max-width: 100px;
            max-height: 100px;
            display: block;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .scouth-product-value a {
            color: #0d6efd;
            text-decoration: none;
        }

        .scouth-product-value a:hover {
            text-decoration: underline;
        }

        /* Highlight the selected dropdown option */
        .dropdown-item.active {
            background-color: #e9ecef;
            color: #495057;
            font-weight: bold;
        }

        /* Style for the filter selection text in buttons */
        .filter-selection {
            font-weight: bold;
            color: #0d6efd;
            margin-left: 4px;
        }

        /* Make dropdown buttons show their state */
        .btn-light.active-filter {
            background-color: #e2e6ea;
            border-color: #dae0e5;
        }
        .nr-hide{
            display: none !important;
        }
    </style>
@endsection

@section('content')
    @include('layouts.shared/page-title', [
        'page_title' => 'Listing Audit Wayfair',
        'sub_title' => 'Wayfair',
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex align-items-center" style="gap: 12px;">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <!-- Example for LISTED dropdown - apply same pattern to all dropdowns -->
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="listedFilterDropdown"
                                data-bs-toggle="dropdown" data-column="Listed">
                                LISTED: <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="liveFilterDropdown"
                                data-bs-toggle="dropdown" data-column="Live">
                                LIVE: <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="categoryFilterDropdown"
                                data-bs-toggle="dropdown" data-column="Category">
                                CATEGORY: <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="attrFilledFilterDropdown"
                                data-bs-toggle="dropdown" data-column="AttrFilled">
                                ATTR FILLED : <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="aPlusFilterDropdown"
                                data-bs-toggle="dropdown" data-column="APlus">
                                A+ : <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="videoFilterDropdown"
                                data-bs-toggle="dropdown" data-column="Video">
                                VIDEO : <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="titleFilterDropdown"
                                data-bs-toggle="dropdown" data-column="Title">
                                TITLE : <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="imagesFilterDropdown"
                                data-bs-toggle="dropdown" data-column="Images">
                                IMAGES : <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="descriptionFilterDropdown"
                                data-bs-toggle="dropdown" data-column="Description">
                                DESCRIPTION : <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="bulletPointsFilterDropdown"
                                data-bs-toggle="dropdown" data-column="BulletPoints">
                                BULLET POINTS : <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown manual-dropdown-container">
                            <button class="btn btn-light dropdown-toggle" type="button" id="inVariationFilterDropdown"
                                data-bs-toggle="dropdown" data-column="InVariation">
                                IN VARIATION : <span class="filter-selection">ALL</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listedFilterDropdown">
                                <li><a class="dropdown-item column-filter" data-value="ALL">ALL</a></li>
                                <li><a class="dropdown-item column-filter" data-value="DONE"
                                        style="background-color: #d4edda; color: #155724; font-weight: bold;">DONE</a></li>
                                <li><a class="dropdown-item column-filter" data-value="PENDING"
                                        style="background-color: #f8d7da; color: #721c24; font-weight: bold;">PENDING</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <!-- Controls row -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <!-- Left side controls -->
                        <div class="form-inline">
                            <div class="form-group mr-2">
                                <label for="row-data-type" class="mr-2">Data Type:</label>
                                <select id="row-data-type" class="form-control form-control-sm">
                                    <option value="all">All</option>
                                    <option value="sku">SKU (Child)</option>
                                    <option value="parent">Parent</option>
                                </select>
                            </div>
                            <div class="form-group ml-2">
                                <label for="inv-filter" class="mr-2">INV:</label>
                                <select id="inv-filter" class="form-control form-control-sm">
                                    <option value="all">All</option>
                                    <option value="0">0</option>
                                    <option value="1-100+">1-100+</option>
                                </select>
                            </div>
                        </div>

                        <!-- Search on right -->
                        <div class="form-inline">
                            <div class="form-group">
                                <label for="search-input" class="mr-2">Search:</label>
                                <input type="text" id="search-input" class="form-control form-control-sm"
                                    placeholder="Search all columns...">
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="custom-resizable-table" id="wayfair-table">
                            <thead>
                                <tr>
                                    <th data-field="sl_no">SL No. <span class="sort-arrow">↓</span></th>
                                    <th data-field="parent" style="vertical-align: middle; white-space: nowrap;">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="d-flex align-items-center sortable-header">
                                                Parent <span class="sort-arrow">↓</span>
                                            </div>
                                            <div class="mt-1 dropdown-search-container">
                                                <input type="text" class="form-control form-control-sm parent-search"
                                                    placeholder="Search parent..." id="parentSearch">
                                                <div class="dropdown-search-results" id="parentSearchResults"></div>
                                            </div>
                                        </div>
                                    </th>
                                    <th data-field="sku" style="vertical-align: middle; white-space: nowrap;">
                                        <div class="d-flex flex-column align-items-center sortable">
                                            <div class="d-flex align-items-center">
                                                Sku <span class="sort-arrow">↓</span>
                                            </div>
                                            <div class="mt-1 dropdown-search-container">
                                                <input type="text" class="form-control form-control-sm sku-search"
                                                    placeholder="Search SKU..." id="skuSearch">
                                                <div class="dropdown-search-results" id="skuSearchResults"></div>
                                            </div>
                                        </div>
                                    </th>
                                    <th data-field="inv" style="vertical-align: middle; white-space: nowrap;">
                                        <div class="d-flex flex-column align-items-center" style="gap: 4px">
                                            <div class="d-flex align-items-center">
                                                INV <span class="sort-arrow">↓</span>
                                            </div>
                                            <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                            <div class="metric-total" id="inv-total">0</div>
                                        </div>
                                    </th>
                                    <th data-field="ov_l30" style="vertical-align: middle; white-space: nowrap;">
                                        <div class="d-flex flex-column align-items-center" style="gap: 4px">
                                            <div class="d-flex align-items-center">
                                                OV L30 <span class="sort-arrow">↓</span>
                                            </div>
                                            <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                            <div class="metric-total" id="ovl30-total">0</div>
                                        </div>
                                    </th>
                                    <th>NRL</th>
                                    <th data-field="listed">
                                        LISTED
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="listed-total">0</div>
                                    </th>
                                    <th data-field="live">
                                        LIVE
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="live-total">0</div>
                                    </th>
                                    <th data-field="category">
                                        Category
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="category-total">0</div>
                                    </th>
                                    <th data-field="attr_filled">
                                        Attr Filled
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="attrfilled-total">0</div>
                                    </th>
                                    <th data-field="a_plus">
                                        A+
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="aplus-total">0</div>
                                    </th>
                                    <th data-field="video">
                                        Video
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="video-total">0</div>
                                    </th>
                                    <th data-field="title">
                                        Title
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="title-total">0</div>
                                    </th>
                                    <th data-field="images">
                                        IMAGES
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="images-total">0</div>
                                    </th>
                                    <th data-field="description">
                                        DESCRIPTION
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="description-total">0</div>
                                    </th>
                                    <th data-field="bullet_points">
                                        BULLET POINTS
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="bulletpoints-total">0</div>
                                    </th>
                                    <th data-field="in_variation">
                                        In Variation
                                        <div style="width: 100%; height: 5px; background-color: #9ec7f4;"></div>
                                        <div class="metric-total" id="invariation-total">0</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination controls -->
                    <div class="pagination-controls mt-2">
                        <div class="form-group">
                            <span id="visible-rows" class="badge badge-light" style="color: #dc3545;">Showing 1-25 of
                                150</span>
                        </div>
                        <button id="first-page" class="btn btn-sm btn-outline-secondary mr-1">First</button>
                        <button id="prev-page" class="btn btn-sm btn-outline-secondary mr-1">Previous</button>
                        <span id="page-info" class="mx-2">Page 1 of 6</span>
                        <button id="next-page" class="btn btn-sm btn-outline-secondary ml-1">Next</button>
                        <button id="last-page" class="btn btn-sm btn-outline-secondary ml-1">Last</button>
                    </div>

                    <div id="data-loader" class="card-loader-overlay" style="display: none;">
                        <div class="loader-content">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="loader-text">Loading Listing Audit data...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.body.style.zoom = "80%";
        $(document).ready(function() {
            // Cache system
            const wayfairListingAuditDataCache = {
                cache: {},

                set: function(id, data) {
                    this.cache[id] = JSON.parse(JSON.stringify(data));
                },

                get: function(id) {
                    return this.cache[id] ? JSON.parse(JSON.stringify(this.cache[id])) : null;
                },

                updateField: function(id, field, value) {
                    if (this.cache[id]) {
                        this.cache[id][field] = value;
                    }
                },

                clear: function() {
                    this.cache = {};
                }
            };

            // Clear cache on page load
            window.addEventListener('load', function() {
                wayfairListingAuditDataCache.clear();
            });

            // Current state
            let currentPage = 1;
            let rowsPerPage = Infinity;
            let currentSort = {
                field: null,
                direction: 1
            };
            let tableData = [];
            let filteredData = [];
            let isResizing = false;
            let isLoading = false;
            let isEditMode = false;
            let currentEditingElement = null;

            // --- Filter State ---
            let columnFilters = {
                Listed: 'ALL',
                Live: 'ALL',
                Category: 'ALL',
                AttrFilled: 'ALL',
                APlus: 'ALL',
                Video: 'ALL',
                Title: 'ALL',
                Images: 'ALL',
                Description: 'ALL',
                BulletPoints: 'ALL',
                InVariation: 'ALL'
            };

            // --- Dropdown Click Handler ---
            $('.manual-dropdown-container .column-filter').on('click', function() {
                const $dropdown = $(this).closest('.manual-dropdown-container').find('button');
                const column = $dropdown.attr('data-column');
                const value = $(this).text().trim();

                if (column) {
                    // Update the filter state
                    columnFilters[column] = value;

                    // Update the dropdown button text
                    $dropdown.find('.filter-selection').text(value);

                    // Apply the filters to the table
                    applyColumnFilters();
                }
            });

            // --- Filtering Logic ---
            function applyColumnFilters() {
                filteredData = tableData.filter(item => {
                    let pass = true;
                    for (const [col, filter] of Object.entries(columnFilters)) {
                        if (filter === 'ALL') continue;
                        if (filter === 'DONE' && !(item[col] === true || item[col] === 'true' || item[
                                col] === 1)) pass = false;
                        if (filter === 'PENDING' && (item[col] === true || item[col] === 'true' || item[
                                col] === 1)) pass = false;
                    }
                    return pass;
                });
                renderTable();
                calculateTotals();
            }

                    // Initialize everything
                    function initTable() {
                        loadData().then(() => {
                            renderTable();
                            initResizableColumns();
                            initSorting();
                            initPagination();
                            initSearch();
                            calculateTotals();
                            initEnhancedDropdowns();
                            initNRSelectChangeHandler();
                        });
                    }

            // Load data from server
            function loadData() {
                showLoader();
                return $.ajax({
                    url: '/listing_audit_wayfair/view-data',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        // If response is an object with a data property, use that
                        if (Array.isArray(response)) {
                            tableData = response;
                        } else if (Array.isArray(response.data)) {
                            tableData = response.data;
                        } else {
                            tableData = [];
                        }
                        filteredData = [...tableData];
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading data:', error);
                        showNotification('danger', 'Failed to load data. Please try again.');
                        tableData = [];
                        filteredData = [];
                    },
                    complete: function() {
                        hideLoader();
                    }
                });
            }

            // Render table with current data
            function renderTable() {
                const $tbody = $('#wayfair-table tbody');
                $tbody.empty();

                if (isLoading) {
                    $tbody.append('<tr><td colspan="5" class="text-center">Loading data...</td></tr>');
                    return;
                }

                // First filter out all rows with INV <= 0 (except parent rows)
                const filteredRows = filteredData.filter(item => {
                    return item.is_parent || (parseFloat(item.INV) > 0);
                });

                // Group data by parent
                const groupedData = {};
                filteredRows.forEach(item => {
                    if (!groupedData[item.Parent]) {
                        groupedData[item.Parent] = [];
                    }
                    groupedData[item.Parent].push(item);
                });

                // Sort parents alphabetically
                const sortedParents = Object.keys(groupedData).sort();

                let rowIndex = 1;

                // Iterate through each parent group
                sortedParents.forEach(parent => {
                    const items = groupedData[parent];

                    // Count children with INV > 0 for this parent
                    const validChildren = items.filter(item => !item.is_parent && parseFloat(item.INV) >
                        0);

                    // Only show this group if it has valid children
                    if (validChildren.length > 0) {
                        // First add all valid child rows
                        validChildren.forEach(item => {
                            const $row = createTableRow(item, rowIndex++);
                            $tbody.append($row);
                        });

                        // Then find and add the parent row at the end of the group
                        const parentItem = items.find(item => item.is_parent);
                        if (parentItem) {
                            const $parentRow = createTableRow(parentItem, rowIndex++);
                            $parentRow.addClass('parent-row');
                            $tbody.append($parentRow);
                        }
                    }
                });

                if ($tbody.children().length === 0) {
                    $tbody.append(
                        '<tr><td colspan="5" class="text-center">No matching records found</td></tr>');
                }

                updatePaginationInfo();
                $('#visible-rows').text(
                    `Showing all ${$tbody.children().length} rows`);
            }

            // Helper function to create a table row
            function createTableRow(item, index) {
                const $row = $('<tr>');
                if(item.NR === 'NR'){
                    $row.addClass('nr-hide');
                }
                $row.append($('<td>').text(index));
                $row.append($('<td>').text(item.Parent));

                if (item.is_parent) {
                    $row.append($('<td>').html(`<strong>${item.sku}</strong>`));

                                // Calculate totals for this parent
                                const totals = getParentTotals(item.Parent);
                                $row.append($('<td>').html(`<strong>${totals.invTotal.toLocaleString()}</strong>`));
                                $row.append($('<td>').html(`<strong>${totals.l30Total.toLocaleString()}</strong>`));
                            } else {
                                $row.append($('<td>').text(item.sku));
                                $row.append($('<td>').text(item.INV));
                                $row.append($('<td>').text(item.L30));
                            }

                            if (item.is_parent) {
                                $row.append($('<td>')); // Empty cell for parent
                            } else {
                                const currentNR = item.NR === 'REQ' || item.NR === 'NR' ? item.NR : 'REQ'; // default to REQ
                                const $select = $(`
                                    <select class="form-select form-select-sm nr-select" style="min-width: 100px;">
                                        <option value="NR" ${currentNR === 'NR' ? 'selected' : ''}>NR</option>
                                        <option value="REQ" ${currentNR === 'REQ' ? 'selected' : ''}>REQ</option>
                                    </select>
                                `);

                                // Set background color based on value
                                if (currentNR === 'NR') {
                                    $select.css('background-color', '#dc3545');
                                    $select.css('color', '#ffffff');
                                } else if (currentNR === 'REQ') {
                                    $select.css('background-color', '#28a745');
                                    $select.css('color', '#ffffff');
                                }
                                $select.data('sku', item.sku);
                                $row.append($('<td>').append($select));
                            }

                const fields = [{
                        key: 'Listed',
                        label: 'LISTED'
                    },
                    {
                        key: 'Live',
                        label: 'LIVE'
                    },
                    {
                        key: 'Category',
                        label: 'Category'
                    },
                    {
                        key: 'AttrFilled',
                        label: 'Attr Filled'
                    },
                    {
                        key: 'APlus',
                        label: 'A+'
                    },
                    {
                        key: 'Video',
                        label: 'Video'
                    },
                    {
                        key: 'Title',
                        label: 'Title'
                    },
                    {
                        key: 'Images',
                        label: 'IMAGES'
                    },
                    {
                        key: 'Description',
                        label: 'DESCRIPTION'
                    },
                    {
                        key: 'BulletPoints',
                        label: 'BULLET POINTS'
                    },
                    {
                        key: 'InVariation',
                        label: 'In Variation'
                    }
                ];

                fields.forEach(f => {
                    if (item.is_parent) {
                        $row.append($('<td>'));
                    } else {
                        let value = item[f.key] ?? '';
                        const $container = $(
                            '<div class="na-edit-container d-flex align-items-center"></div>');
                        if (item.INV > 0) {
                            const $checkbox = $('<input type="checkbox" class="na-checkbox" />')
                                .prop('checked', value === true || value === 'true' || value === 1)
                                .data('field', f.key)
                                .data('original-value', value);
                            // No edit icon, just the checkbox
                            $container.append($checkbox);
                        }
                        $row.append($('<td>').append($container));
                    }
                });

                return $row;
            }

            // Initialize tooltips
            function initTooltips() {
                $('[data-bs-toggle="tooltip"]').tooltip({
                    trigger: 'hover',
                    placement: 'top',
                    boundary: 'window',
                    container: 'body',
                    offset: [0, 5],
                    template: '<div class="tooltip" role="tooltip">' +
                        '<div class="tooltip-arrow"></div>' +
                        '<div class="tooltip-inner"></div></div>'
                });
            }

                        function initNRSelectChangeHandler() {
                                $(document).off('change', '.nr-select');
                                $(document).on('change', '.nr-select', function () {
                                    const $select = $(this);
                                    const newValue = $select.val();
                                    const sku = $select.data('sku');

                                    // Change background color based on selected value
                                    if (newValue === 'NR') {
                                        $select.css('background-color', '#dc3545').css('color', '#ffffff');
                                    } else {
                                        $select.css('background-color', '#28a745').css('color', '#ffffff');
                                    }

                                    // Send AJAX
                                    $.ajax({
                                        url: '/wayfair/save-nr',
                                        type: 'POST',
                                        data: {
                                            sku: sku,
                                            nr: newValue,
                                            _token: $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function (response) {
                                        showNotification('success', 'NR updated successfully!');

                                        // Update tableData and filteredData
                                        tableData.forEach(item => {
                                            if (item.sku === sku) {
                                                item.NR = newValue;
                                            }
                                        });
                                        filteredData.forEach(item => {
                                            if (item.sku === sku) {
                                                item.NR = newValue;
                                            }
                                        });
                                        calculateTotals();
                                        renderTable();
                                    },
                                    error: function (xhr) {
                                        showNotification('danger', 'Failed to update NR.');
                                    }
                                });
                            });
                        }
                        // Make columns resizable
                        function initResizableColumns() {
                            const $table = $('#wayfair-table');
                            const $headers = $table.find('th');
                            let startX, startWidth, columnIndex;

                $headers.each(function() {
                    $(this).append('<div class="resize-handle"></div>');
                });

                $table.on('mousedown', '.resize-handle', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    isResizing = true;
                    $(this).addClass('resizing');

                    const $th = $(this).parent();
                    columnIndex = $th.index();
                    startX = e.pageX;
                    startWidth = $th.outerWidth();

                    $('body').css('user-select', 'none');
                });

                $(document).on('mousemove', function(e) {
                    if (!isResizing) return;

                    const $resizer = $('.resize-handle.resizing');
                    if ($resizer.length) {
                        const $th = $resizer.parent();
                        const newWidth = startWidth + (e.pageX - startX);
                        $th.css('width', newWidth + 'px');
                        $th.css('min-width', newWidth + 'px');
                        $th.css('max-width', newWidth + 'px');
                    }
                });

                $(document).on('mouseup', function(e) {
                    if (!isResizing) return;

                    e.stopPropagation();
                    $('.resize-handle').removeClass('resizing');
                    $('body').css('user-select', '');
                    isResizing = false;
                });
            }

            // Initialize sorting functionality
            function initSorting() {
                $('th[data-field]').addClass('sortable').on('click', function(e) {
                    if (isResizing) {
                        e.stopPropagation();
                        return;
                    }

                    // Prevent sorting when clicking on search inputs
                    if ($(e.target).is('input') || $(e.target).closest('.position-relative').length) {
                        return;
                    }

                    const th = $(this).closest('th');
                    const thField = th.data('field');
                    const dataField = thField === 'parent' ? 'Parent' : thField;

                    // Toggle direction if clicking same column, otherwise reset to ascending
                    if (currentSort.field === dataField) {
                        currentSort.direction *= -1;
                    } else {
                        currentSort.field = dataField;
                        currentSort.direction = 1;
                    }

                    // Update UI arrows
                    $('.sort-arrow').html('↓');
                    $(this).find('.sort-arrow').html(currentSort.direction === 1 ? '↑' : '↓');

                    // Sort with fresh data
                    const freshData = [...tableData];
                    freshData.sort((a, b) => {
                        const valA = a[dataField] || '';
                        const valB = b[dataField] || '';

                        // Numeric comparison for numeric fields
                        if (dataField === 'sl_no' || dataField === 'INV' || dataField ===
                            'L30') {
                            return (parseFloat(valA) - parseFloat(valB)) * currentSort
                                .direction;
                        }

                        // String comparison for other fields
                        return String(valA).localeCompare(String(valB)) * currentSort.direction;
                    });

                    filteredData = freshData;
                    currentPage = 1;
                    renderTable();
                });
            }

            // Initialize pagination
            function initPagination() {
                // Remove rows-per-page related code

                // Keep these but modify to work with all rows
                $('#first-page').on('click', function() {
                    currentPage = 1;
                    renderTable();
                });

                // Similar modifications for other pagination buttons...
                // But since we're showing all rows, you might want to disable pagination completely
            }

            function updatePaginationInfo() {
                // Since we're showing all rows, you can either:
                // Option 1: Hide pagination completely
                $('.pagination-controls').hide();

                // Option 2: Show "Showing all rows" message
                $('#page-info').text('Showing all rows');
                $('#first-page, #prev-page, #next-page, #last-page').prop('disabled', true);
            }

            // Initialize search functionality
            function initSearch() {
                $('#search-input').on('keyup', function() {
                    const searchTerm = $(this).val().toLowerCase();

                    if (searchTerm) {
                        filteredData = tableData.filter(item => {
                            return Object.values(item).some(val => {
                                if (typeof val === 'boolean' || val === null)
                                    return false;
                                return val.toString().toLowerCase().includes(
                                    searchTerm);
                            });
                        });
                    } else {
                        filteredData = [...tableData];
                    }

                    currentPage = 1;
                    renderTable();
                    calculateTotals();
                });
            }

            // Calculate and display totals
            function calculateTotals() {
                try {
                    if (isLoading || filteredData.length === 0) {
                        resetMetricsToZero();
                        return;
                    }

                    const metrics = {
                        invTotal: 0,
                        ovL30Total: 0,
                        rowCount: 0,
                        listedTotal: 0,
                        liveTotal: 0,
                        categoryTotal: 0,
                        attrfilledTotal: 0,
                        aplusTotal: 0,
                        videoTotal: 0,
                        titleTotal: 0,
                        imagesTotal: 0,
                        descriptionTotal: 0,
                        bulletpointsTotal: 0,
                        invariationTotal: 0
                    };

                    filteredData.forEach(item => {
                        if(item.NR === 'NR'){
                            return;
                        }
                        if (!item.is_parent) {
                            metrics.invTotal += parseFloat(item.INV) || 0;
                            metrics.ovL30Total += parseFloat(item.L30) || 0;
                            metrics.rowCount++;

                            if (parseFloat(item.INV) > 0) {
                                // Count unchecked (false) for each column
                                if (!item.Listed) metrics.listedTotal++;
                                if (!item.Live) metrics.liveTotal++;
                                if (!item.Category) metrics.categoryTotal++;
                                if (!item.AttrFilled) metrics.attrfilledTotal++;
                                if (!item.APlus) metrics.aplusTotal++;
                                if (!item.Video) metrics.videoTotal++;
                                if (!item.Title) metrics.titleTotal++;
                                if (!item.Images) metrics.imagesTotal++;
                                if (!item.Description) metrics.descriptionTotal++;
                                if (!item.BulletPoints) metrics.bulletpointsTotal++;
                                if (!item.InVariation) metrics.invariationTotal++;
                            }
                        }
                    });

                    // Update metric displays
                    $('#inv-total').text(metrics.invTotal.toLocaleString());
                    $('#ovl30-total').text(metrics.ovL30Total.toLocaleString());
                    $('#listed-total').text(metrics.listedTotal);
                    $('#live-total').text(metrics.liveTotal);
                    $('#category-total').text(metrics.categoryTotal);
                    $('#attrfilled-total').text(metrics.attrfilledTotal);
                    $('#aplus-total').text(metrics.aplusTotal);
                    $('#video-total').text(metrics.videoTotal);
                    $('#title-total').text(metrics.titleTotal);
                    $('#images-total').text(metrics.imagesTotal);
                    $('#description-total').text(metrics.descriptionTotal);
                    $('#bulletpoints-total').text(metrics.bulletpointsTotal);
                    $('#invariation-total').text(metrics.invariationTotal);

                } catch (error) {
                    console.error('Error in calculateTotals:', error);
                    resetMetricsToZero();
                }
            }

            function resetMetricsToZero() {
                $('#inv-total').text('0');
                $('#ovl30-total').text('0');
            }

            // Initialize enhanced dropdowns
            function initEnhancedDropdowns() {
                // Define constants at the function level
                const minSearchLength = 1;

                // Parent dropdown
                const $parentSearch = $('#parentSearch');
                const $parentResults = $('#parentSearchResults');

                // SKU dropdown
                const $skuSearch = $('#skuSearch');
                const $skuResults = $('#skuSearchResults');

                // Initialize both dropdowns
                initEnhancedDropdown($parentSearch, $parentResults, 'Parent');
                initEnhancedDropdown($skuSearch, $skuResults, 'sku');

                // Close dropdowns when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.dropdown-search-container').length) {
                        $('.dropdown-search-results').hide();
                    }
                });

                // Function to update dropdown results
                function updateDropdownResults($results, field, searchTerm) {
                    if (!tableData.length) return;

                    $results.empty();

                    // Get unique values for the field
                    const uniqueValues = [...new Set(tableData.map(item => String(item[field] || '')))];

                    // Filter based on search term if provided
                    const filteredValues = searchTerm.length >= minSearchLength ?
                        uniqueValues.filter(value =>
                            value.toLowerCase().includes(searchTerm.toLowerCase())
                        ) :
                        uniqueValues;

                    if (filteredValues.length) {
                        filteredValues.sort().forEach(value => {
                            if (value) {
                                $results.append(
                                    `<div class="dropdown-search-item" tabindex="0" data-value="${value}">${value}</div>`
                                );
                            }
                        });
                    } else {
                        $results.append('<div class="dropdown-search-item no-results">No matches found</div>');
                    }

                    $results.show();
                }

                // Function to filter the table by column value
                function filterByColumn(column, value) {
                    if (value === '') {
                        filteredData = [...tableData];
                    } else {
                        filteredData = tableData.filter(item =>
                            String(item[column] || '').toLowerCase() === value.toLowerCase()
                        );
                    }

                    currentPage = 1;
                    renderTable();
                    calculateTotals();
                }

                // Initialize a single dropdown
                function initEnhancedDropdown($input, $results, field) {
                    let timeout;

                    // Show dropdown when input is focused
                    $input.on('focus', function(e) {
                        e.stopPropagation();
                        updateDropdownResults($results, field, $(this).val().trim().toLowerCase());
                    });

                    // Handle input events
                    $input.on('input', function() {
                        clearTimeout(timeout);
                        const searchTerm = $(this).val().trim().toLowerCase();

                        timeout = setTimeout(() => {
                            updateDropdownResults($results, field, searchTerm);
                        }, 300);
                    });

                    // Handle item selection
                    $results.on('click', '.dropdown-search-item:not(.no-results)', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const value = $(this).data('value');
                        $input.val(value);
                        filterByColumn(field, value);
                        $results.hide();
                    });

                    // Handle keyboard navigation
                    $input.on('keydown', function(e) {
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            const $firstItem = $results.find('.dropdown-search-item').first();
                        }
                    });

                    $results.on('keydown', '.dropdown-search-item', function(e) {
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            $(this).next('.dropdown-search-item').focus();
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            const $prev = $(this).prev('.dropdown-search-item');
                            if ($prev.length) {
                                $prev.focus();
                            } else {
                                $input.focus();
                            }
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            $(this).click();
                            $results.hide();
                        } else if (e.key === 'Escape') {
                            $results.hide();
                            $input.focus();
                        }
                    });
                }

                $('#row-data-type').on('change', function() {
                    const filterType = $(this).val();
                    applyRowTypeFilter(filterType);
                });
            }

            // Calculate INV and L30 totals for each parent
            function getParentTotals(parentName) {
                let invTotal = 0;
                let l30Total = 0;
                filteredData.forEach(item => {
                    if (
                        item.Parent === parentName &&
                        !item.is_parent // Only sum child rows
                    ) {
                        invTotal += parseFloat(item.INV) || 0;
                        l30Total += parseFloat(item.L30) || 0;
                    }
                });
                return {
                    invTotal,
                    l30Total
                };
            }

            function initEnhancedDropdown($input, $results, field) {
                let timeout;
                const minSearchLength = 1;

                // Show dropdown when input is clicked
                $input.on('click', function(e) {
                    e.stopPropagation();
                    updateDropdownResults($results, field, $(this).val().trim().toLowerCase());
                });

                // Handle input events
                $input.on('input', function() {
                    clearTimeout(timeout);
                    const searchTerm = $(this).val().trim().toLowerCase();

                    // If search is cleared, trigger filtering immediately
                    if (searchTerm === '') {
                        filterByColumn(field, '');
                        return;
                    }

                    timeout = setTimeout(() => {
                        updateDropdownResults($results, field, searchTerm);
                    }, 300);
                });

                // Handle item selection
                $results.on('click', '.dropdown-search-item', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const value = $(this).data('value');
                    $input.val(value);
                    filterByColumn(field, value);

                    // Close the dropdown after selection
                    $results.hide();

                    // If you want to clear the filter when clicking the same value again
                    if ($input.data('last-value') === value) {
                        $input.val('');
                        filterByColumn(field, '');
                    }
                    $input.data('last-value', value);
                });

                // Handle keyboard navigation
                $input.on('keydown', function(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const $firstItem = $results.find('.dropdown-search-item').first();
                        if ($firstItem.length) {
                            $firstItem.focus();
                        }
                    }
                });

                $results.on('keydown', '.dropdown-search-item', function(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        $(this).next('.dropdown-search-item').focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        const $prev = $(this).prev('.dropdown-search-item');
                        if ($prev.length) {
                            $prev.focus();
                        } else {
                            $input.focus();
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        $(this).click();
                        $results.hide();
                    } else if (e.key === 'Escape') {
                        $results.hide();
                        $input.focus();
                    }
                });
            }

            function updateDropdownResults($results, field, searchTerm) {
                if (!tableData.length) return;

                $results.empty();

                if (searchTerm.length < minSearchLength) {
                    // Show all unique values when search is empty
                    const uniqueValues = [...new Set(tableData.map(item => String(item[field] || '')))];
                    uniqueValues.sort().forEach(value => {
                        if (value) {
                            $results.append(
                                `<div class="dropdown-search-item" data-value="${value}">${value}</div>`
                            );
                        }
                    });
                } else {
                    // Filter results based on search term
                    const matches = tableData.filter(item =>
                        String(item[field] || '').toLowerCase().includes(searchTerm)
                    );

                    if (matches.length) {
                        const uniqueMatches = [...new Set(matches.map(item => String(item[field] || '')))];
                        uniqueMatches.sort().forEach(value => {
                            if (value) {
                                $results.append(
                                    `<div class="dropdown-search-item" data-value="${value}">${value}</div>`
                                );
                            }
                        });
                    } else {
                        $results.append('<div class="dropdown-search-item no-results">No matches found</div>');
                    }
                }

                $results.show();
            }

            function applyRowTypeFilter(filterType) {
                // Reset to all data first
                filteredData = [...tableData];

                // Apply the row type filter
                if (filterType === 'parent') {
                    filteredData = filteredData.filter(item => item.is_parent);
                } else if (filterType === 'sku') {
                    filteredData = filteredData.filter(item => !item.is_parent);
                }
                // else 'all' - no filtering needed

                // Reset to first page and render
                currentPage = 1;
                renderTable();
                calculateTotals();
            }

            // Show notification
            function showNotification(type, message) {
                const notification = $(`
                    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                `);

                $('body').append(notification);

                setTimeout(() => {
                    notification.find('.alert').alert('close');
                }, 3000);
            }

            // Loader functions
            function showLoader() {
                $('#data-loader').fadeIn();
            }

            function hideLoader() {
                $('#data-loader').fadeOut();
            }

            // Initialize everything
            initTable();

            // Handle INV filter change
            $('#inv-filter').on('change', function() {
                const selectedValue = $(this).val();

                if (selectedValue === 'all') {
                    filteredData = [...tableData];
                } else if (selectedValue === '0') {
                    filteredData = tableData.filter(item => parseFloat(item.INV) === 0);
                } else if (selectedValue === '1-100+') {
                    filteredData = tableData.filter(item => parseFloat(item.INV) > 0);
                }

                currentPage = 1; // Reset to the first page
                renderTable(); // Re-render the table
                calculateTotals(); // Recalculate totals
            });

            function initNAEditHandlers() {
                // Remove old handlers
                $(document).off('change', '.na-checkbox');
                
                // Save on checkbox change
                $(document).on('change', '.na-checkbox', function(e) {
                    const $checkbox = $(this);
                    const $row = $checkbox.closest('tr');
                    const sku = $row.find('td:eq(2)').text().trim(); // Assuming SKU is in 3rd column
                    const field = $checkbox.data('field');
                    const updatedValue = $checkbox.is(':checked') ? "true" : "false";

                    const $icon = $checkbox.siblings('.na-edit-icon'); // 🟢 Added Line

                    // --- Save to database via AJAX ---
                    $.ajax({
                        url: '/listing_audit_wayfair/save-na',
                        type: 'POST',
                        data: {
                            sku: sku,
                            field: field,
                            value: updatedValue,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showNotification('success', field + ' updated successfully!');
                            $checkbox.prop('disabled', true);

                            $icon.removeClass('fa-spinner fa-spin text-primary')
                                .addClass('fa-pen text-primary')
                                .attr('title', 'Edit');

                            // Update in filteredData and tableData
                            [filteredData, tableData].forEach(dataset => {
                                dataset.forEach(item => {
                                    if (item.sku === sku) {
                                        item[field] = $checkbox.is(':checked');
                                    }
                                });
                            });

                            calculateTotals();
                        },
                        error: function(xhr) {
                            showNotification('danger', 'Failed to update ' + field + '.');
                            $checkbox.prop('checked', $checkbox.data('original-value'))
                                    .prop('disabled', true);

                            $icon.removeClass('fa-spinner fa-spin text-primary')
                                .addClass('fa-pen text-primary')
                                .attr('title', 'Edit');
                        }
                    });
                });

                // Allow clicking the checkbox directly to enter edit mode
                $(document).off('click', '.na-checkbox:disabled');
                $(document).on('click', '.na-checkbox:disabled', function(e) {
                    e.stopPropagation();
                    $(this).siblings('.na-edit-icon').trigger('click');
                });
            }

            initNAEditHandlers();
        });
    </script>
@endsection
