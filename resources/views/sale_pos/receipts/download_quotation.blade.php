<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="charset=utf-8" />
    <style>
        @page {
            margin: 14mm 10mm 18mm 10mm;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        table.product_table {
            width: 100%;
            table-layout: fixed;
        }

        .sl-col {
            width: 4%;
            text-align: center;
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <!-- business information here -->
    <!--Businees and Customer Information -->
    <div class="row" style="color: #000000 !important;">
        <!-- business information here -->
        <div class="col-xs-12 text-center">
            <table class="table text-center " style="width:100%;">
                <tbody>
                    <tr style="font-size: 14px !important;">
                        <td width="20%" valign="top">
                            <img style="max-height: 120px; width: 120px;"
                                src="{{'data:image/png;base64,' . base64_encode(file_get_contents(base_path('public/uploads/invoice_logos/TBL-Logo-PNG.png')))}}"
                                class="img img-responsive center-block">
                        </td>
                        <td width="60%">
                            <h2 class="text-center m-0">
                                {{$receipt_details->display_name}}
                            </h2>

                            <h5 class="text-center" style="margin: 5px;">
                                <p class="text-center m-0" style="line-height: 20px;">
                                    {!! $receipt_details->address !!}<br>
                                    @if(!empty($receipt_details->phone_contact))
                                        {!! $receipt_details->phone_contact !!}<br>
                                    @endif
                                    @if(!empty($receipt_details->email))Email: {{ $receipt_details->email }}@endif
                                    @if(!empty($receipt_details->email) && !empty($receipt_details->website)), @endif
                                    @if(!empty($receipt_details->website))Website: {{ $receipt_details->website }}@endif<br>
                                </p>
                            </h5>
                        </td>
                        <td width="20%"></td>
                    </tr>
                </tbody>
            </table>
            <br>
            <!-- Title of receipt -->

        </div>


        <!-- Customer Information -->
        <table style="width: 100% !important">
            <tbody>
                <tr>
                    <td width="60%"><b>Quotation No:</b> {{$receipt_details->invoice_no}}</td>
                    <td width="40%"><b>{{$receipt_details->date_label}}:</b> {{$receipt_details->current_datetime}}</td>
                </tr>

            </tbody>
        </table>
        <hr class="m-0">
        <table style="width: 100% !important">
            <tbody>
                <tr>
                    <td width="60%"><b>{{ $receipt_details->customer_label }},</b></td>
                    <td width="40%"></td>
                </tr>
                <tr>
                    <td width="60%">{!! $receipt_details->business_name !!}</td>
                    <td width="40%"> <b>{{ $receipt_details->sell_custom_field_2_label }}:</b>
                        {!!$receipt_details->sell_custom_field_2_value ?? ''!!}</td>
                </tr>
                <tr>
                    <td width="60%">{{ $receipt_details->address_line_1 }}</td>
                    <td width="40%"><b>Mobile: </b> {!! $receipt_details->customer_mobile !!}</td>
                </tr>

            </tbody>
        </table>
        <br>
        <table style="width: 100% !important">
            <tbody>
                <tr>
                    <td width="60%"><b>{{ $receipt_details->sales_person_label }}:</b>
                        {{ $receipt_details->sales_person }}</td>
                    <td width="40%"></td>
                </tr>

            </tbody>
        </table>

    </div>

    <!--End Businees and Customer Information -->

    <br>

    <!--Product Table Information -->
    <div class="row color-555">
        <div class="col-xs-12">

            <table class="table product_table" width="100%" style="font-size: 14px;">
                <thead>
                    <tr class="item_table_header">
                        <th class="sl-col">SL.</th>
                        <th width="13%">Part/Model Number</th>
                        <th width="42%">{{$receipt_details->table_product_label}}</th>
                        <th width="11%">Delivery Time</th>
                        <th style="text-align: center;" width="6%">Unit</th>
                        <th class="text-right" width="5%">{{$receipt_details->table_qty_label}}</th>
                        <th style="text-align: right;" width="9%">{{$receipt_details->table_unit_price_label}}</th>
                        <th style="text-align: right;" width="10%">{{$receipt_details->table_subtotal_label}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipt_details->lines as $line)
                        <tr>
                            <td class="sl-col">{{ $loop->iteration }}</td>
                            <td>{{$line['sub_sku']}}</td>
                            <td class="description-cell">
                                <div>{{$line['name']}}</div>
                                @if(!empty($line['brand'])) <div>{{'Brand: ' . $line['brand']}}</div>@endif
                                @if(!empty($line['origin'])) <div>{{'Origin: ' . $line['origin']}}</div>@endif

                                @php($formatted_description = sanitizeEditorHtmlForPdf($line['product_description'] ?? ''))
                                @if($formatted_description !== '')
                                    <div class="product-description">{!! $formatted_description !!}</div>
                                @endif
                            </td>
                            <td style="text-align: center;">{{$line['delivery_time']}}</td>
                            <td style="text-align: center;">{{$line['units']}}</td>
                            <td style="text-align: center;">{{$line['quantity']}}</td>
                            <td style="text-align: right;">{{$line['unit_price_inc_tax']}}</td>
                            <td style="text-align: right;">{{$line['line_total']}}</td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <!--End Product Table Information -->
    <hr>
    <table style="width: 100% !important">
        <tbody>
            <tr>
                <td width="60%" style="text-transform: capitalize;" valign="top">
                    <b>Amount In Words : {{$receipt_details->total_in_words}} Taka Only</b>
                </td>
                <td width="40%">
                    <table style="width: 100% !important">
                        <tbody>
                            <tr>
                                <th style="width:40%" style="text-align: left;">
                                    {!! $receipt_details->subtotal_label !!}
                                </th>
                                <td style="text-align: right;">
                                    {{$receipt_details->subtotal}}
                                </td>
                            </tr>

                            <!-- Discount -->
                            @if(!empty($receipt_details->discount))
                                <tr>
                                    <th style="text-align: left;">
                                        {!! $receipt_details->discount_label !!}
                                    </th>

                                    <td style="text-align: right;">
                                        (-) {{$receipt_details->discount}}
                                    </td>
                                </tr>
                            @endif

                            <!-- Tax -->
                            @if(!empty($receipt_details->tax))
                                <tr>
                                    <th style="text-align: left;">{!! $receipt_details->tax_label !!}</th>
                                    <td style="text-align: right;">
                                        (+) {{$receipt_details->tax}}
                                    </td>
                                </tr>
                            @endif

                            <!-- Total -->
                            <tr>
                                <th style="text-align: left;">
                                    {!! $receipt_details->total_label !!}
                                </th>
                                <td style="text-align: right;">
                                    {{$receipt_details->total}}

                                </td>
                            </tr>
                        </tbody>
                    </table>

                </td>
            </tr>

        </tbody>
    </table>
    <p class="m-0"><b>Terms and Conditions:</b></p>
    <table class="table table-slim">
        @foreach($tc_description as $tc)
            <tr>
                <td>{{ $loop->iteration . '. ' }} </td>
                <td>{{ $tc->description }}</td>
            </tr>
        @endforeach
    </table>
    <br>
    <table width="100%">
        <tbody>
            <tr>
                <td width="40%"><strong>Best Regards,</strong></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td> </td>
            </tr>
            <tr>
                <td>{{ Auth::user()->first_name . ' ' . Auth::user()->last_name}}</td>
                <td></td>
            </tr>
            <tr>
                <td>{{ Auth::user()->contact_number }}</td>
                <td></td>
            </tr>
            <tr>
                <td>{{ Auth::user()->custom_field_1 }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>



    <footer class="text-center" style="font-size:10px;">
        System generated report, hence no signature required.
        Printed on <?php echo date("jS F Y", strtotime(date("Y-m-d"))) . " " . date("h:i A"); ?>
    </footer>
    <style>
        table,
        th,
        td {
            /* border: 1px solid black; */
            border-collapse: collapse;
        }

        .product_table,
        .product_table th,
        .product_table td {
            border: 1px solid black;
            font-size: 13px !important;
            vertical-align: middle;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .item_table_header {
            background-color: #868a91 !important;
            color: white !important;
        }

        table,
        p,
        th,
        td {
            font-size: 13px;
        }

        .product_table {
            table-layout: fixed;
            width: 100%;
            page-break-inside: auto;
        }

        .product_table thead {
            display: table-header-group;
        }

        .product_table tbody tr {
            page-break-inside: auto !important;
            page-break-after: auto;
        }

        .product_table td,
        .product_table th {
            page-break-inside: auto !important;
            page-break-before: auto;
            white-space: normal;
        }

        .description-cell {
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            vertical-align: top;
        }

        .product-description {
            margin-top: 4px;
            font-size: 12px;
            line-height: 1.15;
            white-space: normal;
        }

        .product-description p,
        .product-description div,
        .product-description blockquote,
        .product-description h1,
        .product-description h2,
        .product-description h3,
        .product-description h4,
        .product-description h5,
        .product-description h6 {
            margin: 0 0 3px;
            padding: 0;
        }

        .product-description h1,
        .product-description h2,
        .product-description h3,
        .product-description h4,
        .product-description h5,
        .product-description h6 {
            font-size: 12px;
            font-weight: bold;
        }

        .product-description ul,
        .product-description ol {
            margin: 2px 0 3px 15px;
            padding: 0;
        }

        .product-description li {
            margin: 0;
            padding: 0;
        }

        .product-description blockquote {
            border-left: 2px solid #999;
            padding-left: 5px;
        }

        .product-description table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
            margin: 3px 0;
        }

        .product-description table th,
        .product-description table td {
            border: 1px solid #777;
            padding: 1px 2px;
            font-size: 11px;
            line-height: 1.1;
            vertical-align: top;
            white-space: normal;
            word-break: break-word;
        }

        .product-description table th {
            background-color: #eeeeee;
            font-weight: bold;
        }

        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 12px;
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
