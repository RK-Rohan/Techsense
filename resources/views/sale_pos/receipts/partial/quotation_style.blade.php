<style>
    @page { size: A4 portrait; margin: 14mm 10mm 28mm; }
    .quotation-document { font-family: Arial, Helvetica, sans-serif; font-size: 9.8pt; line-height: 1.17; color: #000; }
    .quotation-document * { box-sizing: border-box; }
    .quotation-document table { width: 100%; border-collapse: collapse; border-spacing: 0; margin: 0; color: #000; }
    .quotation-document td, .quotation-document th { padding: 0; font-size: 9.8pt; line-height: 1.17; border: 0; }
    .quotation-document p { margin: 0; }
    .quotation-document .quotation-header td { vertical-align: bottom; }
    .quotation-document .quotation-logo-cell { width: 90pt; }
    .quotation-document .quotation-logo-frame { width: 90pt; height: 55pt; position: relative; }
    .quotation-document .quotation-logo { width: 112pt; height: auto; display: block; position: absolute; left: -10pt; bottom: -9pt; }
    .quotation-document .quotation-brand { text-align: right; border-bottom: 1pt solid #ff3030; padding-bottom: 2pt; }
    .quotation-document .quotation-company { color: #1255a0; font-size: 26.5pt; font-weight: bold; line-height: 1.15; margin: 0 0 2pt; }
    .quotation-document .quotation-tagline { font-size: 12.27pt; font-weight: bold; font-style: italic; }
    .quotation-document .quotation-title { text-align: center; margin: 8pt 0 5pt; }
    .quotation-document .quotation-title span { display: inline-block; border: .75pt solid #1255a0; padding: 1pt 6pt; font-size: 15pt; font-weight: bold; line-height: 1.15; }
    .quotation-document .quotation-meta td { padding: 0 0 6pt; }
    .quotation-document .quotation-rule { height: 2pt; border-top: 1pt solid #145ba6; border-bottom: .6pt solid #4bbbd1; }
    .quotation-document .quotation-customer { margin-top: 4pt; }
    .quotation-document .quotation-customer td { vertical-align: top; padding-bottom: 2pt; }
    .quotation-document .quotation-sales { margin: 12pt 0; }
    .quotation-document .quotation-items { table-layout: fixed; }
    .quotation-document .quotation-items th, .quotation-document .quotation-items td { border: .75pt solid #000; padding: 1pt; vertical-align: middle; overflow-wrap: break-word; word-wrap: break-word; }
    .quotation-document .quotation-items th { text-align: center; font-weight: bold; background: #fff; padding: 2pt 1pt; }
    .quotation-document .quotation-items thead { display: table-header-group; }
    .quotation-document .quotation-items tr { page-break-inside: avoid; }
    .quotation-document .quotation-center { text-align: center; }
    .quotation-document .quotation-right { text-align: right; }
    .quotation-document .product-description { font-size: 9pt; line-height: 1.17; margin-top: 3pt; }
    .quotation-document .product-description p, .quotation-document .product-description div { margin: 0; }
    .quotation-document .product-description ul, .quotation-document .product-description ol { padding: 0; margin: 2pt 0 2pt 12pt; }
    .quotation-document .product-description li { padding: 0; margin: 0; }
    .quotation-document .product-description table td, .quotation-document .product-description table th { font-size: 8pt; }
    .quotation-document .quotation-summary { margin-top: 5pt; border-top: .75pt solid #000; }
    .quotation-document .quotation-summary > tbody > tr > td { padding-top: 6pt; vertical-align: top; }
    .quotation-document .quotation-totals th { text-align: left; }
    .quotation-document .quotation-totals td { text-align: right; }
    .quotation-document .quotation-totals th, .quotation-document .quotation-totals td { padding-bottom: 1pt; }
    .quotation-document .quotation-terms { margin-top: 2pt; }
    .quotation-document .quotation-terms tr { page-break-inside: avoid; }
    .quotation-document .quotation-terms td { vertical-align: top; }
    .quotation-document .quotation-term-number { width: 12pt; white-space: nowrap; }
    .quotation-document .quotation-signoff { margin-top: 12pt; page-break-inside: avoid; }
    .quotation-document .quotation-signoff-name { margin-top: 14pt; }
    .quotation-document .quotation-signoff p { margin-bottom: 2pt; }
    .quotation-document.quotation-footer { position: fixed; bottom: -22mm; left: 0; right: 0; text-align: center; font-size: 8.5pt; line-height: 1.6; }
    .quotation-document .quotation-generated { font-size: 7.5pt; margin-bottom: 12pt; }
    .quotation-document.quotation-footer .quotation-rule { margin-bottom: 5pt; }
    @if(empty($quotation_pdf))
    @media screen {
        .quotation-document { max-width: 190mm; margin: 0 auto; background: #fff; }
        .quotation-document.quotation-footer { position: static; margin-top: 36pt; }
    }
    @endif
    @media print {
        .quotation-document { width: 100%; max-width: none; }
        .quotation-document.quotation-footer { position: fixed; }
    }
</style>
