<?php

namespace App\Utils;

use App\Account;
use App\AccountTransaction;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use Carbon\Carbon;

/**
 * Keeps investment ledger entries and the bank account transactions they post
 * in step. The investment ledger is the source of truth: the bank side is
 * created, updated or removed to match it, never the other way round.
 */
class InvestmentLedger
{
    const NOTE_PREFIXES = [
        InvestmentTransaction::TYPE_INVESTMENT => 'Investment deposit:',
        InvestmentTransaction::TYPE_TOP_UP => 'Investment top-up:',
        InvestmentTransaction::TYPE_RETURN => 'Investment return:',
    ];

    /**
     * Create, update or remove the bank transaction for one entry so it
     * matches the entry's account, amount, date and direction.
     */
    public static function syncBank(InvestmentTransaction $entry, $user_id)
    {
        $at = $entry->account_transaction_id ? AccountTransaction::find($entry->account_transaction_id) : null;
        $amount = (float) $entry->amount;

        if (empty($entry->account_id) || $amount <= 0) {
            if ($at) {
                $at->delete();
            }
            if ($entry->account_transaction_id) {
                $entry->account_transaction_id = null;
                $entry->save();
            }

            return null;
        }

        $values = [
            'account_id' => $entry->account_id,
            'type' => self::expectedType($entry),
            'sub_type' => 'deposit',
            'amount' => $amount,
            'operation_date' => $entry->transaction_date
                ? Carbon::parse($entry->transaction_date)
                : ($at ? $at->operation_date : Carbon::now()),
            'note' => self::noteFor($entry),
        ];

        if ($at) {
            $at->update($values);
        } else {
            $at = AccountTransaction::createAccountTransaction($values + ['created_by' => $user_id]);
            $entry->account_transaction_id = $at->id;
            $entry->save();
        }

        return $at;
    }

    public static function syncAll(Investment $investment, $user_id)
    {
        foreach ($investment->transactions()->get() as $entry) {
            self::syncBank($entry, $user_id);
        }
    }

    /** Remove an entry and the bank transaction it posted. */
    public static function deleteEntry(InvestmentTransaction $entry)
    {
        if ($entry->account_transaction_id) {
            AccountTransaction::where('id', $entry->account_transaction_id)->get()->each->delete();
        }
        $entry->delete();
    }

    /** Investment amount = initial amount + every top-up. */
    public static function recalculate(Investment $investment)
    {
        $investment->amount = InvestmentTransaction::where('investment_id', $investment->id)
            ->whereIn('type', [InvestmentTransaction::TYPE_INVESTMENT, InvestmentTransaction::TYPE_TOP_UP])
            ->sum('amount');
        $investment->save();

        return $investment;
    }

    /**
     * The entry for the original amount. Created on the fly for an investment
     * that somehow has none, so edits always have something to adjust.
     */
    public static function initialEntry(Investment $investment)
    {
        $entry = InvestmentTransaction::where('investment_id', $investment->id)
            ->where('type', InvestmentTransaction::TYPE_INVESTMENT)
            ->orderBy('id')
            ->first();

        if (! $entry) {
            $top_ups = InvestmentTransaction::where('investment_id', $investment->id)
                ->where('type', InvestmentTransaction::TYPE_TOP_UP)
                ->sum('amount');

            $entry = InvestmentTransaction::create([
                'investment_id' => $investment->id,
                'type' => InvestmentTransaction::TYPE_INVESTMENT,
                'amount' => max((float) $investment->amount - (float) $top_ups, 0),
                'transaction_date' => $investment->received_date,
                'account_id' => $investment->received_account_id,
                'created_by' => $investment->created_by,
            ]);
        }

        return $entry;
    }

    /**
     * Why the bank side disagrees with this entry, or null when it matches.
     * Expects accountTransaction to be loaded (trashed rows load as null).
     */
    public static function bankIssue(InvestmentTransaction $entry)
    {
        $at = $entry->accountTransaction;
        $amount = (float) $entry->amount;

        if (empty($entry->account_id) || $amount <= 0) {
            return $at ? 'Bank entry exists but no account is set here' : null;
        }
        if (! $at) {
            return 'Missing in bank account';
        }
        if ((int) $at->account_id !== (int) $entry->account_id) {
            return 'Posted to a different bank account';
        }
        if ($at->type !== self::expectedType($entry)) {
            return 'Posted as '.$at->type.' instead of '.self::expectedType($entry);
        }
        if (abs((float) $at->amount - $amount) >= 0.005) {
            return 'Bank shows '.number_format((float) $at->amount, 2).' instead of '.number_format($amount, 2);
        }
        if ($entry->transaction_date && $at->operation_date
            && $at->operation_date->toDateString() !== Carbon::parse($entry->transaction_date)->toDateString()) {
            return 'Bank date is '.$at->operation_date->toDateString().' instead of '.Carbon::parse($entry->transaction_date)->toDateString();
        }

        return null;
    }

    /**
     * Investment bank transactions on this business's accounts that no ledger
     * entry points to: left behind by deleted investments or by the old
     * Edit Return, which posted a new debit on every save.
     */
    public static function orphanBankTransactions($business_id)
    {
        $account_ids = Account::where('business_id', $business_id)->pluck('id');
        $linked = InvestmentTransaction::whereNotNull('account_transaction_id')->pluck('account_transaction_id');

        return AccountTransaction::with('account')
            ->whereIn('account_id', $account_ids)
            ->whereNotIn('id', $linked)
            ->where(function ($q) {
                foreach (self::NOTE_PREFIXES as $prefix) {
                    $q->orWhere('note', 'like', $prefix.'%');
                }
            })
            ->orderBy('operation_date', 'desc')
            ->get();
    }

    public static function isInvestmentNote($note)
    {
        foreach (self::NOTE_PREFIXES as $prefix) {
            if (strpos((string) $note, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    protected static function expectedType(InvestmentTransaction $entry)
    {
        return $entry->isOutflow() ? 'debit' : 'credit';
    }

    protected static function noteFor(InvestmentTransaction $entry)
    {
        $investment = $entry->investment ?: Investment::find($entry->investment_id);
        $name = optional(optional($investment)->investor)->name;
        $note = self::NOTE_PREFIXES[$entry->type].' '.$name;
        if (! empty($investment->invoice_no)) {
            $note .= ' (Inv '.$investment->invoice_no.')';
        }

        return $note;
    }
}
