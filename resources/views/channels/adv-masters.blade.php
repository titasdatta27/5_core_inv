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
                            <th class="text-center">TOTAL</th>
                            <th class="text-center">L30 SALES <br><hr> {{ $total_l30_sales}}</th>
                            <th class="text-center">GPFT <br><hr> 0</th>
                            <th class="text-center">TPFT <br><hr> 0</th>
                            <th class="text-center">SPENT <br><hr> {{ $total_spent}}</th>
                            <th class="text-center">CLICKS <br><hr> {{ $total_clicks}}</th>
                            <th class="text-center">AD SALES <br><hr> {{ $total_ad_sales}}</th>
                            <th class="text-center">ACOS <br><hr> 0</th>
                            <th class="text-center">TACOS <br><hr> 0</th>     
                            <th class="text-center">AD SOLD <br><hr> {{ $total_ad_sold}}</th>        
                            <th class="text-center">CVR <br><hr> 0 </th>     
                            <th class="text-center">MISSING ADS <br><hr> {{ $total_missing}}</th>     
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td class="text-center"><b>AMAZON</b></td>
                            <td class="text-center">{{ $amazon_l30_sales }}</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $amazon_spent }}</td>
                            <td class="text-center">{{ $amazon_clicks }}</td>
                            <td class="text-center">{{ $amazon_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($amazon_ad_sales > 0){
                                        $acos = ($amazon_spent/$amazon_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center">
                                @php
                                    if($amazon_l30_sales > 0){
                                        $tacos = ($amazon_spent/$amazon_l30_sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                    $tacos = round($tacos);
                                @endphp
                                {{ $tacos.' %'  }}
                            </td>
                            <td class="text-center">{{ $amazon_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($amazon_clicks > 0){
                                        $cvr = ($amazon_ad_sold/$amazon_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $amazon_missing_ads }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td class="text-center"><a href="{{ route('amazon.kw.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ KW</a></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $amazonkw_spent }}</td>
                            <td class="text-center">{{ $amazonkw_clicks }}</td>
                            <td class="text-center">{{ $amazonkw_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($amazonkw_ad_sales > 0){
                                        $acos = ($amazonkw_spent/$amazonkw_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $amazonkw_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($amazonkw_clicks > 0){
                                        $cvr = ($amazonkw_ad_sold/$amazonkw_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $amazonkw_missing_ads }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td class="text-center"><a href="{{ route('amazon.pt.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ PT</a></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $amazonpt_spent }}</td>
                            <td class="text-center">{{ $amazonpt_clicks }}</td>
                            <td class="text-center">{{ $amazonpt_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($amazonpt_ad_sales > 0){
                                        $acos = ($amazonpt_spent/$amazonpt_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $amazonpt_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($amazonpt_clicks > 0){
                                        $cvr = ($amazonpt_ad_sold/$amazonpt_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $amazonpt_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center"><a href="{{ route('amazon.hl.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ HL</a></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $amazonhl_spent }}</td>
                            <td class="text-center">{{ $amazonhl_clicks }}</td>
                            <td class="text-center">{{ $amazonhl_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($amazonhl_ad_sales > 0){
                                        $acos = ($amazonhl_spent/$amazonhl_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $amazonhl_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($amazonhl_clicks > 0){
                                        $cvr = ($amazonhl_ad_sold/$amazonhl_clicks)*100;
                                        $cvr = number_format($cvr, 2); 
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center"></td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td class="text-center"><b>EBAY</b></td>
                            <td class="text-center">{{ $ebay_l30_sales }}</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay_spent }}</td>
                            <td class="text-center">{{ $ebay_clicks }}</td>
                            <td class="text-center">{{ $ebay_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay_ad_sales > 0){
                                        $acos = ($ebay_spent/$ebay_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center">
                                @php
                                    if($ebay_l30_sales > 0){
                                        $tacos = ($ebay_spent/$ebay_l30_sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                    $tacos = round($tacos);
                                @endphp
                                {{ $tacos.' %'  }}
                            </td>
                            <td class="text-center">{{ $ebay_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay_clicks > 0){
                                        $cvr = ($ebay_ad_sold/$ebay_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $ebay_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center"><a href="{{ route('ebay.keywords.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">EB KW</a></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebaykw_spent }}</td>
                            <td class="text-center">{{ $ebaykw_clicks }}</td>
                            <td class="text-center">{{ $ebaykw_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($ebaykw_ad_sales > 0){
                                        $acos = ($ebaykw_spent/$ebaykw_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebaykw_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($ebaykw_clicks > 0){
                                        $cvr = ($ebaykw_ad_sold/$ebaykw_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $ebaykw_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center"><a href="{{ route('ebay.pmp.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">EB PMT</a></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebaypmt_spent }}</td>
                            <td class="text-center">{{ $ebaypmt_clicks }}</td>
                            <td class="text-center">{{ $ebaypmt_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($ebaypmt_ad_sales > 0){
                                        $acos = ($ebaypmt_spent/$ebaypmt_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebaypmt_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($ebaypmt_clicks > 0){
                                        $cvr = ($ebaypmt_ad_sold/$ebaypmt_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $ebaypmt_missing_ads }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td class="text-center"><b>EBAY 2</b></td>
                            <td class="text-center">{{ $ebay2_l30_sales }}</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay2_spent }}</td>
                            <td class="text-center">{{ $ebay2_clicks }}</td>
                            <td class="text-center">{{ $ebay2_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay2_ad_sales > 0){
                                        $acos = ($ebay2_spent/$ebay2_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center">
                                @php
                                    if($ebay2_l30_sales > 0){
                                        $tacos = ($ebay2_spent/$ebay2_l30_sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                    $tacos = round($tacos);
                                @endphp
                                {{ $tacos.' %'  }}
                            </td>
                            <td class="text-center">{{ $ebay2_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay2_clicks > 0){
                                        $cvr = ($ebay2_ad_sold/$ebay2_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $ebay2_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center">EB PMT</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay2pmt_spent }}</td>
                            <td class="text-center">{{ $ebay2pmt_clicks }}</td>
                            <td class="text-center">{{ $ebay2pmt_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay2pmt_ad_sales > 0){
                                        $acos = ($ebay2pmt_spent/$ebay2pmt_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay2pmt_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay2pmt_clicks > 0){
                                        $cvr = ($ebay2pmt_ad_sold/$ebay2pmt_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $ebay2pmt_missing_ads }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td class="text-center"><b>EBAY 3</b></td>
                            <td class="text-center">{{ $ebay3_l30_sales }}</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay3_spent }}</td>
                            <td class="text-center">{{ $ebay3_clicks }}</td>
                            <td class="text-center">{{ $ebay3_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay3_ad_sales > 0){
                                        $acos = ($ebay3_spent/$ebay3_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center">
                                @php
                                    if($ebay3_l30_sales > 0){
                                        $tacos = ($ebay3_spent/$ebay3_l30_sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                    $tacos = round($tacos);
                                @endphp
                                {{ $tacos.' %'  }}
                            </td>
                            <td class="text-center">{{ $ebay3_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay3_clicks > 0){
                                        $cvr = ($ebay3_ad_sold/$ebay3_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $ebay3_missing_ads }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td class="text-center">EB KW</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay3kw_spent }}</td>
                            <td class="text-center">{{ $ebay3kw_clicks }}</td>
                            <td class="text-center">{{ $ebay3kw_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay3kw_ad_sales > 0){
                                        $acos = ($ebay3kw_spent/$ebay3kw_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay3kw_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay3kw_clicks > 0){
                                        $cvr = ($ebay3kw_ad_sold/$ebay3kw_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $ebay3kw_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center">EB PMT</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay3pmt_spent }}</td>
                            <td class="text-center">{{ $ebay3pmt_clicks }}</td>
                            <td class="text-center">{{ $ebay3pmt_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay3pmt_ad_sales > 0){
                                        $acos = ($ebay3pmt_spent/$ebay3pmt_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $ebay3pmt_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($ebay3pmt_clicks > 0){
                                        $cvr = ($ebay3pmt_ad_sold/$ebay3pmt_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center">{{ $ebay3pmt_missing_ads }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td class="text-center"><b>WALMART</b></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $walmart_spent }}</td>
                            <td class="text-center">{{ $walmart_clicks }}</td>
                            <td class="text-center">{{ $walmart_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($walmart_ad_sales > 0){
                                        $acos = ($walmart_spent/$walmart_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center">
                              
                            </td>
                            <td class="text-center">{{ $walmart_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($walmart_clicks > 0){
                                        $cvr = ($walmart_ad_sold/$walmart_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center"></td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td class="text-center"><b>SHOPIFY</b></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>

                         <tr class="accordion-body">
                            <td class="text-center">G SHOPPING</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $gshoping_spent }}</td>
                            <td class="text-center">{{ $gshoping_clicks }}</td>
                            <td class="text-center">{{ $gshoping_ad_sales }}</td>
                            <td class="text-center">
                                @php
                                    if($gshoping_ad_sales > 0){
                                        $acos = ($gshoping_spent/$gshoping_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                    $acos = round($acos);
                                @endphp
                                {{ $acos.' %'  }}
                            </td>
                            <td class="text-center"></td>
                            <td class="text-center">{{ $gshoping_ad_sold }}</td>
                            <td class="text-center">
                                @php
                                    if($gshoping_clicks > 0){
                                        $cvr = ($gshoping_ad_sold/$gshoping_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                    $cvr = round($cvr);
                                @endphp
                                {{ $cvr.' %' }}
                            </td>
                            <td class="text-center"></td>
                        </tr>

                         <tr class="accordion-body">
                            <td class="text-center">G SERP</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center">FB CARAOUSAL</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center">FB VIDEO</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center">INSTA CARAOUSAL</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center">INSTA VIDEO</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>

                        <tr class="accordion-body">
                            <td class="text-center">YOUTUBE</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td class="text-center"><b>TIKTOK</b></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
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
