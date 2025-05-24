<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsCondition extends Model
{
    use HasFactory;

    protected $fillable = ['description'];

    protected $casts = [
        'tc_line' => 'json',
    ];

    public function tcTransactions()
    {
        return $this->hasMany(TcTransaction::class, 'tc_id');
    }
}
