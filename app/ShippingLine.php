<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingLine extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Shipping lines of a business, ready for a select dropdown.
     */
    public static function forDropdown($business_id)
    {
        return ShippingLine::where('business_id', $business_id)
            ->orderBy('name')
            ->pluck('name', 'id');
    }
}
