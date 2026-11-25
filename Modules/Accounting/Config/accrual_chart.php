<?php

// Account codes and explicit parentage from the client's supplied chart.
return [
    'asset' => ['Assets', '1000–1999', [
        ['Current Assets', '1000–1499', [
            [1000, 'Cash & Cash Equivalents', [1001 => 'Petty Cash', 1002 => 'Operating Bank Account', 1003 => 'Short-Term Fixed Deposits']],
            [1100, 'Trade & Other Receivables', [1101 => 'Accounts Receivable (Trade Debtors)', 1102 => 'Allowance for Expected Credit Losses', 1103 => 'Accrued / Unbilled Revenue']],
            [1400, 'Advance, Deposits & Prepayment', [1401 => 'Prepaid Rent & Insurance', 1402 => 'Advances to Suppliers', 1403 => 'Prepaid Corporate Tax / Input VAT']],
        ]],
        ['Non-Current Assets', '1500–1999', [
            [1500, 'Property, Plant & Equipment', []],
            [1600, 'Intangible Assets', [1521 => 'Software, Patents & Licenses', 1522 => 'Accumulated Amortization']],
            [1700, 'Investment', [1531 => 'Stock Market Investments']],
        ]],
    ]],
    'liability' => ['Liabilities', '2000–2999', [
        ['Current Liabilities', '2000–2899', [
            [2000, 'Trade & Other Payables', [2111 => 'Accounts Payable (Trade Creditors)', 2112 => 'Goods Received Not Invoiced (GRNI)']],
            [2600, 'Accrued Liabilities & Provisions', [2121 => 'Accrued Salaries & Wages', 2122 => 'Accrued Utilities & Audit Fees', 2123 => 'Accrued Interest Payable']],
            [2700, 'Deferred / Unearned Revenue', [2131 => 'Customer Deposits & Advances', 2132 => 'Unearned Subscription / Service Income']],
            [2800, 'Taxes & Short-Term Debt', [2141 => 'Sales Tax / VAT & Withholding Payable', 2142 => 'Corporate Income Tax Payable', 2143 => 'Short-Term Bank Overdraft / Line']],
            [2500, 'Short Term Loan', [2501 => 'Bank Loan']],
            [2400, 'Others Payable', []],
            [2450, 'Liabilities for Expence', []],
        ]],
        ['Non-Current Liabilities', '2900–2999', [
            [2900, 'Long-Term Loan', [2511 => 'Long-Term Term Loans', 2512 => 'Non-Current Lease Liabilities']],
        ]],
    ]],
    'equity' => ['Equity', '3000–3999', [
        ['Equity', '3000–3999', [
            [3000, 'Contributed Capital', [3111 => 'Common / Share Capital', 3112 => 'Share Premium / Additional Paid-In Capital']],
            [3100, 'Retained Earnings', [3121 => 'Retained Earnings (Prior Years)', 3122 => 'Current Period Net Income / (Loss)', 3123 => 'Dividends Declared']],
            [3300, 'Reserves', [3301 => 'Regerve for Future']],
        ]],
    ]],
    'revenue' => ['Revenue', '4000–4999', [
        ['Operating Revenue', '4000–4999', [
            [4000, 'Product & Service Sales', [4001 => 'Product Sales Revenue', 4002 => 'Service & Consulting Revenue', 4003 => 'Sales Returns & Allowances', 4004 => 'Sales Discounts Allowed']],
        ]],
    ]],
    'cost_of_goods_sold' => ['Cost of Goods Sold (COGS)', '5000–5999', [
        ['Cost of Goods Sold (COGS)', '5100', [
            [5110, 'Cost of Goods Sold (COGS)', [5001 => 'Purchase', 5002 => 'Direct Labor & Subcontractors', 5003 => 'Freight-In & Customs Duties', 5004 => 'Carriage inward']],
        ]],
    ]],
    'expenses' => ['Expenses', '6000–6999', [
        ['Marketing Expenses', '6100', [
            [6110, 'Selling & Marketing Expenses', [6111 => 'Advertising & Digital Marketing', 6112 => 'Sales Commissions & Outward Freight']],
        ]],
        ['Indirect Expenses', '6200', [
            [6210, 'General & Administrative (G&A)', [6211 => 'Administrative Salaries & Benefits', 6212 => 'Office Rent, Utilities & Software', 6213 => 'Depreciation & Amortization Expense', 6214 => 'Bad Debt / Credit Loss Expense']],
        ]],
    ]],
    'non_operating_income' => ['Non-Operating Income', '7000–7999', [
        ['Financial & Investment Income', '7100', [
            [7110, 'Interest & Dividend Income', [7111 => 'Bank & Deposit Interest Income', 7112 => 'Dividend Income from Investments']],
        ]],
        ['Other Non-Operating Gains', '7200', [
            [7210, 'Disposal & Foreign Exchange Gains', [7211 => 'Gain on Disposal of Fixed Assets', 7212 => 'Foreign Exchange Gains (Realized/Unrealized)', 7213 => 'Scrap Sales & Miscellaneous Income']],
        ]],
    ]],
    'finance_costs' => ['Finance Costs', '8000–8999', [
        ['Finance Costs & Non-Operating Losses', '8100', [
            [8110, 'Borrowing & FX Costs', [8111 => 'Interest Expense on Loans & Leases', 8112 => 'Foreign Exchange Losses & Bank Charges']],
        ]],
    ]],
];
