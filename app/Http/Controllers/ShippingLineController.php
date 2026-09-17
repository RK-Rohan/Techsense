<?php

namespace App\Http\Controllers;

use App\ShippingLine;
use Illuminate\Http\Request;

/**
 * Manages the shipping line lookup used by the purchase form. Lines are added
 * inline from that form, so this only needs to create and list them.
 */
class ShippingLineController extends Controller
{
    /**
     * Stores a shipping line and returns it for the select to pick up.
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate(['name' => 'required|string|max:191']);

        $business_id = $request->session()->get('user.business_id');
        $name = trim($request->input('name'));

        try {
            // Reuse a line of the same name rather than creating a duplicate.
            $shipping_line = ShippingLine::where('business_id', $business_id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->first();

            if (empty($shipping_line)) {
                $shipping_line = ShippingLine::create([
                    'business_id' => $business_id,
                    'name' => $name,
                    'created_by' => $request->session()->get('user.id'),
                ]);
            }

            $output = [
                'success' => true,
                'id' => $shipping_line->id,
                'name' => $shipping_line->name,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
