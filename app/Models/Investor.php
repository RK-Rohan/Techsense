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
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
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

    /**
     * Portal login account for this investor, if one has been created.
     */
    public function user()
    {
        return $this->hasOne(\App\User::class, 'investor_id');
    }
}
