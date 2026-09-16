<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MushakBook extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $casts = ['rows' => 'array'];

    public static function fields($type)
    {
        $party = $type === '6-1' ? 'supplier' : 'buyer';
        return [
            'date' => 'date', 'opening_qty' => 'number', 'opening_value' => 'number',
            'challan_no' => 'text', 'challan_date' => 'date',
            $party.'_name' => 'text', $party.'_address' => 'text', $party.'_bin' => 'text',
            'description' => 'text', 'unit' => 'text', 'quantity' => 'number',
            ($type === '6-1' ? 'value' : 'taxable_value') => 'number',
            'sd_amount' => 'number', 'vat' => 'number',
        ] + ($type === '6-1'
            ? ['used_qty' => 'number', 'used_value' => 'number']
            : ['produced_qty' => 'number', 'produced_value' => 'number'])
            + ['remarks' => 'text'];
    }

    public static function calculateRows($type, array $rows)
    {
        $result = [];
        foreach (array_values($rows) as $index => $row) {
            $clean = [];
            foreach (static::fields($type) as $key => $kind) {
                $clean[$key] = $kind === 'number' ? (float) ($row[$key] ?? 0) : ($row[$key] ?? '');
            }
            $purchase = $type === '6-1';
            $clean['serial'] = $index + 1;
            $clean['total_qty'] = $clean['opening_qty'] + $clean[$purchase ? 'quantity' : 'produced_qty'];
            $clean['total_value'] = $clean['opening_value'] + $clean[$purchase ? 'value' : 'produced_value'];
            $clean['closing_qty'] = $clean['total_qty'] - $clean[$purchase ? 'used_qty' : 'quantity'];
            $clean['closing_value'] = $clean['total_value'] - $clean[$purchase ? 'used_value' : 'taxable_value'];
            $result[] = $clean;
        }
        return $result;
    }
}
