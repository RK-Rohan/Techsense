<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }}</title>
    @include('financial_report.partials.pdf_styles')
</head>
<body>
    <div class="statement-heading">
        <h3>{{ $business_name }}</h3>
        <h4>{{ $report_title }}</h4>
        <p>As on {{ \Carbon::parse($end_date)->format('d-F-Y') }}</p>
    </div>

    @include('financial_report.partials.financial_position_body', [
        'current' => $statements['current'],
        'previous' => $statements['previous'],
        'period' => $statements['period'],
    ])
</body>
</html>
