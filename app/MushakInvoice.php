<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MushakInvoice extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = ['issued_at', 'deleted_at'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
