<?php

namespace Modules\Accounting\Utils;

use App\Business;
use App\Transaction;
use App\TransactionPayment;
use App\Utils\Util;
use DB;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

class AccountingUtil extends Util
{
    public function nextPostingGlCode(AccountingAccount $ledger): ?string
    {
        if (! preg_match('/^[1-8][0-9]{3}$/', (string) $ledger->gl_code)) {
            return null;
        }

        $start = (int) $ledger->gl_code + 1;
        $seededCodes = [];
        foreach (config('accounting.accrual_chart')[$ledger->account_primary_type][2] ?? [] as [, , $ledgers]) {
            foreach ($ledgers as [$code, , $children]) {
                if ((string) $code === $ledger->gl_code && $children) {
                    $seededCodes = array_keys($children);
                    $start = min($seededCodes);
                }
            }
        }

        // Keep each posting series in its own ten-code block.
        $end = intdiv($start, 10) * 10 + 9;
        if ($end > 8999) {
            return null;
        }

        $accounts = AccountingAccount::where('business_id', $ledger->business_id)
            ->whereIn('gl_code', array_map('strval', range($start, $end)))
            ->get(['gl_code', 'parent_account_id']);
        $last = max(array_merge([$start - 1], $seededCodes, $accounts
            ->where('parent_account_id', $ledger->id)->pluck('gl_code')->map(fn ($code) => (int) $code)->all()));
        $usedCodes = $accounts->pluck('gl_code')->all();

        for ($code = $last + 1; $code <= $end; $code++) {
            if (! in_array((string) $code, $usedCodes, true)) {
                return (string) $code;
            }
        }

        return null;
    }

