<?php

namespace App\Utils;

use App\Account;
use App\Transaction;
use DB;

/**
 * Builds the statutory financial statements (Statement of Financial Position,
 * Profit or Loss, Notes to the Accounts, Receipt & Payment and Cash Flow)
 * from the transactional data already recorded in the system.
 *
 * Every figure is derived; nothing is keyed in by hand. Each statement is
 * produced for a period and for the comparative period that precedes it, so
 * the views can render the two columns the printed statements use.
 */
class FinancialStatementUtil
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Returns every statement for a period and its comparative, ready for the views.
     */
    public function getStatements($business_id, $start_date, $end_date, $location_id = null)
    {
        [$prev_start, $prev_end] = $this->comparativePeriod($start_date, $end_date);

        $current = $this->buildPeriod($business_id, $start_date, $end_date, $location_id);
        $previous = $this->buildPeriod($business_id, $prev_start, $prev_end, $location_id);

        return [
            'current' => $current,
            'previous' => $previous,
            'period' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'prev_start_date' => $prev_start,
                'prev_end_date' => $prev_end,
            ],
        ];
    }

    /**
     * The comparative period is the same span immediately before the one selected,
     * which mirrors how the printed statements present the prior year.
     */
    public function comparativePeriod($start_date, $end_date)
    {
        $start = \Carbon::createFromFormat('Y-m-d', $start_date);
        $end = \Carbon::createFromFormat('Y-m-d', $end_date);

        $days = $start->diffInDays($end) + 1;

        $prev_end = $start->copy()->subDay();
        $prev_start = $prev_end->copy()->subDays($days - 1);

        return [$prev_start->format('Y-m-d'), $prev_end->format('Y-m-d')];
    }

    /**
     * Computes the full set of figures for a single period.
     */
    protected function buildPeriod($business_id, $start_date, $end_date, $location_id)
    {
        $profit_loss = $this->profitOrLoss($business_id, $start_date, $end_date, $location_id);
        $position = $this->financialPosition($business_id, $end_date, $location_id, $profit_loss);
        $receipt_payment = $this->receiptAndPayment($business_id, $start_date, $end_date, $location_id);
        $cash_flow = $this->cashFlow($business_id, $start_date, $end_date, $location_id, $receipt_payment);
        $notes = $this->notes($business_id, $start_date, $end_date, $location_id, $profit_loss, $position);

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'profit_loss' => $profit_loss,
            'position' => $position,
            'receipt_payment' => $receipt_payment,
            'cash_flow' => $cash_flow,
            'notes' => $notes,
        ];
    }

    /**
     * Profit or Loss and other Comprehensive Income.
     *
     * Cost of sales follows the periodic formula the statement footnotes:
     * opening inventory + purchases - closing inventory, plus carriage inward.
     */
    public function profitOrLoss($business_id, $start_date, $end_date, $location_id = null)
    {
        $day_before = \Carbon::createFromFormat('Y-m-d', $start_date)->subDay()->format('Y-m-d');

        $opening_stock = (float) $this->transactionUtil->getOpeningClosingStock($business_id, $day_before, $location_id, true);
        $closing_stock = (float) $this->transactionUtil->getOpeningClosingStock($business_id, $end_date, $location_id, false);

        $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start_date, $end_date, $location_id);
        $sell_details = $this->transactionUtil->getSellTotals($business_id, $start_date, $end_date, $location_id);

        $transaction_totals = $this->transactionUtil->getTransactionTotals(
            $business_id,
            ['purchase_return', 'sell_return', 'expense'],
            $start_date,
            $end_date,
            $location_id
        );

        $sales = (float) ($sell_details['total_sell_exc_tax'] ?? $sell_details['total_exc_tax'] ?? 0);
        $sell_return = (float) ($transaction_totals['total_sell_return_exc_tax'] ?? 0);
        $revenue = $sales - $sell_return;

        $purchases = (float) ($purchase_details['total_purchase_exc_tax'] ?? 0);
        $purchase_return = (float) ($transaction_totals['total_purchase_return_exc_tax'] ?? 0);
        $net_purchase = $purchases - $purchase_return;

        // Carriage inward: freight and additional charges incurred on purchases.
        $carriage_inward = (float) ($purchase_details['total_shipping_charges'] ?? 0)
            + (float) ($purchase_details['total_additional_expense'] ?? 0);

        $cost_of_sales = $opening_stock + $net_purchase + $carriage_inward - $closing_stock;
        $gross_profit = $revenue - $cost_of_sales;

        $expenses = $this->expenseBreakdown($business_id, $start_date, $end_date, $location_id);

        $administrative = $expenses['administrative_total'];
        $finance_cost = $expenses['finance_total'];

        $operating_profit = $gross_profit - $administrative;
        $non_operating_income = 0.0;
        $profit_before_tax = $operating_profit + $non_operating_income - $finance_cost;

        return [
            'revenue' => $revenue,
            'sales' => $sales,
            'sell_return' => $sell_return,
            'opening_stock' => $opening_stock,
            'closing_stock' => $closing_stock,
            'purchases' => $purchases,
            'purchase_return' => $purchase_return,
            'net_purchase' => $net_purchase,
            'carriage_inward' => $carriage_inward,
            'cost_of_sales' => $cost_of_sales,
            'gross_profit' => $gross_profit,
            'administrative_expenses' => $administrative,
            'administrative_breakdown' => $expenses['administrative'],
            'operating_profit' => $operating_profit,
            'non_operating_income' => $non_operating_income,
            'finance_cost' => $finance_cost,
            'finance_breakdown' => $expenses['finance'],
            'profit_before_tax' => $profit_before_tax,
        ];
    }

    /**
     * Splits expenses by category. Categories whose name reads as a financing
     * charge are reported under Finance Cost; everything else is administrative.
     */
    protected function expenseBreakdown($business_id, $start_date, $end_date, $location_id = null)
    {
        $query = Transaction::leftJoin('expense_categories as ec', 'transactions.expense_category_id', '=', 'ec.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'expense')
            ->whereDate('transactions.transaction_date', '>=', $start_date)
            ->whereDate('transactions.transaction_date', '<=', $end_date);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $rows = $query->select(
            DB::raw("COALESCE(ec.name, 'Other Expenses') as category"),
            DB::raw('SUM(transactions.final_total) as amount')
        )
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $administrative = [];
        $finance = [];
        $administrative_total = 0.0;
        $finance_total = 0.0;

        foreach ($rows as $row) {
            $amount = (float) $row->amount;
            if ($this->isFinanceCategory($row->category)) {
                $finance[$row->category] = $amount;
                $finance_total += $amount;
            } else {
                $administrative[$row->category] = $amount;
                $administrative_total += $amount;
            }
        }

        return [
            'administrative' => $administrative,
            'administrative_total' => $administrative_total,
            'finance' => $finance,
            'finance_total' => $finance_total,
        ];
    }

    /**
     * Finance cost covers interest and bank charges, as the statements group them.
     */
    protected function isFinanceCategory($name)
    {
        foreach (['interest', 'bank charge', 'bank-charge', 'finance cost', 'loan charge'] as $needle) {
            if (stripos($name, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Statement of Financial Position as at the period end.
     */
    public function financialPosition($business_id, $end_date, $location_id = null, $profit_loss = null)
    {
        $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, null, $end_date, $location_id);
        $sell_details = $this->transactionUtil->getSellTotals($business_id, null, $end_date, $location_id);

        $sell_return_details = $this->transactionUtil->getTransactionTotals(
            $business_id,
            ['sell_return'],
            null,
            $end_date,
            $location_id
        );

        $closing_stock = (float) $this->transactionUtil->getOpeningClosingStock($business_id, $end_date, $location_id, false);

        $accounts = $this->accountBalances($business_id, $end_date, $location_id);

        $cash_and_equivalent = 0.0;
        foreach ($accounts as $balance) {
            $cash_and_equivalent += (float) $balance;
        }

        $accounts_receivable = (float) ($sell_details['invoice_due'] ?? 0)
            - (float) ($sell_return_details['total_sell_return_inc_tax'] ?? 0);
        $accounts_payable = (float) ($purchase_details['purchase_due'] ?? 0);

        // Nothing in the system records fixed assets or prepayments separately,
        // so these lines stay at nil until such accounts are maintained.
        $non_current_assets = 0.0;
        $advance_deposit = 0.0;
        $loans_and_borrowing = 0.0;
        $expenses_payable = 0.0;
        $paid_up_capital = $this->capitalBalance($business_id, $end_date);

        $total_current_assets = $closing_stock + $cash_and_equivalent + $accounts_receivable + $advance_deposit;
        $total_assets = $non_current_assets + $total_current_assets;

        $total_current_liabilities = $accounts_payable + $loans_and_borrowing + $expenses_payable;

        // Retained earnings is the residual that balances the statement.
        $retained_earnings = $total_assets - $total_current_liabilities - $paid_up_capital;
        $total_equity = $paid_up_capital + $retained_earnings;

        return [
            'non_current_assets' => $non_current_assets,
            'closing_inventory' => $closing_stock,
            'cash_and_equivalent' => $cash_and_equivalent,
            'cash_breakdown' => $accounts,
            'accounts_receivable' => $accounts_receivable,
            'advance_deposit' => $advance_deposit,
            'total_current_assets' => $total_current_assets,
            'total_assets' => $total_assets,
            'paid_up_capital' => $paid_up_capital,
            'retained_earnings' => $retained_earnings,
            'total_equity' => $total_equity,
            'accounts_payable' => $accounts_payable,
            'loans_and_borrowing' => $loans_and_borrowing,
            'expenses_payable' => $expenses_payable,
            'total_current_liabilities' => $total_current_liabilities,
            'total_equity_and_liabilities' => $total_equity + $total_current_liabilities,
            'profit_for_period' => $profit_loss['profit_before_tax'] ?? 0,
        ];
    }

    /**
     * Balance of each payment account up to a date, keyed by account name.
     */
    protected function accountBalances($business_id, $end_date, $location_id = null)
    {
        $query = Account::leftJoin('account_transactions as AT', 'AT.account_id', '=', 'accounts.id')
            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->whereDate('AT.operation_date', '<=', $end_date);

        $account_ids = $this->permittedAccountIds($business_id, $location_id);
        if ($account_ids !== null) {
            $query->whereIn('accounts.id', $account_ids);
        }

        return $query->select(
            'accounts.name',
            DB::raw("SUM( IF(AT.type='credit', AT.amount, -1*AT.amount) ) as balance")
        )
            ->groupBy('accounts.id')
            ->get()
            ->pluck('balance', 'name');
    }

    /**
     * Opening balances deposited into accounts stand in for paid up capital.
     */
    protected function capitalBalance($business_id, $end_date)
    {
        return (float) DB::table('account_transactions as AT')
            ->join('accounts', 'accounts.id', '=', 'AT.account_id')
            ->whereNull('AT.deleted_at')
            ->whereNull('accounts.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('AT.sub_type', 'opening_balance')
            ->whereDate('AT.operation_date', '<=', $end_date)
            ->sum(DB::raw("IF(AT.type='credit', AT.amount, -1*AT.amount)"));
    }

    /**
     * Account ids the user may see, or null when every account is permitted.
     */
    protected function permittedAccountIds($business_id, $location_id = null)
    {
        $permitted_locations = auth()->user()->permitted_locations();

        $locations = \App\BusinessLocation::where('business_id', $business_id);
        if ($permitted_locations != 'all') {
            $locations->whereIn('id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $locations->where('id', $location_id);
        }

        if ($permitted_locations == 'all' && empty($location_id)) {
            return null;
        }

        $account_ids = [];
        foreach ($locations->get() as $location) {
            if (empty($location->default_payment_accounts)) {
                continue;
            }
            $default_payment_accounts = json_decode($location->default_payment_accounts, true);
            foreach ($default_payment_accounts as $account) {
                if (! empty($account['is_enabled']) && ! empty($account['account'])) {
                    $account_ids[] = $account['account'];
                }
            }
        }

        return array_values(array_unique($account_ids));
    }

    /**
     * Receipt & Payment account: actual money in and out over the period.
     */
    public function receiptAndPayment($business_id, $start_date, $end_date, $location_id = null)
    {
        $day_before = \Carbon::createFromFormat('Y-m-d', $start_date)->subDay()->format('Y-m-d');

        $opening = $this->accountBalances($business_id, $day_before, $location_id);
        $closing = $this->accountBalances($business_id, $end_date, $location_id);

        $opening_balance = 0.0;
        foreach ($opening as $balance) {
            $opening_balance += (float) $balance;
        }

        $closing_balance = 0.0;
        foreach ($closing as $balance) {
            $closing_balance += (float) $balance;
        }

        $receipts = $this->paymentTotals($business_id, $start_date, $end_date, $location_id, ['sell', 'sell_return'], false);
        $payments = $this->paymentTotals($business_id, $start_date, $end_date, $location_id, ['purchase', 'purchase_return', 'expense'], true);

        $receive_from_customers = (float) ($receipts['sell'] ?? 0);
        $payment_to_suppliers = (float) ($payments['purchase'] ?? 0);
        $payment_as_expenses = (float) ($payments['expense'] ?? 0);

        // Deposits and withdrawals recorded straight against an account are real
        // cash movements that never pass through transaction_payments, so they
        // are reported on their own lines. Without them the account would not
        // reconcile from its opening to its closing balance.
        $deposits = $this->accountMovement($business_id, $start_date, $end_date, $location_id, 'deposit');

        $receive_from_others = (float) $deposits['credit'];
        $payment_others = (float) $deposits['debit'];

        $total_payment = $payment_to_suppliers + $payment_as_expenses + $payment_others;

        // Any remaining ledger movement the lines above do not explain. Carrying
        // it as a receipt keeps the statement reconciling from the opening to the
        // closing balance rather than silently losing cash.
        $unreconciled = round(
            ($closing_balance - $opening_balance) - ($receive_from_customers + $receive_from_others - $total_payment),
            2
        );

        $total_received = $receive_from_customers + $receive_from_others + $unreconciled;

        return [
            'opening_balance' => $opening_balance,
            'opening_breakdown' => $opening,
            'receive_from_customers' => $receive_from_customers,
            'receive_from_others' => $receive_from_others,
            'total_received' => $total_received,
            'payment_to_suppliers' => $payment_to_suppliers,
            'payment_as_expenses' => $payment_as_expenses,
            'payment_others' => $payment_others,
            'total_payment' => $total_payment,
            'unreconciled' => $unreconciled,
            'closing_balance' => $closing_balance,
            'closing_breakdown' => $closing,
        ];
    }

    /**
     * Money moved directly against accounts in the period, split into cash in
     * (credit) and cash out (debit), for a given ledger sub type.
     */
    protected function accountMovement($business_id, $start_date, $end_date, $location_id, $sub_type)
    {
        $query = DB::table('account_transactions as AT')
            ->join('accounts', 'accounts.id', '=', 'AT.account_id')
            ->whereNull('AT.deleted_at')
            ->whereNull('accounts.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('AT.sub_type', $sub_type)
            ->whereDate('AT.operation_date', '>=', $start_date)
            ->whereDate('AT.operation_date', '<=', $end_date);

        $account_ids = $this->permittedAccountIds($business_id, $location_id);
        if ($account_ids !== null) {
            $query->whereIn('accounts.id', $account_ids);
        }

        $row = $query->select(
            DB::raw("COALESCE(SUM(IF(AT.type='credit', AT.amount, 0)), 0) as total_credit"),
            DB::raw("COALESCE(SUM(IF(AT.type='debit', AT.amount, 0)), 0) as total_debit")
        )->first();

        return [
            'credit' => (float) ($row->total_credit ?? 0),
            'debit' => (float) ($row->total_debit ?? 0),
        ];
    }

    /**
     * Sums settled payments over the period, grouped by the kind of transaction
     * they settle. $outgoing selects money paid out rather than received.
     */
    protected function paymentTotals($business_id, $start_date, $end_date, $location_id, array $types, $outgoing)
    {
        $query = DB::table('transaction_payments as tp')
            ->leftJoin('transactions as t', 't.id', '=', 'tp.transaction_id')
            ->where('tp.business_id', $business_id)
            ->whereDate('tp.paid_on', '>=', $start_date)
            ->whereDate('tp.paid_on', '<=', $end_date);

        if (! empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        $rows = $query->select(
            DB::raw("COALESCE(t.type, 'other') as transaction_type"),
            DB::raw('SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)) as amount')
        )
            ->groupBy('transaction_type')
            ->get();

        $totals = [];
        foreach ($rows as $row) {
            $type = $row->transaction_type;
            $amount = (float) $row->amount;

            if (in_array($type, $types, true)) {
                $key = str_replace('_return', '', $type);
                $totals[$key] = ($totals[$key] ?? 0) + $amount;
            }
        }

        return $totals;
    }

    /**
     * Cash Flow Statement, presented by the direct method as in the printed form.
     */
    public function cashFlow($business_id, $start_date, $end_date, $location_id = null, $receipt_payment = null)
    {
        if ($receipt_payment === null) {
            $receipt_payment = $this->receiptAndPayment($business_id, $start_date, $end_date, $location_id);
        }

        $expenses = $this->expenseBreakdown($business_id, $start_date, $end_date, $location_id);

        $cash_from_customers = $receipt_payment['receive_from_customers'];

        // Interest and income tax are shown on their own lines below, so they
        // are excluded here to avoid counting the same payment twice.
        $interest_paid = $expenses['finance_total'];
        $income_tax_paid = $this->incomeTaxPaid($expenses['administrative']);

        $cash_to_suppliers = $receipt_payment['payment_to_suppliers']
            + $receipt_payment['payment_as_expenses']
            - $interest_paid
            - $income_tax_paid;

        // The residual on the Receipt & Payment account is cash the itemised
        // lines do not explain; it is carried here so both statements agree.
        $operating = $cash_from_customers - $cash_to_suppliers - $interest_paid - $income_tax_paid
            + $receipt_payment['unreconciled'];


        // Fixed asset movements are not tracked separately yet.
        $purchase_of_fixed_assets = 0.0;
        $sale_of_fixed_assets = 0.0;
        $investing = $sale_of_fixed_assets - $purchase_of_fixed_assets;

        // Deposits into and withdrawals out of the accounts are financing movements.
        $loan_received = $receipt_payment['receive_from_others'];
        $loan_repayment = $receipt_payment['payment_others'];
        $financing = $loan_received - $loan_repayment;

        $net_increase = $operating + $investing + $financing;

        return [
            'cash_from_customers' => $cash_from_customers,
            'cash_to_suppliers' => $cash_to_suppliers,
            'interest_paid' => $interest_paid,
            'income_tax_paid' => $income_tax_paid,
            'net_operating' => $operating,
            'purchase_of_fixed_assets' => $purchase_of_fixed_assets,
            'sale_of_fixed_assets' => $sale_of_fixed_assets,
            'net_investing' => $investing,
            'loan_received' => $loan_received,
            'loan_repayment' => $loan_repayment,
            'net_financing' => $financing,
            'net_increase' => $net_increase,
            'opening_cash' => $receipt_payment['opening_balance'],
            'ending_cash' => $receipt_payment['opening_balance'] + $net_increase,
        ];
    }

    /**
     * Income tax recorded as an expense category, reported on its own cash flow line.
     */
    protected function incomeTaxPaid(array $administrative)
    {
        $total = 0.0;
        foreach ($administrative as $category => $amount) {
            if (stripos($category, 'income tax') !== false) {
                $total += (float) $amount;
            }
        }

        return $total;
    }

    /**
     * Notes to the Accounts: the supporting breakdown behind each statement line.
     */
    public function notes($business_id, $start_date, $end_date, $location_id = null, $profit_loss = null, $position = null)
    {
        if ($profit_loss === null) {
            $profit_loss = $this->profitOrLoss($business_id, $start_date, $end_date, $location_id);
        }
        if ($position === null) {
            $position = $this->financialPosition($business_id, $end_date, $location_id, $profit_loss);
        }

        return [
            'non_current_assets' => [],
            'closing_stock' => ['Closing Inventory' => $position['closing_inventory']],
            'cash_and_equivalent' => collect($position['cash_breakdown'])->toArray(),
            'accounts_receivable' => $this->contactDues($business_id, $end_date, $location_id, 'sell'),
            'advance_deposit' => [],
            'paid_up_capital' => ['Paid up Capital' => $position['paid_up_capital']],
            'retained_earnings' => [
                'Profit During the Period' => $profit_loss['profit_before_tax'],
                'Retained Earnings' => $position['retained_earnings'],
            ],
            'accounts_payable' => $this->contactDues($business_id, $end_date, $location_id, 'purchase'),
            'loans_and_borrowing' => [],
            'expenses_payable' => [],
            'revenue' => ['Sales' => $profit_loss['revenue']],
            'purchase' => [
                'Opening Stock' => $profit_loss['opening_stock'],
                'Purchase Accounts' => $profit_loss['net_purchase'],
                'Carriage Inward' => $profit_loss['carriage_inward'],
                'Closing Stock' => -1 * $profit_loss['closing_stock'],
            ],
            'administrative_expenses' => $profit_loss['administrative_breakdown'],
            'finance_cost' => $profit_loss['finance_breakdown'],
        ];
    }

    /**
     * Outstanding balance per contact, which backs the receivable and payable notes.
     */
    protected function contactDues($business_id, $end_date, $location_id, $type)
    {
        $return_type = $type === 'sell' ? 'sell_return' : 'purchase_return';

        $query = Transaction::join('contacts as c', 'transactions.contact_id', '=', 'c.id')
            ->where('transactions.business_id', $business_id)
            ->whereIn('transactions.type', [$type, $return_type])
            ->where('transactions.status', '!=', 'draft')
            ->whereDate('transactions.transaction_date', '<=', $end_date);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }
        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $rows = $query->select(
            DB::raw("COALESCE(NULLIF(TRIM(c.supplier_business_name), ''), CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,''))) as contact_name"),
            DB::raw("SUM(
                IF(transactions.type = '".$return_type."', -1, 1) *
                (transactions.final_total - COALESCE((SELECT SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)) FROM transaction_payments as tp WHERE tp.transaction_id = transactions.id), 0))
            ) as due")
        )
            ->groupBy('c.id')
            ->havingRaw('ROUND(due, 2) != 0')
            ->orderBy('contact_name')
            ->get();

        $dues = [];
        foreach ($rows as $row) {
            $dues[trim($row->contact_name)] = (float) $row->due;
        }

        return $dues;
    }
}
