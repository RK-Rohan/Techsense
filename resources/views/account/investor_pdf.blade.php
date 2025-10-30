<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Certificate of Investment</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color:#111; }
        .container { width: 100%; padding: 24px; }
        .title { text-align:center; font-size: 22px; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; }
        .subtitle { text-align:center; font-size: 12px; color:#555; margin-bottom: 24px; }
        .section { margin-top: 14px; margin-bottom: 10px; }
        .muted { color:#444; }
        .grid { width:100%; border-collapse: collapse; border:1px solid #dde1e7; }
        .grid td { padding: 6px 8px; vertical-align: top; border:1px solid #dde1e7; }
        .label { width: 38%; color:#222; font-weight: 600; }
        .value { width: 62%; color:#111; }
        .hr { border-top: 1px solid #e5e7eb; margin: 14px 0; }
        .small { font-size: 11px; color:#333; line-height: 1.5; }
        .footer { margin-top: 20px; font-size: 10px; color:#555; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Company Header -->
        <table style="width:100%; margin-bottom: 10px;">
            <tr>
                <td style="width:20%; vertical-align: top; text-align: left;">
                    @php
                        $logoPath = public_path('uploads/invoice_logos/TBL-Logo-PNG.png');
                    @endphp
                    @if(file_exists($logoPath))
                        <img style="max-height: 90px; width: 90px;" src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) }}" />
                    @endif
                </td>
                <td style="width:60%; text-align: center;">
                    @php $biz = session('business'); @endphp
                    <div style="font-size:18px; font-weight:700;">{{ $biz->name ?? 'Techsense Bangladesh Ltd' }}</div>
                    <div style="font-size:11px; line-height:1.5; color:#333;">
                        {!! $biz->business_address ?? '' !!}
                        @if(!empty($biz->mobile))<br>Phone: {{ $biz->mobile }}@endif
                        @if(!empty($biz->website))<br>Website: {{ $biz->website }}@endif
                    </div>
                </td>
                <td style="width:20%;"></td>
            </tr>
        </table>

        <div class="title">Certificate of Investment</div>
        <div class="section small">
            This is to certify that the following person is a registered Investor of Techsense Bangladesh Ltd and has been allocated his investment for an order only for a short-term period mentioned below. Following are details of the allotment as on {{ optional($investor->received_date ? \Carbon\Carbon::parse($investor->received_date) : null)->format('d-M-Y') ?? '' }}.
        </div>

        <div class="hr"></div>

        <table class="grid">
            <tr>
                <td class="label">Investor Name:</td>
                <td class="value">{{ $investor->name }}</td>
            </tr>
            <tr>
                <td class="label">NID/Passport:</td>
                <td class="value">{{ $investor->nid ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Phone Number:</td>
                <td class="value">{{ $investor->phone ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Order No:</td>
                <td class="value">{{ $investor->invoice_no ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Order Amount:</td>
                <td class="value">{{ number_format((float)($investor->invest_amount ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total Investment Amount:</td>
                <td class="value">{{ number_format((float)($investor->invest_amount ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td class="label">Transaction Reference Number:</td>
                <td class="value">{{ $investor->txn_ref ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Agreed Rate of Return:</td>
                <td class="value">5-6% of the total amount invested or 40% of net profit</td>
            </tr>
            <tr>
                <td class="label">Risk of Investment:</td>
                <td class="value">Operational Losses or extended delays will not reduce investor principal amount. However profit payout may be capped (e.g. 5%) for delays beyond 30 days.</td>
            </tr>
            <tr>
                <td class="label">Agreed Time Period of Return:</td>
                <td class="value">3Month±15days.</td>
            </tr>
            <tr>
                <td class="label">Investment Opening Date:</td>
                <td class="value">{{ $investor->received_date ? \Carbon\Carbon::parse($investor->received_date)->format('d-M-Y') : '' }}</td>
            </tr>
            <tr>
                <td class="label">Expected Closing Date:</td>
                <td class="value">{{ $investor->received_date ? \Carbon\Carbon::parse($investor->received_date)->addDays(90)->format('d-M-Y') : '' }}</td>
            </tr>
        </table>

        <div class="hr"></div>

        <div class="section small">
            This agreement will be void automatically after the successful returns of the investment with profit or after completion of the “Agreed Time period of return” from the investment date unless any complain submitted within 30 days.
        </div>

        <div class="footer">
            System generated report, hence no signature required. Printed on {{ \Carbon\Carbon::now()->format('jS F Y h:i A') }}
        </div>
    </div>
</body>
</html>
