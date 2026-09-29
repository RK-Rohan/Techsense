<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvestor extends Model
{
    protected $table = 'purchase_investors';

    protected $fillable = [
        'business_id',
        'transaction_id',
        'investor_id',
        'amount',
    ];

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class);
    }
}
