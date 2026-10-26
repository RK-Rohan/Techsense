<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Delivery Challan {{ $receipt_details->invoice_no }}</title>
    <style>body { margin: 0; padding: 0; }</style>
    @include('sale_pos.receipts.partial.quotation_style', ['quotation_pdf' => true])
</head>
<body>
    @include('sale_pos.receipts.partial.sales_document', ['document_kind' => 'challan', 'quotation_pdf' => true])
</body>
</html>
