<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One money movement on an investment: the initial amount, a top-up added
 * later on the same invoice, or the return paid back. Each one that touches a
 * bank account is linked to the AccountTransaction it posted.
 */
class InvestmentTransaction extends Model
{
    const TYPE_INVESTMENT = 'investment';
    const TYPE_TOP_UP = 'top_up';
    const TYPE_RETURN = 'return';

    protected $table = 'investment_transactions';

    protected $fillable = [
        'investment_id',
        'type',
        'amount',
        'transaction_date',
        'account_id',
        'account_transaction_id',
        'note',
        'created_by',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    public function account()
    {
        return $this->belongsTo(\App\Account::class, 'account_id');
    }

    /**
     * The bank-side entry. Soft-deleted bank entries resolve to null, which
     * the mismatch check reports as missing.
     */
    public function accountTransaction()
    {
        return $this->belongsTo(\App\AccountTransaction::class, 'account_transaction_id');
    }

    /** Money paid out to the investor (debit) rather than received (credit). */
    public function isOutflow()
    {
        return $this->type === self::TYPE_RETURN;
    }

    public function typeLabel()
    {
        return [
            self::TYPE_INVESTMENT => 'Initial Investment',
            self::TYPE_TOP_UP => 'Added Amount',
            self::TYPE_RETURN => 'Return',
        ][$this->type] ?? $this->type;
    }
}
