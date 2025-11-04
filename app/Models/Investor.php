<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    protected $table = 'investors';

    protected $fillable = [
        'name',
        'nid',
        'phone',
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

    public function investments()
    {
        return $this->hasMany(\App\Models\Investment::class);
    }
}
