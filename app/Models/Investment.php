<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $table = 'investments';

    protected $fillable = [
        'investor_id',
        'amount',
        'return_amount',
        'return_date',
        'received_date',
        'received_account_id',
        'return_account_id',
        'invoice_no',
        'txn_ref',
        'remarks',
        'business_id',
        'created_by',
    ];

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function receivedAccount()
    {
        return $this->belongsTo(\App\Account::class, 'received_account_id');
    }

    public function returnAccount()
    {
        return $this->belongsTo(\App\Account::class, 'return_account_id');
    }
}
