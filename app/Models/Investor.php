<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    protected $table = 'investors';

    protected $fillable = [
        'name',
        'phone',
        'nid',
        'invest_amount',
        'received_date',
        'invoice_no',
        'received_account_id',
        'return_amount',
        'return_date',
        'remarks',
        'loan_duration',
        'return_account_id'
    ];

    public $timestamps = true;

    public function receivedAccount()
    {
        return $this->belongsTo(\App\Account::class, 'received_account_id');
    }

    public function returnAccount()
    {
        return $this->belongsTo(\App\Account::class, 'return_account_id');
    }
}
