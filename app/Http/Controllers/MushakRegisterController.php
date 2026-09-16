<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Transaction;
use App\Utils\Util;
use Illuminate\Http\Request;
use PDF;

/** Shared register rendering and legacy transaction calculations. */
class MushakRegisterController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    public function purchaseBook(Request $request)
    {
        return app(MushakBookController::class)->index($request, '6-1');
    }

    public function salesBook(Request $request)
    {
        return app(MushakBookController::class)->index($request, '6-2');
    }
    /**
     * Builds the Mushak 6.1 rows.
     *
     * Columns (3)/(4) are the opening stock of inputs, which is the closing
     * balance carried from the previous row; the first row opens with whatever
     * was on hand immediately before the range started. Columns (5)(6) - use of
     * inputs in production - stay blank for a trading business, so the closing
     * balance is simply opening + purchased.
     */
    private function buildPurchaseBook($business_id, Request $request)
    {
        [$start_date, $end_date] = $this->dateRange($request);
        $location_id = $request->input('location_id');

        $query = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'purchase')
            ->where('transactions.status', 'received')
            ->with([
                'contact',
                'purchase_lines' => function ($q) {
                    $q->orderBy('id');
                },
                'purchase_lines.product.unit',
                'purchase_lines.product.brand',
                'purchase_lines.variations',
            ]);

        if (! empty($start_date) && ! empty($end_date)) {
            $query->whereDate('transactions.transaction_date', '>=', $start_date)
                ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $transactions = $query->orderBy('transactions.transaction_date')
            ->orderBy('transactions.id')
            ->get();

        //Stock on hand before the range opens the book.
        $opening = $this->openingStock($business_id, 'purchase', $start_date, $location_id);
        $running_qty = $opening['quantity'];
        $running_value = $opening['value'];

        $rows = [];
        $serial = 0;

        foreach ($transactions as $transaction) {
            $contact = $transaction->contact;
            $supplier_address = $contact
                ? collect($contact->contact_address_array)->filter()->implode(', ')
                : '';

            foreach ($transaction->purchase_lines as $line) {
                $serial++;

                $quantity = (float) $line->quantity;
                $unit_price = (float) $line->purchase_price;
                $value = $quantity * $unit_price;
                $vat = (float) $line->item_tax * $quantity;

                $opening_qty = $running_qty;
                $opening_value = $running_value;

                //(15) = (3+11) and (16) = (4+12)
                $total_qty = $opening_qty + $quantity;
                $total_value = $opening_value + $value;

                //Trading business: nothing is consumed in production.
                $used_qty = 0;
                $used_value = 0;

                $running_qty = $total_qty - $used_qty;
                $running_value = $total_value - $used_value;

                $rows[] = [
                    'serial' => $serial,
                    'date' => $transaction->transaction_date,
                    'opening_qty' => $opening_qty,
                    'opening_value' => $opening_value,
                    'challan_no' => $transaction->ref_no,
                    'challan_date' => $transaction->transaction_date,
                    'supplier_name' => $contact
                        ? ($contact->supplier_business_name ?: $contact->name)
                        : '',
                    'supplier_address' => $supplier_address,
                    'supplier_bin' => optional($contact)->tax_number,
                    'description' => $this->lineDescription($line),
                    'quantity' => $quantity,
                    'value' => $value,
                    'sd_amount' => 0,
                    'vat' => $vat,
                    'total_qty' => $total_qty,
                    'total_value' => $total_value,
                    'used_qty' => $used_qty,
                    'used_value' => $used_value,
                    'closing_qty' => $running_qty,
                    'closing_value' => $running_value,
                    'unit' => $this->lineUnit($line),
                    'remarks' => '',
                ];
            }
        }

        return array_merge(
            $this->headerData($business_id, $location_id),
            [
                'rows' => collect($rows),
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]
        );
    }

    /**
     * Builds the Mushak 6.2 rows.
     *
     * Columns (3)-(6) cover produced goods. For a trading business nothing is
     * produced, so the opening balance is the stock on hand and production is
     * zero; (7)=(3+5) and (8)=(4+6) therefore carry the opening through, and
     * the closing balance (19)=(7-11) / (20)=(8-16) falls by what was sold.
     */
    private function buildSalesBook($business_id, Request $request)
    {
        [$start_date, $end_date] = $this->dateRange($request);
        $location_id = $request->input('location_id');

        $query = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->with([
                'contact',
                'tax',
                'sell_lines' => function ($q) {
                    $q->whereNull('parent_sell_line_id')
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
                'sell_lines.product.unit',
                'sell_lines.product.brand',
                'sell_lines.variations',
                'sell_lines.sub_unit',
                'sell_lines.line_tax',
            ]);

        if (! empty($start_date) && ! empty($end_date)) {
            $query->whereDate('transactions.transaction_date', '>=', $start_date)
                ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $transactions = $query->orderBy('transactions.transaction_date')
            ->orderBy('transactions.id')
            ->get();

        $opening = $this->openingStock($business_id, 'sell', $start_date, $location_id);
        $running_qty = $opening['quantity'];
        $running_value = $opening['value'];

        $rows = [];

        foreach ($transactions as $transaction) {
            $contact = $transaction->contact;
            $buyer_address = $contact
                ? collect($contact->contact_address_array)->filter()->implode(', ')
                : '';

            //Invoice level VAT is spread across the lines so each row of the
            //book carries its own auditable VAT figure, same as the 6.3 does.
            $has_order_vat = ! empty($transaction->tax_id) && (float) $transaction->tax_amount !== 0.0;
            $line_base_total = $transaction->sell_lines->sum(function ($line) use ($has_order_vat) {
                $unit_price = $has_order_vat ? $line->unit_price_inc_tax : $line->unit_price;

                return (float) $unit_price * (float) $line->quantity;
            });

            foreach ($transaction->sell_lines as $line) {
                $quantity = (float) $line->quantity;
                $unit_price = (float) ($has_order_vat ? $line->unit_price_inc_tax : $line->unit_price);
                $value = $quantity * $unit_price;

                $line_vat = $has_order_vat ? 0 : (float) $line->item_tax * $quantity;
                $allocated_order_vat = $has_order_vat && $line_base_total > 0
                    ? ((float) $transaction->tax_amount * ($value / $line_base_total))
                    : 0;
                $vat = $line_vat + $allocated_order_vat;

                $opening_qty = $running_qty;
                $opening_value = $running_value;

                //Trading business: no production, so (7)=(3+5) carries opening.
                $produced_qty = 0;
                $produced_value = 0;
                $total_qty = $opening_qty + $produced_qty;
                $total_value = $opening_value + $produced_value;

                //(19) = (7-11) and (20) = (8-16)
                $running_qty = $total_qty - $quantity;
                $running_value = $total_value - $value;

                $rows[] = [
                    'date' => $transaction->transaction_date,
                    'opening_qty' => $opening_qty,
                    'opening_value' => $opening_value,
                    'produced_qty' => $produced_qty,
                    'produced_value' => $produced_value,
                    'total_qty' => $total_qty,
                    'total_value' => $total_value,
                    'buyer_name' => $contact
                        ? ($contact->supplier_business_name ?: $contact->name)
                        : '',
                    'buyer_address' => $buyer_address,
                    'buyer_bin' => optional($contact)->tax_number,
                    'challan_no' => $transaction->custom_field_3 ?: $transaction->invoice_no,
                    'challan_date' => $transaction->transaction_date,
                    'description' => $this->lineDescription($line),
                    'quantity' => $quantity,
                    'taxable_value' => $value,
                    'sd_amount' => 0,
                    'vat' => $vat,
                    'closing_qty' => $running_qty,
                    'closing_value' => $running_value,
                    'unit' => $this->lineUnit($line),
                    'remarks' => '',
                ];
            }
        }

        return array_merge(
            $this->headerData($business_id, $location_id),
            [
                'rows' => collect($rows),
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]
        );
    }

    /**
     * Quantity and value on hand immediately before the book opens, so the
     * first row's opening balance continues from the previous period.
     */
    private function openingStock($business_id, $type, $start_date, $location_id)
    {
        if (empty($start_date)) {
            return ['quantity' => 0, 'value' => 0];
        }

        $purchased = Transaction::join('purchase_lines as pl', 'pl.transaction_id', '=', 'transactions.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'purchase')
            ->where('transactions.status', 'received')
            ->whereDate('transactions.transaction_date', '<', $start_date);

        if (! empty($location_id)) {
            $purchased->where('transactions.location_id', $location_id);
        }

        $purchased = $purchased->selectRaw('SUM(pl.quantity) as qty, SUM(pl.quantity * pl.purchase_price) as value')->first();

        $sold = Transaction::join('transaction_sell_lines as sl', 'sl.transaction_id', '=', 'transactions.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->whereNull('sl.parent_sell_line_id')
            ->whereDate('transactions.transaction_date', '<', $start_date);

        if (! empty($location_id)) {
            $sold->where('transactions.location_id', $location_id);
        }

        $sold = $sold->selectRaw('SUM(sl.quantity) as qty, SUM(sl.quantity * sl.unit_price) as value')->first();

        //The purchase book tracks inputs received; the sales book tracks goods
        //available to supply. Both net off what has already left stock.
        $quantity = (float) optional($purchased)->qty - (float) optional($sold)->qty;
        $value = (float) optional($purchased)->value - (float) optional($sold)->value;

        return [
            'quantity' => $quantity,
            'value' => $value,
        ];
    }

    /**
     * Registered person details printed in both book headers.
     */
    protected function headerData($business_id, $location_id)
    {
        $business = \App\Business::find($business_id);

        $location = ! empty($location_id)
            ? BusinessLocation::where('business_id', $business_id)->find($location_id)
            : BusinessLocation::where('business_id', $business_id)->first();

        $seller_address = collect([
            optional($location)->landmark,
            optional($location)->city,
            optional($location)->state,
            optional($location)->country,
            optional($location)->zip_code,
        ])->filter()->implode(', ');

        $is_techsense = \Illuminate\Support\Str::contains(
            \Illuminate\Support\Str::lower((string) optional($business)->name),
            'techsense'
        );

        $seller_bin = optional($business)->tax_number_1
            ?: optional($business)->tax_number_2
            ?: ($is_techsense ? config('constants.mushak_registered_bin') : null);

        if ($is_techsense && config('constants.mushak_registered_address')) {
            $seller_address = config('constants.mushak_registered_address');
        }

        $government_seal_path = public_path('img/bangladesh-government-seal.png');

        return [
            'business' => $business,
            'seller_bin' => $seller_bin,
            'seller_address' => $seller_address,
            'government_seal' => file_exists($government_seal_path)
                ? base64_encode(file_get_contents($government_seal_path))
                : null,
        ];
    }

    /**
     * Product description including brand and sub-sku, matching the 6.3.
     */
    private function lineDescription($line)
    {
        $description = $line->product ? $line->product->name : '';

        if ($line->product && $line->product->brand) {
            $description .= ' (' . $line->product->brand->name . ')';
        }

        if ($line->variations && ! empty($line->variations->sub_sku)) {
            $description .= "\n" . $line->variations->sub_sku;
        }

        return $description;
    }

    private function lineUnit($line)
    {
        return optional($line->sub_unit ?? null)->short_name
            ?: optional(optional($line->product)->unit)->short_name;
    }

    /**
     * Resolves the filter date range into Y-m-d bounds.
     */
    private function dateRange(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        //Default to the current month so the book always opens with a period.
        if (empty($start_date) || empty($end_date)) {
            $start_date = \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
            $end_date = \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        return [$start_date, $end_date];
    }

    /**
     * Streams a book as a landscape A4 PDF, matching the NBR layout.
     */
    protected function streamPdf($view, array $data, $prefix)
    {
        $pdf = PDF::loadView($view, $data)->setPaper('a4', 'landscape');

        $filename = $prefix . '_' . $data['start_date'] . '_to_' . $data['end_date'] . '.pdf';

        return $pdf->stream($filename);
    }
}
