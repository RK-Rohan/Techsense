<?php

namespace App\Utils;

use App\Transaction;
use App\Models\Investor;
use Illuminate\Support\Facades\Validator;

class SalePurchaseLink
{
    public function query($businessId)
    {
        $user = auth()->user();
        abort_unless($user->can('purchase.view') || $user->can('purchase.create') || $user->can('view_own_purchase'), 403);

        $query = Transaction::where('transactions.business_id', $businessId)
            ->where('transactions.type', 'purchase');
        $locations = $user->permitted_locations();
        if ($locations !== 'all') {
            $query->whereIn('transactions.location_id', $locations);
        }
        if (! $user->can('purchase.view') && $user->can('view_own_purchase')) {
            $query->where('transactions.created_by', $user->id);
        }

        return $query;
    }

    public function fields(Transaction $purchase)
    {
        $names = $purchase->purchase_investors->map(function ($row) {
            return optional($row->investor)->name;
        });
        if ($purchase->purchase_investors->isEmpty() && $purchase->investor_id) {
            $names = collect([optional(Investor::find($purchase->investor_id))->name]);
        }

        return [
            'custom_field_4' => optional($purchase->contact)->name,
            'custom_field_5' => $names->filter()->unique()->implode(', '),
            'custom_field_6' => app(TransactionUtil::class)->num_f($purchase->final_total),
        ];
    }

    public function apply($businessId, array $input)
    {
        if (! array_key_exists('linked_purchase_id', $input)) {
            return $input;
        }
        Validator::make($input, ['linked_purchase_id' => 'nullable|integer|min:1'])->validate();
        $input['linked_purchase_id'] = $input['linked_purchase_id'] ?: null;
        if ($input['linked_purchase_id']) {
            $purchase = $this->query($businessId)->with(['contact', 'purchase_investors.investor'])
                ->findOrFail($input['linked_purchase_id']);
            $fields = $this->fields($purchase);
            Validator::make($fields, [
                'custom_field_4' => 'nullable|string|max:255',
                'custom_field_5' => 'nullable|string|max:255',
                'custom_field_6' => 'nullable|string|max:255',
            ])->validate();
            $input = array_replace($input, $fields);
        }

        return $input;
    }
}