    public function balanceFormula($accounting_accounts_alias = 'accounting_accounts',
                                 $accounting_account_transaction_alias = 'AAT')
    {
        return "SUM( IF(
            ($accounting_accounts_alias.account_primary_type='asset' AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type IN ('expense', 'expenses', 'cost_of_goods_sold', 'finance_costs') AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type IN ('income', 'revenue', 'non_operating_income') AND $accounting_account_transaction_alias.type='credit')
            OR ($accounting_accounts_alias.account_primary_type='equity' AND $accounting_account_transaction_alias.type='credit')
            OR ($accounting_accounts_alias.account_primary_type='liability' AND $accounting_account_transaction_alias.type='credit'), 
            amount, -1*amount)) as balance";
    }

    public function getAccountingSettings($business_id)
    {
        $accounting_settings = Business::where('id', $business_id)
                                ->value('accounting_settings');

        $accounting_settings = ! empty($accounting_settings) ? json_decode($accounting_settings, true) : [];

        return $accounting_settings;
    }

    public function getAgeingReport($business_id, $type, $group_by, $location_id = null)
    {
        $today = \Carbon::now()->format('Y-m-d');
        $query = Transaction::where('transactions.business_id', $business_id);

        if (AccountingAccount::whereIn('id', [$deposit_to, $payment_account])->where('is_control_ledger', true)->exists()) {
            throw new \DomainException(__('accounting::lang.control_ledger_posting_error'));
        }

        if ($type == 'sell') {
            $query->where('transactions.type', 'sell')
            ->where('transactions.status', 'final');
        } elseif ($type == 'purchase') {
            $query->where('transactions.type', 'purchase')
                ->where('transactions.status', 'received');
        }

        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $dues = $query->whereNotNull('transactions.pay_term_number')
                ->whereIn('transactions.payment_status', ['partial', 'due'])
                ->join('contacts as c', 'c.id', '=', 'transactions.contact_id')
                ->select(
                    DB::raw(
                        'DATEDIFF(
                            "'.$today.'", 
                            IF(
                                transactions.pay_term_type="days",
                                DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY),
                                DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH)
                            )
                        ) as diff'
                    ),
                    DB::raw('SUM(transactions.final_total - 
                        (SELECT COALESCE(SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)), 0) 
                        FROM transaction_payments as tp WHERE tp.transaction_id = transactions.id) )  
                        as total_due'),

                    'c.name as contact_name',
                    'transactions.contact_id',
                    'transactions.invoice_no',
                    'transactions.ref_no',
                    'transactions.transaction_date',
                    DB::raw('IF(
                        transactions.pay_term_type="days",
                        DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY),
                        DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH)
                    ) as due_date')
                )
                ->groupBy('transactions.id')
                ->get();

        $report_details = [];
        if ($group_by == 'contact') {
            foreach ($dues as $due) {
                if (! isset($report_details[$due->contact_id])) {
                    $report_details[$due->contact_id] = [
                        'name' => $due->contact_name,
                        '<1' => 0,
                        '1_30' => 0,
                        '31_60' => 0,
                        '61_90' => 0,
                        '>90' => 0,
                        'total_due' => 0,
                    ];
                }

                if ($due->diff < 1) {
                    $report_details[$due->contact_id]['<1'] += $due->total_due;
                } elseif ($due->diff >= 1 && $due->diff <= 30) {
                    $report_details[$due->contact_id]['1_30'] += $due->total_due;
                } elseif ($due->diff >= 31 && $due->diff <= 60) {
                    $report_details[$due->contact_id]['31_60'] += $due->total_due;
                } elseif ($due->diff >= 61 && $due->diff <= 90) {
                    $report_details[$due->contact_id]['61_90'] += $due->total_due;
                } elseif ($due->diff > 90) {
                    $report_details[$due->contact_id]['>90'] += $due->total_due;
                }

                $report_details[$due->contact_id]['total_due'] += $due->total_due;
            }
        } elseif ($group_by == 'due_date') {
            $report_details = [
                'current' => [],
                '1_30' => [],
                '31_60' => [],
                '61_90' => [],
                '>90' => [],
            ];
            foreach ($dues as $due) {
                $temp_array = [
                    'transaction_date' => $this->format_date($due->transaction_date),
                    'due_date' => $this->format_date($due->due_date),
                    'ref_no' => $due->ref_no,
                    'invoice_no' => $due->invoice_no,
                    'contact_name' => $due->contact_name,
                    'due' => $due->total_due,
                ];
                if ($due->diff < 1) {
                    $report_details['current'][] = $temp_array;
                } elseif ($due->diff >= 1 && $due->diff <= 30) {
                    $report_details['1_30'][] = $temp_array;
                } elseif ($due->diff >= 31 && $due->diff <= 60) {
                    $report_details['31_60'][] = $temp_array;
                } elseif ($due->diff >= 61 && $due->diff <= 90) {
                    $report_details['61_90'][] = $temp_array;
                } elseif ($due->diff > 90) {
                    $report_details['>90'][] = $temp_array;
                }
            }
        }

        return $report_details;
    }

    /**
     * Function to delete a mapping
     */
    public function deleteMap($transaction_id, $transaction_payment_id){
        AccountingAccountsTransaction::where('transaction_id', $transaction_id)
            ->whereIn('map_type', ['payment_account', 'deposit_to'])
            ->where('transaction_payment_id', $transaction_payment_id)
            ->delete();
    }

    /**
     * Function to save a mapping
     */
    public function saveMap($type, $id, $user_id, $business_id, $deposit_to, $payment_account, $note = null){
        if (AccountingAccount::whereIn('id', [$deposit_to, $payment_account])->where('is_control_ledger', true)->exists()) {
            throw new \DomainException(__('accounting::lang.control_ledger_posting_error'));
        }

        if ($type == 'sell') {
            $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstorFail();

            //$payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'credit',
                'sub_type' => $type,
                'note' => $note,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            //Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'debit',
                'sub_type' => $type,
                'note' => $note,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        } elseif (in_array($type, ['purchase_payment', 'sell_payment'])) {
            $transaction_payment = TransactionPayment::where('id', $id)->where('business_id', $business_id)
                            ->firstorFail();

            //$payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => null,
                'transaction_payment_id' => $id,
                'amount' => $transaction_payment->amount,
                'type' => 'credit',
                'sub_type' => $type,
                'note' => $note,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            //Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => null,
                'transaction_payment_id' => $id,
                'amount' => $transaction_payment->amount,
                'type' => 'debit',
                'sub_type' => $type,
                'note' => $note,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        } elseif ($type == 'purchase') {
            $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstorFail();

            //$payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'credit',
                'sub_type' => $type,
                'note' => $note,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            //Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'debit',
                'sub_type' => $type,
                'note' => $note,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        }elseif ($type == 'expense') {
            $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstorFail();            
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'credit',
                'sub_type' => $type,
                'note' => $note,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'debit',
                'sub_type' => $type,
                'note' => $note,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        }

        AccountingAccountsTransaction::updateOrCreateMapTransaction($payment_data);
        AccountingAccountsTransaction::updateOrCreateMapTransaction($deposit_data);
    }
}
