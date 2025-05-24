<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="charset=utf-8" />
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="row" style="color: #000000 !important;">
        <!-- business information here -->
        <div class="col-xs-12 text-center">
            <table class="table text-center " style="width:100%;">
                <tbody>
                    <tr style="font-size: 12px !important;">
                        <td width="20%" valign="top">
                            <img style="max-height: 120px; width: 120px;" src="{{'data:image/png;base64,'.base64_encode(file_get_contents(base_path('public/uploads/invoice_logos/TBL-Logo-PNG.png')))}}" class="img img-responsive center-block">
                        </td>
                        <td width="60%">
                            <h2 class="text-center m-0">
                                {{$receipt_details->display_name}}
                            </h2>

                            <h5 class="text-center" style="margin: 5px;">
                                <p class="text-center m-0" style="line-height: 20px;">
                                    {!! $receipt_details->address !!}<br>
                                    {!! $receipt_details->contact !!}<br>
                                    Website: {{ $receipt_details->website }}<br>
                                </p>
                            </h5>
                        </td>
                        <td width="20%"></td>
                    </tr>
                </tbody>
            </table>
            <br>
            <!-- Title of receipt -->
            <h3 class="text-center m-1" style="text-decoration: underline;">Delivery Chalan</h3>
        </div>

        <!-- Customer Information -->
        <div class="col-xs-12 text-center m-0">
            <!-- Invoice  number, Date  -->

            <table class="m-0" style="width:100%;">
                <tbody>
                    <tr style="font-size: 14px !important;">
                        <td width="33%"><b>Chalan No: </b>
                            {{'CH-'.$receipt_details->invoice_no}}</br>
                        </td>
                        <td width="33%"><b>{{ $receipt_details->sell_custom_field_1_label }}:</b> {!!$receipt_details->sell_custom_field_1_value ?? ''!!}</td>
                        <td width="33%"><b>{{$receipt_details->date_label}}:</b> {{$receipt_details->current_datetime}}</td>
                    </tr>
                </tbody>
            </table>

            <hr>
            <table class="table table-responsive table-sm" width="100%">
                <tbody>
                    <tr style="font-size: 14px !important;">
                    <td width="50%" valign="top">@if(!empty($receipt_details->customer_info))
                            <b>Billing Address,</b> <br>
                            Name: @if(!empty($receipt_details->business_name))
                            {{ $receipt_details->business_name }}
                            @else
                            {{ $receipt_details->customer_name }}
                            @endif <br>
                            Address: {!! $receipt_details->address_line_1 !!} <br>
                            @endif
                        </td>
                        <td width="50%" class="text-left word-wrap"><strong>@lang('lang_v1.shipping_address'),</strong><br>
                            Delivery To: {!! $receipt_details->delivered_to !!}</br>
                            Mobile: {!! $receipt_details->customer_mobile !!} <br>
                            Address: {!! $receipt_details->shipping_address !!}</br>
                            Delivery Type: {!! $receipt_details->shipping_status !!}
                            @if(!empty($receipt_details->shipping_custom_field_1_label))
                            <br><strong>{!!$receipt_details->shipping_custom_field_1_label!!} :</strong> {!!$receipt_details->shipping_custom_field_1_value ?? ''!!}
                            @endif
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

    </div>

    <!--End Businees and Customer Information -->

    <br>

    <!--Product Table Information -->
    <div class="row color-555">
        <div class="col-xs-12">

            <table class="table product_table" width="100%" style="font-size: 14px;">
                <thead>
                    <tr class="item_table_header">
                        <td> SL. </td>

                        <td style="text-align: center;">
                            Code/SKU
                        </td>

                        <td style="text-align: center;">
                            {{$receipt_details->table_product_label}}
                        </td>

                        <td style="text-align: center;">
                            Unit
                        </td>
                        <td style="text-align: center;">
                            {{$receipt_details->table_qty_label}}
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipt_details->lines as $line)
                    <tr>
                        <td>
                            {{$loop->iteration}}
                        </td>
                        <td>{{$line['sub_sku']}}</td>
                        <td>
                            {{$line['name']}} <br>
                            @if(!empty($line['brand'])) {{'Brand: '.$line['brand']}} <br>@endif
                            @if(!empty($line['origin'])) {{'Origin: '.$line['origin']}}<br>@endif
                            <!-- @if(!empty($line['brand'])) {{'Brand: '.$line['brand']}} @endif -->
                            <!-- {{$line['product_variation']}} {{$line['variation']}} -->
                            <!-- @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif -->
                            <!-- @if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif -->
                            <small>
                                {!!$line['product_description']!!}
                            </small>
                        </td>
                        <td>{{$line['units']}}</td>
                        <td>
                            {{$line['quantity']}}
                        </td>
                    </tr>
                    @if(!empty($line['modifiers']))
                    @foreach($line['modifiers'] as $modifier)
                    <tr>
                        <td class="text-center">
                            &nbsp;
                        </td>
                        <td>
                            {{$modifier['name']}} {{$modifier['variation']}}
                            @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif
                            @if(!empty($modifier['sell_line_note']))({!!$modifier['sell_line_note']!!}) @endif
                        </td>
                        <td class="text-right">
                            {{$modifier['quantity']}} {{$modifier['units']}}
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    @endforeach

                    @php
                    $lines = count($receipt_details->lines);
                    @endphp

                    @for ($i = $lines; $i < 1; $i++) <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        </tr>
                        @endfor

                </tbody>
            </table>
        </div>
    </div>
    <!--End Product Table Information -->

    <div class="row" style="color: #000000 !important;">
        <div class="col-md-12">

        </div>

        <br>
        <br>
        <br>
        <div class="border-bottom col-md-12">
            <table class="table" width="100%">
                <tr style="font-size: 14px !important;">
                    <td width="60%">
                        <strong>
                            <p>{{ Auth::user()->first_name .' '. Auth::user()->last_name}}</p>
                        </strong>
                    </td>
                    <td width="40%">
                        <strong>Goods Received in Good Condition</strong>
                        <br>
                    </td>
                </tr>
                <tr style="font-size: 14px !important; ">
                    <td width="60%">
                        <strong style="text-decoration: overline;">Authorized Signature</strong></br>
                    </td>
                    <td width="40%">
                        <strong style="text-decoration: overline;">Receiver's Signature</strong></br>
                    </td>
                </tr>
                <tr style="font-size: 14px !important;">
                    <td width="60%">
                        Designation: {{ Auth::user()->custom_field_1 }}
                    </td>
                    <td width="40%">
                        Name:
                    </td>
                </tr>
                <tr style="font-size: 14px !important;">
                    <td width="60%">
                        Mobile: {{ Auth::user()->contact_number }}
                    </td>
                    <td width="40%">
                        Designation:
                    </td>
                </tr>
                <tr style="font-size: 14px !important;">
                    <td width="60%">

                    </td>
                    <td width="40%">
                        Mobile: </br>
                    </td>
                </tr>

            </table>
        </div>
    </div>


    <footer class="text-center" style="font-size:10px; position: fixed; bottom: 0cm; left: 0px; right: 0px; height: 5px;">
        System generated report, hence no signature required.
        Printed on <?php echo date("jS F Y", strtotime(date("Y-m-d"))) . " " . date("h:i A"); ?>
    </footer>


    <style>
        table,
        th,
        td {
            /* border: 1px solid black; */
            border-collapse: collapse;
            font-size: 12px;
        }

        .product_table,
        .product_table th,
        .product_table td {
            border: 1px solid black;
            font-size: 12px !important;
        }

        .item_table_header {
            background-color: #868a91 !important;
            color: white !important;
        }

        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 10px;
        }

        .m-0 {
            margin: 0 !important
        }

        .m-1 {
            margin: .25rem !important
        }

        .m-2 {
            margin: .5rem !important
        }

        .m-3 {
            margin: 1rem !important
        }

        .m-4 {
            margin: 1.5rem !important
        }

        .m-5 {
            margin: 3rem !important
        }

        .m-auto {
            margin: auto !important
        }

        .mx-0 {
            margin-right: 0 !important;
            margin-left: 0 !important
        }

        .mx-1 {
            margin-right: .25rem !important;
            margin-left: .25rem !important
        }

        .mx-2 {
            margin-right: .5rem !important;
            margin-left: .5rem !important
        }

        .mx-3 {
            margin-right: 1rem !important;
            margin-left: 1rem !important
        }

        .mx-4 {
            margin-right: 1.5rem !important;
            margin-left: 1.5rem !important
        }

        .mx-5 {
            margin-right: 3rem !important;
            margin-left: 3rem !important
        }

        .mx-auto {
            margin-right: auto !important;
            margin-left: auto !important
        }

        .my-0 {
            margin-top: 0 !important;
            margin-bottom: 0 !important
        }

        .my-1 {
            margin-top: .25rem !important;
            margin-bottom: .25rem !important
        }

        .my-2 {
            margin-top: .5rem !important;
            margin-bottom: .5rem !important
        }

        .my-3 {
            margin-top: 1rem !important;
            margin-bottom: 1rem !important
        }

        .my-4 {
            margin-top: 1.5rem !important;
            margin-bottom: 1.5rem !important
        }

        .my-5 {
            margin-top: 3rem !important;
            margin-bottom: 3rem !important
        }

        .my-auto {
            margin-top: auto !important;
            margin-bottom: auto !important
        }

        .mt-0 {
            margin-top: 0 !important
        }

        .mt-1 {
            margin-top: .25rem !important
        }

        .mt-2 {
            margin-top: .5rem !important
        }

        .mt-3 {
            margin-top: 1rem !important
        }

        .mt-4 {
            margin-top: 1.5rem !important
        }

        .mt-5 {
            margin-top: 3rem !important
        }

        .mt-auto {
            margin-top: auto !important
        }

        .me-0 {
            margin-right: 0 !important
        }

        .me-1 {
            margin-right: .25rem !important
        }

        .me-2 {
            margin-right: .5rem !important
        }

        .me-3 {
            margin-right: 1rem !important
        }

        .me-4 {
            margin-right: 1.5rem !important
        }

        .me-5 {
            margin-right: 3rem !important
        }

        .me-auto {
            margin-right: auto !important
        }

        .mb-0 {
            margin-bottom: 0 !important
        }

        .mb-1 {
            margin-bottom: .25rem !important
        }

        .mb-2 {
            margin-bottom: .5rem !important
        }

        .mb-3 {
            margin-bottom: 1rem !important
        }

        .mb-4 {
            margin-bottom: 1.5rem !important
        }

        .mb-5 {
            margin-bottom: 3rem !important
        }

        .mb-auto {
            margin-bottom: auto !important
        }

        .ms-0 {
            margin-left: 0 !important
        }

        .ms-1 {
            margin-left: .25rem !important
        }

        .ms-2 {
            margin-left: .5rem !important
        }

        .ms-3 {
            margin-left: 1rem !important
        }

        .ms-4 {
            margin-left: 1.5rem !important
        }

        .ms-5 {
            margin-left: 3rem !important
        }

        .ms-auto {
            margin-left: auto !important
        }

        .text-start {
            text-align: left !important
        }

        .text-end {
            text-align: right !important
        }

        .text-center {
            text-align: center !important
        }

        .text-decoration-none {
            text-decoration: none !important
        }

        .text-decoration-underline {
            text-decoration: underline !important
        }

        .text-decoration-line-through {
            text-decoration: line-through !important
        }

        .text-lowercase {
            text-transform: lowercase !important
        }

        .text-uppercase {
            text-transform: uppercase !important
        }

        .text-capitalize {
            text-transform: capitalize !important
        }

        .text-wrap {
            white-space: normal !important
        }

        .text-nowrap {
            white-space: nowrap !important
        }

        .text-break {
            word-wrap: break-word !important;
            word-break: break-word !important
        }

        .text-primary {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-primary-rgb), var(--bs-text-opacity)) !important
        }

        .text-secondary {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-secondary-rgb), var(--bs-text-opacity)) !important
        }

        .text-success {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-success-rgb), var(--bs-text-opacity)) !important
        }

        .text-info {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-info-rgb), var(--bs-text-opacity)) !important
        }

        .text-warning {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-warning-rgb), var(--bs-text-opacity)) !important
        }

        .text-danger {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-danger-rgb), var(--bs-text-opacity)) !important
        }

        .text-light {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-light-rgb), var(--bs-text-opacity)) !important
        }

        .text-dark {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-dark-rgb), var(--bs-text-opacity)) !important
        }

        .text-black {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-black-rgb), var(--bs-text-opacity)) !important
        }

        .text-white {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-white-rgb), var(--bs-text-opacity)) !important
        }
    </style>
</body>

</html>