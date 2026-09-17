<?php

namespace App\Utils;

use Illuminate\Validation\Rule;

class ExpenseItems
{
    public static function fromRequest($request, $businessId)
    {
        if (! $request->has('expense_items_json')) {
            return [];
        }
        $request->validate(['expense_items_json' => 'required|json']);
        $request->merge(['expense_items' => json_decode($request->expense_items_json, true)]);
        $data = $request->validate([
            'expense_category_id' => ['required', Rule::exists('expense_categories', 'id')->where('business_id', $businessId)->whereNull('parent_id')->whereNull('deleted_at')],
            'expense_items' => 'required|array|min:1|max:200',
            'expense_items.*.subcategory_id' => ['required', 'integer', 'distinct', Rule::exists('expense_categories', 'id')->where('business_id', $businessId)->where('parent_id', $request->expense_category_id)->whereNull('deleted_at')],
            'expense_items.*.note' => 'nullable|string|max:5000',
            'expense_items.*.amount' => 'required|numeric|min:0.01|max:999999999',
        ]);
        $items = array_map(function ($item) {
            return ['subcategory_id' => (int) $item['subcategory_id'], 'note' => $item['note'] ?? '', 'amount' => round((float) $item['amount'], 4)];
        }, $data['expense_items']);
        $ids = array_column($items, 'subcategory_id');
        return ['expense_items' => $items, 'expense_sub_category_id' => $ids[0],
            'expense_sub_category_ids' => $ids, 'final_total' => array_sum(array_column($items, 'amount'))];
    }
}
