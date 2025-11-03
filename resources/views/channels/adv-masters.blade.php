@extends('layouts.vertical', ['title' => 'ADV Masters'])

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        .stats-card {
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .stats-card h4 {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.5rem;
        }
        .stats-card .badge {
            font-size: 1.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .table-container {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        th {
            cursor: col-resize;
        }
        #adv-master-table {
            table-layout: fixed; /* prevents text from overflowing */
            width: 100%;
        }

        #adv-master-table th,
        #adv-master-table td {
            overflow: hidden;
            text-overflow: ellipsis; /* optional: shows "..." for long text */
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        
        {{-- <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="m-0">ADV Masters</h4>
            </div>
        </div> --}}

        <!-- Table Container -->
        <div class="table-container">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="search-input" placeholder="Search..." />
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4"></div>
            <div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-responsive display" id="adv-master-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>CHANNEL</th>
                            <th>L30 SALES</th>
                            <th>GPFT</th>
                            <th>TPFT</th>
                            <th>SPENT</th>
                            <th>CLICKS</th>
                            <th>AD SALES</th>
                            <th>ACOS</th>
                            <th>TACOS</th>     
                            <th>AD SOLD</th>        
                            <th>CVR</th>     
                            <th>MISSING ADS</th>     
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>AMAZON</b></td>
                            <td>{{ $totalSales }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $SPEND_L30_total }}</td>
                            <td>{{ $CLICKS_L30_total }}</td>
                            <td>{{ $SALES_L30_Total }}</td>
                            <td>
                                @php
                                    if($SALES_L30_Total > 0){
                                        $acos = ($SPEND_L30_total/$SALES_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                                @php
                                    if($totalSales > 0){
                                        $tacos = ($SPEND_L30_total/$totalSales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                @endphp
                                {{ '('.$tacos.') %'  }}
                            </td>
                            <td>{{ $SOLD_L30_Total }}</td>
                            <td>
                                @php
                                    if($CLICKS_L30_total > 0){
                                        $cvr = ($SOLD_L30_Total/$CLICKS_L30_total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $totalMissingAds }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td><a href="{{ route('amazon.kw.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ KW</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $kw_spend_L30_total }}</td>
                            <td>{{ $kw_clicks_L30_total }}</td>
                            <td>{{ $kw_sales_L30_Total }}</td>
                            <td>
                                @php
                                    if($kw_sales_L30_Total > 0){
                                        $acos = ($kw_spend_L30_total/$kw_sales_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $kw_sold_L30_Total }}</td>
                            <td>
                                @php
                                    if($kw_clicks_L30_total > 0){
                                        $cvr = ($kw_sold_L30_Total/$kw_clicks_L30_total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $kwMissing }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td><a href="{{ route('amazon.pt.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ PT</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $pt_spend_L30_total }}</td>
                            <td>{{ $pt_clicks_L30_total }}</td>
                            <td>{{ $pt_sales_L30_Total }}</td>
                            <td>
                                @php
                                    if($pt_sales_L30_Total > 0){
                                        $acos = ($pt_spend_L30_total/$pt_sales_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $pt_sold_L30_Total }}</td>
                            <td>
                                @php
                                    if($pt_clicks_L30_total > 0){
                                        $cvr = ($pt_sold_L30_Total/$pt_clicks_L30_total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ptMissing }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td><a href="{{ route('amazon.hl.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ HL</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $hl_spend_L30_total }}</td>
                            <td>{{ $hl_clicks_L30_total }}</td>
                            <td>{{ $hl_sales_L30_Total }}</td>
                            <td>
                                @php
                                    if($hl_sales_L30_Total > 0){
                                        $acos = ($hl_spend_L30_total/$hl_sales_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $hl_sold_L30_Total }}</td>
                            <td>
                                @php
                                    if($hl_clicks_L30_total > 0){
                                        $cvr = ($hl_sold_L30_Total/$hl_clicks_L30_total)*100;
                                        $cvr = number_format($cvr, 2); 
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td></td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>EBAY</b></td>
                            <td>{{ $totalEbaySales }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay_SPEND_L30_total }}</td>
                            <td>{{ $ebay_CLICKS_L30_total }}</td>
                            <td>{{ $ebay_SALES_L30_total }}</td>
                            <td>
                                @php
                                    if($ebay_SALES_L30_total > 0){
                                        $acos = ($ebay_SPEND_L30_total/$ebay_SALES_L30_total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                                @php
                                    if($totalEbaySales > 0){
                                        $tacos = ($ebay_SPEND_L30_total/$totalEbaySales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                @endphp
                                {{ '('.$tacos.') %'  }}
                            </td>
                            <td>{{ $ebay_SOLD_L30_total }}</td>
                            <td>
                                @php
                                    if($ebay_CLICKS_L30_total > 0){
                                        $cvr = ($ebay_SOLD_L30_total/$ebay_CLICKS_L30_total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebaytotalMissingAds }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td><a href="{{ route('ebay.keywords.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">EB KW</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay_kw_spend_L30_total }}</td>
                            <td>{{ $ebay_kw_clicks_L30_total }}</td>
                            <td>{{ $ebay_kw_sales_L30_total }}</td>
                            <td>
                                @php
                                    if($ebay_kw_sales_L30_total > 0){
                                        $acos = ($ebay_kw_spend_L30_total/$ebay_kw_sales_L30_total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebay_kw_sold_L30_total }}</td>
                            <td>
                                @php
                                    if($ebay_kw_clicks_L30_total > 0){
                                        $cvr = ($ebay_kw_sold_L30_total/$ebay_kw_clicks_L30_total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebaykwMissing }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td><a href="{{ route('ebay.pmp.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">EB PMT</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay_pmt_spend_L30_total }}</td>
                            <td>{{ $ebay_pmt_clicks_L30_total }}</td>
                            <td>{{ $ebay_pmt_sales_L30_total }}</td>
                            <td>
                                @php
                                    if($ebay_pmt_sales_L30_total > 0){
                                        $acos = ($ebay_pmt_spend_L30_total/$ebay_pmt_sales_L30_total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebay_pmt_sold_L30_total }}</td>
                            <td>
                                @php
                                    if($ebay_pmt_clicks_L30_total > 0){
                                        $cvr = ($ebay_pmt_sold_L30_total/$ebay_pmt_clicks_L30_total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebayptMissing }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>EBAY 2</b></td>
                            <td>{{ $totalEbay2Sales }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay2_SPEND_L30_Total }}</td>
                            <td>{{ $ebay2_CLICKS_L30_Total }}</td>
                            <td>{{ $ebay2_SALES_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay2_SALES_L30_Total > 0){
                                        $acos = ($ebay2_SPEND_L30_Total/$ebay2_SALES_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                                @php
                                    if($totalEbay2Sales > 0){
                                        $tacos = ($ebay2_SPEND_L30_Total/$totalEbay2Sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                @endphp
                                {{ '('.$tacos.') %'  }}
                            </td>
                            <td>{{ $ebay2_SOLD_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay2_CLICKS_L30_Total > 0){
                                        $cvr = ($ebay2_SOLD_L30_Total/$ebay2_CLICKS_L30_Total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay2_ptMissing }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td>EB PMT</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay2_pmt_spend_L30_Total }}</td>
                            <td>{{ $ebay2_pmt_clicks_L30_Total }}</td>
                            <td>{{ $ebay2_pmt_sales_L30_total }}</td>
                            <td>
                                @php
                                    if($ebay2_pmt_sales_L30_total > 0){
                                        $acos = ($ebay2_pmt_spend_L30_Total/$ebay2_pmt_sales_L30_total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebay2_pmt_sold_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay2_pmt_clicks_L30_Total > 0){
                                        $cvr = ($ebay2_pmt_sold_L30_Total/$ebay2_pmt_clicks_L30_Total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay2_ptMissing }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>EBAY 3</b></td>
                            <td>{{ $totalSalesEbay3 }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay3_SPEND_L30_Total }}</td>
                            <td>{{ $ebay3_CLICKS_L30_Total }}</td>
                            <td>{{ $ebay3_SALES_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay3_SALES_L30_Total > 0){
                                        $acos = ($ebay3_SPEND_L30_Total/$ebay3_SALES_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                                @php
                                    if($totalSalesEbay3 > 0){
                                        $tacos = ($ebay3_SPEND_L30_Total/$totalSalesEbay3)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                @endphp
                                {{ '('.$tacos.') %'  }}
                            </td>
                            <td>{{ $ebay3_SOLD_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay3_CLICKS_L30_Total > 0){
                                        $cvr = ($ebay3_SOLD_L30_Total/$ebay3_CLICKS_L30_Total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay3totalMissingAds }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td>EB KW</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay3_kw_spend_L30_total }}</td>
                            <td>{{ $ebay3_kw_clicks_L30_Total }}</td>
                            <td>{{ $ebay3_kw_sales_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay3_kw_sales_L30_Total > 0){
                                        $acos = ($ebay3_kw_spend_L30_total/$ebay3_kw_sales_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebay3_kw_sold_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay3_kw_clicks_L30_Total > 0){
                                        $cvr = ($ebay3_kw_sold_L30_Total/$ebay3_kw_clicks_L30_Total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay3kwMissing }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td>EB PMT</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay3_pmt_spend_L30_Total }}</td>
                            <td>{{ $ebay3_pmt_clicks_L30_Total }}</td>
                            <td>{{ $ebay3_pmt_sales_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay3_pmt_sales_L30_Total > 0){
                                        $acos = ($ebay3_pmt_spend_L30_Total/$ebay3_pmt_sales_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebay3_pmt_sold_L30_Total }}</td>
                            <td>
                                @php
                                    if($ebay3_pmt_clicks_L30_Total > 0){
                                        $cvr = ($ebay3_pmt_sold_L30_Total/$ebay3_pmt_clicks_L30_Total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay3ptMissing }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>WALMART</b></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $walmart_SPEND_L30_Total }}</td>
                            <td>{{ $walmart_CLICKS_L30_Total }}</td>
                            <td>{{ $walmart_SALES_L30_Total }}</td>
                            <td>
                                @php
                                    if($walmart_SALES_L30_Total > 0){
                                        $acos = ($walmart_SPEND_L30_Total/$walmart_SALES_L30_Total)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                              
                            </td>
                            <td>{{ $walmart_SOLD_L30_Total }}</td>
                            <td>
                                @php
                                    if($walmart_CLICKS_L30_Total > 0){
                                        $cvr = ($walmart_SOLD_L30_Total/$walmart_CLICKS_L30_Total)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td></td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>SHOPIFY</b></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                         <tr class="accordion-body">
                            <td>G SHOPPING</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                         <tr class="accordion-body">
                            <td>G SERP</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr class="accordion-body">
                            <td>FB CARAOUSAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr class="accordion-body">
                            <td>FB VIDEO</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr class="accordion-body">
                            <td>INSTA CARAOUSAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr class="accordion-body">
                            <td>INSTA VIDEO</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr class="accordion-body">
                            <td>YOUTUBE</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>TIKTOK</b></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  {{-- <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script> --}}
  {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/colresizable/1.6.0/colResizable-1.6.min.js"></script> --}}
<script>
$(document).ready(function() {

    $(".accordion-body").hide();
    $(".accordion-header").click(function() {
        $(this).nextUntil(".accordion-header").slideToggle(200);
    });
   
    setTimeout(function() {
        var dtScript = document.createElement('script');
        dtScript.src = "https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js";
        dtScript.onload = function() {
            var colScript = document.createElement('script');
            colScript.src = "https://cdnjs.cloudflare.com/ajax/libs/colresizable/1.6.0/colResizable-1.6.min.js";
            colScript.onload = function() {

                let table = $('#adv-master-table').DataTable({
                    paging: false,
                    info: false,
                    searching: true,
                    scrollX:false,
                    autoWidth: false,
                    ordering:false,
                });

                $('.dataTables_filter').hide();
                
                $('#adv-master-table').colResizable({
                    liveDrag: true,
                    resizeMode: 'fit', // or 'flex'
                    gripInnerHtml: "<div class='grip'></div>",
                    draggingClass: "dragging"
                });

                $('#search-input').on('keyup', function() {
                    table.search(this.value).draw();
                });

            };
            document.body.appendChild(colScript); 
        };
        document.body.appendChild(dtScript); 
    }, 200); 

   
});
</script>
   
@endsection
