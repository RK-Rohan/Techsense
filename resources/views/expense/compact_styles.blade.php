<style>
    .expense-compact .box { margin-bottom: 16px; }
    .expense-compact .box > .box-body { padding: 16px !important; }
    .expense-compact .box > .box-header { padding: 12px 16px 0 !important; }
    .expense-compact .form-group { margin-bottom: 10px; }
    .expense-compact .help-block { margin: 4px 0 0; font-size: 12px; }
    .expense-compact textarea.form-control { height: 38px; min-height: 38px; resize: vertical; }
    .expense-compact .expense-header-fields,
    .expense-compact #expense_payment_rows > .payment_row > .row {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        column-gap: 16px;
        margin: 0;
    }
    .expense-compact .expense-header-fields::before,
    .expense-compact .expense-header-fields::after,
    .expense-compact #expense_payment_rows > .payment_row > .row::before,
    .expense-compact #expense_payment_rows > .payment_row > .row::after,
    .expense-compact .expense-header-fields > .clearfix,
    .expense-compact #expense_payment_rows > .payment_row > .row > .clearfix { display: none; }
    .expense-compact .expense-header-fields > [class*="col-"],
    .expense-compact #expense_payment_rows > .payment_row > .row > [class*="col-"] {
        width: auto;
        min-width: 0;
        padding: 0;
        float: none;
    }
    .expense-compact #expense_payment_rows > .payment_row > .row > .col-md-12 {
        grid-column: 1 / -1;
    }
    .expense-compact #expense_payment_rows > .payment_row {
        padding-bottom: 10px;
        margin-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }
    .expense-compact .well { padding: 12px; margin-bottom: 0; }
    @media (min-width: 768px) {
        .expense-compact .expense-header-fields,
        .expense-compact #expense_payment_rows > .payment_row > .row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1200px) {
        .expense-compact .expense-header-fields,
        .expense-compact #expense_payment_rows > .payment_row > .row {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
</style>
