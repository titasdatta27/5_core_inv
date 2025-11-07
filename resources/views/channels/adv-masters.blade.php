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
                            <td>{{ $amazon_l30_sales }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $amazon_spent }}</td>
                            <td>{{ $amazon_clicks }}</td>
                            <td>{{ $amazon_ad_sales }}</td>
                            <td>
                                @php
                                    if($amazon_ad_sales > 0){
                                        $acos = ($amazon_spent/$amazon_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                                @php
                                    if($amazon_l30_sales > 0){
                                        $tacos = ($amazon_spent/$amazon_l30_sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                @endphp
                                {{ '('.$tacos.') %'  }}
                            </td>
                            <td>{{ $amazon_ad_sold }}</td>
                            <td>
                                @php
                                    if($amazon_clicks > 0){
                                        $cvr = ($amazon_ad_sold/$amazon_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $amazon_missing_ads }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td><a href="{{ route('amazon.kw.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ KW</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $amazonkw_spent }}</td>
                            <td>{{ $amazonkw_clicks }}</td>
                            <td>{{ $amazonkw_ad_sales }}</td>
                            <td>
                                @php
                                    if($amazonkw_ad_sales > 0){
                                        $acos = ($amazonkw_spent/$amazonkw_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $amazonkw_ad_sold }}</td>
                            <td>
                                @php
                                    if($amazonkw_clicks > 0){
                                        $cvr = ($amazonkw_ad_sold/$amazonkw_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $amazonkw_missing_ads }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td><a href="{{ route('amazon.pt.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ PT</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $amazonpt_spent }}</td>
                            <td>{{ $amazonpt_clicks }}</td>
                            <td>{{ $amazonpt_ad_sales }}</td>
                            <td>
                                @php
                                    if($amazonpt_ad_sales > 0){
                                        $acos = ($amazonpt_spent/$amazonpt_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $amazonpt_ad_sold }}</td>
                            <td>
                                @php
                                    if($amazonpt_clicks > 0){
                                        $cvr = ($amazonpt_ad_sold/$amazonpt_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $amazonpt_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td><a href="{{ route('amazon.hl.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">AMZ HL</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $amazonhl_spent }}</td>
                            <td>{{ $amazonhl_clicks }}</td>
                            <td>{{ $amazonhl_ad_sales }}</td>
                            <td>
                                @php
                                    if($amazonhl_ad_sales > 0){
                                        $acos = ($amazonhl_spent/$amazonhl_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $amazonhl_ad_sold }}</td>
                            <td>
                                @php
                                    if($amazonhl_clicks > 0){
                                        $cvr = ($amazonhl_ad_sold/$amazonhl_clicks)*100;
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
                            <td>{{ $ebay_l30_sales }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay_spent }}</td>
                            <td>{{ $ebay_clicks }}</td>
                            <td>{{ $ebay_ad_sales }}</td>
                            <td>
                                @php
                                    if($ebay_ad_sales > 0){
                                        $acos = ($ebay_spent/$ebay_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                                @php
                                    if($ebay_l30_sales > 0){
                                        $tacos = ($ebay_spent/$ebay_l30_sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                @endphp
                                {{ '('.$tacos.') %'  }}
                            </td>
                            <td>{{ $ebay_ad_sold }}</td>
                            <td>
                                @php
                                    if($ebay_clicks > 0){
                                        $cvr = ($ebay_ad_sold/$ebay_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td><a href="{{ route('ebay.keywords.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">EB KW</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebaykw_spent }}</td>
                            <td>{{ $ebaykw_clicks }}</td>
                            <td>{{ $ebaykw_ad_sales }}</td>
                            <td>
                                @php
                                    if($ebaykw_ad_sales > 0){
                                        $acos = ($ebaykw_spent/$ebaykw_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebaykw_ad_sold }}</td>
                            <td>
                                @php
                                    if($ebaykw_clicks > 0){
                                        $cvr = ($ebaykw_ad_sold/$ebaykw_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebaykw_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td><a href="{{ route('ebay.pmp.ads') }}" target="_blank" style="text-decoration:none; color:#000000;">EB PMT</a></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebaypmt_spent }}</td>
                            <td>{{ $ebaypmt_clicks }}</td>
                            <td>{{ $ebaypmt_ad_sales }}</td>
                            <td>
                                @php
                                    if($ebaypmt_ad_sales > 0){
                                        $acos = ($ebaypmt_spent/$ebaypmt_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebaypmt_ad_sold }}</td>
                            <td>
                                @php
                                    if($ebaypmt_clicks > 0){
                                        $cvr = ($ebaypmt_ad_sold/$ebaypmt_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebaypmt_missing_ads }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>EBAY 2</b></td>
                            <td>{{ $ebay2_l30_sales }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay2_spent }}</td>
                            <td>{{ $ebay2_clicks }}</td>
                            <td>{{ $ebay2_ad_sales }}</td>
                            <td>
                                @php
                                    if($ebay2_ad_sales > 0){
                                        $acos = ($ebay2_spent/$ebay2_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                                @php
                                    if($ebay2_l30_sales > 0){
                                        $tacos = ($ebay2_spent/$ebay2_l30_sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                @endphp
                                {{ '('.$tacos.') %'  }}
                            </td>
                            <td>{{ $ebay2_ad_sold }}</td>
                            <td>
                                @php
                                    if($ebay2_clicks > 0){
                                        $cvr = ($ebay2_ad_sold/$ebay2_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay2_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td>EB PMT</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay2pmt_spent }}</td>
                            <td>{{ $ebay2pmt_clicks }}</td>
                            <td>{{ $ebay2pmt_ad_sales }}</td>
                            <td>
                                @php
                                    if($ebay2pmt_ad_sales > 0){
                                        $acos = ($ebay2pmt_spent/$ebay2pmt_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebay2pmt_ad_sold }}</td>
                            <td>
                                @php
                                    if($ebay2pmt_clicks > 0){
                                        $cvr = ($ebay2pmt_ad_sold/$ebay2pmt_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay2pmt_missing_ads }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>EBAY 3</b></td>
                            <td>{{ $ebay3_l30_sales }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay3_spent }}</td>
                            <td>{{ $ebay3_clicks }}</td>
                            <td>{{ $ebay3_ad_sales }}</td>
                            <td>
                                @php
                                    if($ebay3_ad_sales > 0){
                                        $acos = ($ebay3_spent/$ebay3_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                                @php
                                    if($ebay3_l30_sales > 0){
                                        $tacos = ($ebay3_spent/$ebay3_l30_sales)*100;
                                        $tacos = number_format($tacos, 2);
                                    }else{
                                        $tacos = 0;
                                    }
                                @endphp
                                {{ '('.$tacos.') %'  }}
                            </td>
                            <td>{{ $ebay3_ad_sold }}</td>
                            <td>
                                @php
                                    if($ebay3_clicks > 0){
                                        $cvr = ($ebay3_ad_sold/$ebay3_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay3_missing_ads }}</td>
                        </tr>

                         <tr class="accordion-body">
                            <td>EB KW</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay3kw_spent }}</td>
                            <td>{{ $ebay3kw_clicks }}</td>
                            <td>{{ $ebay3kw_ad_sales }}</td>
                            <td>
                                @php
                                    if($ebay3kw_ad_sales > 0){
                                        $acos = ($ebay3kw_spent/$ebay3kw_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebay3kw_ad_sold }}</td>
                            <td>
                                @php
                                    if($ebay3kw_clicks > 0){
                                        $cvr = ($ebay3kw_ad_sold/$ebay3kw_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay3kw_missing_ads }}</td>
                        </tr>

                        <tr class="accordion-body">
                            <td>EB PMT</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $ebay3pmt_spent }}</td>
                            <td>{{ $ebay3pmt_clicks }}</td>
                            <td>{{ $ebay3pmt_ad_sales }}</td>
                            <td>
                                @php
                                    if($ebay3pmt_ad_sales > 0){
                                        $acos = ($ebay3pmt_spent/$ebay3pmt_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $ebay3pmt_ad_sold }}</td>
                            <td>
                                @php
                                    if($ebay3pmt_clicks > 0){
                                        $cvr = ($ebay3pmt_ad_sold/$ebay3pmt_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
                            <td>{{ $ebay3pmt_missing_ads }}</td>
                        </tr>

                        <tr style="background-color:#cfe2f3;" class="accordion-header">
                            <td><b>WALMART</b></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $walmart_spent }}</td>
                            <td>{{ $walmart_clicks }}</td>
                            <td>{{ $walmart_ad_sales }}</td>
                            <td>
                                @php
                                    if($walmart_ad_sales > 0){
                                        $acos = ($walmart_spent/$walmart_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td>
                              
                            </td>
                            <td>{{ $walmart_ad_sold }}</td>
                            <td>
                                @php
                                    if($walmart_clicks > 0){
                                        $cvr = ($walmart_ad_sold/$walmart_clicks)*100;
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
                            <td>{{ $gshoping_spent }}</td>
                            <td>{{ $gshoping_clicks }}</td>
                            <td>{{ $gshoping_ad_sales }}</td>
                            <td>
                                @php
                                    if($gshoping_ad_sales > 0){
                                        $acos = ($gshoping_spent/$gshoping_ad_sales)*100;
                                        $acos = number_format($acos, 2);
                                    }else{
                                        $acos = 0;
                                    }
                                @endphp
                                {{ '('.$acos.') %'  }}
                            </td>
                            <td></td>
                            <td>{{ $gshoping_ad_sold }}</td>
                            <td>
                                @php
                                    if($gshoping_clicks > 0){
                                        $cvr = ($gshoping_ad_sold/$gshoping_clicks)*100;
                                        $cvr = number_format($cvr, 2);
                                    }else{
                                        $cvr = 0;
                                    }
                                @endphp
                                {{ '('.$cvr.') %' }}
                            </td>
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
