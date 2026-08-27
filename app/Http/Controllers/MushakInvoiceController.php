<?php

namespace App\Http\Controllers;

use App\MushakInvoice;
use App\Transaction;
use App\User;
use App\Utils\Util;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MushakInvoiceController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Eager loads everything the Mushak 6.3 document needs from a sale.
     */
    private function transactionQuery($business_id)
    {
        return Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->with([
                'business',
                'contact',
                'location',
                'sales_person.roles',
                'tax',
                'sell_lines' => function ($query) {
                    $query->whereNull('parent_sell_line_id')
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
                'sell_lines.product.unit',
                'sell_lines.product.brand',
                'sell_lines.variations',
                'sell_lines.sub_unit',
                'sell_lines.line_tax',
            ]);
    }

    /**
     * Values the Mushak form/PDF falls back to when no override is stored.
     * Mirrors how the document was derived before this module existed, so
     * sales without a saved Mushak keep printing exactly as they used to.
     */
    public function defaultsForTransaction(Transaction $transaction)
    {
        $contact = $transaction->contact;
        $salesPerson = $transaction->sales_person;

        $purchaser_address = $contact
            ? collect($contact->contact_address_array)->filter()->implode(', ')
            : '';

        $destination = trim(strip_tags($transaction->shipping_address(true)
            ? implode(', ', $transaction->shipping_address(true))
            : (string) $transaction->shipping_address));

        return [
            'mushak_invoice_no' => $transaction->custom_field_3 ?: $transaction->invoice_no,
            'issued_at' => $transaction->transaction_date,
            'purchaser_name' => $contact
                ? ($contact->supplier_business_name ?: $contact->name)
                : '',
            'purchaser_bin' => optional($contact)->tax_number,
            'purchaser_address' => $purchaser_address,
            'destination_address' => $destination ?: $purchaser_address,
            'vehicle_details' => $transaction->shipping_details ?: 'Transport',
            'authorised_person' => optional($salesPerson)->user_full_name,
            'designation' => optional($salesPerson)->custom_field_1
                ?: collect(optional($salesPerson)->roles)->pluck('name')->first(),
        ];
    }

    /**
     * List of generated Mushak 6.3 documents.
     */
    public function index()
    {
        abort_unless(auth()->user()->can('print_invoice'), 403, 'Unauthorized action.');

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $mushaks = MushakInvoice::leftJoin('transactions as t', 't.id', '=', 'mushak_invoices.transaction_id')
                ->leftJoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->where('mushak_invoices.business_id', $business_id)
                ->select([
                    'mushak_invoices.id',
                    'mushak_invoices.mushak_invoice_no',
                    'mushak_invoices.issued_at',
                    'mushak_invoices.transaction_id',
                    'mushak_invoices.purchaser_name',
                    't.invoice_no',
                    't.final_total',
                    't.tax_amount',
                    'c.name as contact_name',
                    'c.supplier_business_name',
                ]);

            if (! empty(request()->input('start_date')) && ! empty(request()->input('end_date'))) {
                $mushaks->whereDate('mushak_invoices.issued_at', '>=', request()->input('start_date'))
                    ->whereDate('mushak_invoices.issued_at', '<=', request()->input('end_date'));
            }

            return DataTables::of($mushaks)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown">'
                        . __('messages.actions')
                        . '<span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    $html .= '<li><a href="' . action([\App\Http\Controllers\SellPosController::class, 'downloadMushak63Pdf'], [$row->transaction_id]) . '" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> ' . __('messages.view') . ' PDF</a></li>';
                    $html .= '<li><a href="' . action([\App\Http\Controllers\MushakInvoiceController::class, 'edit'], [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    $html .= '<li><a href="#" class="delete_mushak" data-href="' . action([\App\Http\Controllers\MushakInvoiceController::class, 'destroy'], [$row->id]) . '"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</a></li>';

                    $html .= '</ul></div>';

                    return $html;
                })
                ->editColumn('issued_at', function ($row) {
                    return empty($row->issued_at)
                        ? ''
                        : \Carbon\Carbon::parse($row->issued_at)->format('d-m-Y h:i A');
                })
                ->editColumn('purchaser_name', function ($row) {
                    return $row->purchaser_name
                        ?: ($row->supplier_business_name ?: $row->contact_name);
                })
                ->editColumn('final_total', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->final_total . '</span>';
                })
                ->editColumn('tax_amount', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->tax_amount . '</span>';
                })
                ->rawColumns(['action', 'final_total', 'tax_amount'])
                ->make(true);
        }

        return view('mushak.index');
    }

    /**
     * Form for generating a new Mushak 6.3.
     */
    public function create()
    {
        abort_unless(auth()->user()->can('print_invoice'), 403, 'Unauthorized action.');

        $business_id = request()->session()->get('user.business_id');

        $transaction = null;
        $defaults = null;

        if (! empty(request()->input('transaction_id'))) {
            $transaction = $this->transactionQuery($business_id)->find(request()->input('transaction_id'));

            if (! empty($transaction)) {
                abort_unless(User::can_access_this_location($transaction->location_id, $business_id), 403, 'Unauthorized action.');

                //A sale keeps only one Mushak - editing the existing one keeps
                //the issued number and its history intact.
                $existing = MushakInvoice::where('business_id', $business_id)
                    ->where('transaction_id', $transaction->id)
                    ->first();

                if (! empty($existing)) {
                    return redirect()
                        ->action([\App\Http\Controllers\MushakInvoiceController::class, 'edit'], [$existing->id])
                        ->with('status', [
                            'success' => 1,
                            'msg' => __('lang_v1.mushak_already_exists'),
                        ]);
                }

                $defaults = $this->defaultsForTransaction($transaction);
                $defaults['issued_at'] = empty($defaults['issued_at'])
                    ? ''
                    : $this->commonUtil->format_date($defaults['issued_at'], true);
            }
        }

        return view('mushak.create', compact('transaction', 'defaults'));
    }

    /**
     * Store a generated Mushak 6.3.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('print_invoice'), 403, 'Unauthorized action.');

        $request->validate([
            'transaction_id' => 'required',
            'issued_at' => 'required',
        ]);

        $business_id = $request->session()->get('user.business_id');

        try {
            $transaction = $this->transactionQuery($business_id)->findOrFail($request->input('transaction_id'));

            abort_unless(User::can_access_this_location($transaction->location_id, $business_id), 403, 'Unauthorized action.');

            $existing = MushakInvoice::where('business_id', $business_id)
                ->where('transaction_id', $transaction->id)
                ->first();

            if (! empty($existing)) {
                return redirect()
                    ->action([\App\Http\Controllers\MushakInvoiceController::class, 'edit'], [$existing->id])
                    ->with('status', [
                        'success' => 1,
                        'msg' => __('lang_v1.mushak_already_exists'),
                    ]);
            }

            $input = $this->inputFromRequest($request);
            $input['business_id'] = $business_id;
            $input['transaction_id'] = $transaction->id;
            $input['created_by'] = $request->session()->get('user.id');

            $mushak = MushakInvoice::create($input);

            $this->syncMushakNoToTransaction($transaction, $mushak->mushak_invoice_no);

            $output = ['success' => 1, 'msg' => __('lang_v1.mushak_added_success')];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];

            return back()->with('status', $output)->withInput();
        }

        return redirect()
            ->action([\App\Http\Controllers\MushakInvoiceController::class, 'index'])
            ->with('status', $output);
    }

    /**
     * Form for editing a generated Mushak 6.3.
     */
    public function edit($id)
    {
        abort_unless(auth()->user()->can('print_invoice'), 403, 'Unauthorized action.');

        $business_id = request()->session()->get('user.business_id');

        $mushak = MushakInvoice::where('business_id', $business_id)->findOrFail($id);
        $transaction = $this->transactionQuery($business_id)->findOrFail($mushak->transaction_id);

        abort_unless(User::can_access_this_location($transaction->location_id, $business_id), 403, 'Unauthorized action.');

        $defaults = $this->defaultsForTransaction($transaction);

        $issued_at_formatted = empty($mushak->issued_at)
            ? ''
            : $this->commonUtil->format_date($mushak->issued_at, true);

        return view('mushak.edit', compact('mushak', 'transaction', 'defaults', 'issued_at_formatted'));
    }

    /**
     * Update a generated Mushak 6.3.
     */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->can('print_invoice'), 403, 'Unauthorized action.');

        $request->validate([
            'issued_at' => 'required',
        ]);

        $business_id = $request->session()->get('user.business_id');

        try {
            $mushak = MushakInvoice::where('business_id', $business_id)->findOrFail($id);

            $mushak->fill($this->inputFromRequest($request))->save();

            $transaction = Transaction::where('business_id', $business_id)->find($mushak->transaction_id);
            if (! empty($transaction)) {
                $this->syncMushakNoToTransaction($transaction, $mushak->mushak_invoice_no);
            }

            $output = ['success' => 1, 'msg' => __('lang_v1.mushak_updated_success')];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];

            return back()->with('status', $output)->withInput();
        }

        return redirect()
            ->action([\App\Http\Controllers\MushakInvoiceController::class, 'index'])
            ->with('status', $output);
    }

    /**
     * Soft delete a generated Mushak 6.3, keeping it for audit.
     */
    public function destroy($id)
    {
        abort_unless(auth()->user()->can('print_invoice'), 403, 'Unauthorized action.');

        try {
            $business_id = request()->session()->get('user.business_id');

            MushakInvoice::where('business_id', $business_id)->findOrFail($id)->delete();

            $output = ['success' => true, 'msg' => __('lang_v1.mushak_deleted_success')];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }

    /**
     * Sales available to generate a Mushak for (select2 source).
     */
    public function getTransactions()
    {
        abort_unless(auth()->user()->can('print_invoice'), 403, 'Unauthorized action.');

        $business_id = request()->session()->get('user.business_id');
        $term = request()->input('q', '');

        $sells = Transaction::leftJoin('contacts as c', 'c.id', '=', 'transactions.contact_id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->select([
                'transactions.id',
                'transactions.invoice_no',
                'transactions.transaction_date',
                'transactions.final_total',
                'c.name as contact_name',
                'c.supplier_business_name',
            ]);

        if (! empty($term)) {
            $sells->where(function ($q) use ($term) {
                $q->where('transactions.invoice_no', 'like', "%{$term}%")
                    ->orWhere('c.name', 'like', "%{$term}%")
                    ->orWhere('c.supplier_business_name', 'like', "%{$term}%");
            });
        }

        $sells = $sells->orderBy('transactions.transaction_date', 'desc')->limit(30)->get();

        return $sells->map(function ($sell) {
            return [
                'id' => $sell->id,
                'text' => $sell->invoice_no . ' - ' . ($sell->supplier_business_name ?: $sell->contact_name),
            ];
        });
    }

    /**
     * Defaults for a sale, used to prefill the form after picking an invoice.
     */
    public function getTransactionDefaults($transaction_id)
    {
        abort_unless(auth()->user()->can('print_invoice'), 403, 'Unauthorized action.');

        $business_id = request()->session()->get('user.business_id');
        $transaction = $this->transactionQuery($business_id)->findOrFail($transaction_id);

        abort_unless(User::can_access_this_location($transaction->location_id, $business_id), 403, 'Unauthorized action.');

        $existing = MushakInvoice::where('business_id', $business_id)
            ->where('transaction_id', $transaction->id)
            ->first();

        $defaults = $this->defaultsForTransaction($transaction);
        $defaults['issued_at'] = empty($defaults['issued_at'])
            ? ''
            : $this->commonUtil->format_date($defaults['issued_at'], true);

        return [
            'defaults' => $defaults,
            'existing_id' => optional($existing)->id,
        ];
    }

    /**
     * Keeps the "Mushak No" column on the sales list in step with the
     * number actually printed on the document.
     */
    private function syncMushakNoToTransaction(Transaction $transaction, $mushak_no)
    {
        if (! empty($mushak_no) && $transaction->custom_field_3 !== $mushak_no) {
            $transaction->custom_field_3 = $mushak_no;
            $transaction->save();
        }
    }

    /**
     * Normalises the editable Mushak fields from the request.
     */
    private function inputFromRequest(Request $request)
    {
        $input = $request->only([
            'mushak_invoice_no',
            'purchaser_name',
            'purchaser_bin',
            'purchaser_address',
            'destination_address',
            'vehicle_details',
            'authorised_person',
            'designation',
        ]);

        //Parsed with the business' own date/time format, which the picker uses.
        $issued_at = $request->input('issued_at');
        $input['issued_at'] = empty($issued_at)
            ? null
            : $this->commonUtil->uf_date($issued_at, true);

        return $input;
    }
}
