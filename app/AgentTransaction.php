<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'customer_id',
        'sales_id',
        'supplier_id',
        'invoice_no',
        'cnf_qty',
        'cnf_rate',
        'cnf_amount',
        'supplier_amount',
        'number_of_cartons',
        'tracking_no',
        'payment_status',
        'shipping_status',
    ];

    // Define the relationship with Agent model
    public function agent()
    {
        return $this->belongsTo(Contact::class, 'agent_id');
    }

    // Define the relationship with Customer model
    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id');
    }

    // Define the relationship with Sales model
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'sales_id');
    }
}
