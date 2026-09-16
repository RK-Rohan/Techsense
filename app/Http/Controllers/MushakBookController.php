<?php

namespace App\Http\Controllers;

use App\MushakBook;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MushakBookController extends MushakRegisterController
{
    private function scope(Request $request, $type)
    {
        abort_unless(in_array($type, ['6-1', '6-2'], true), 404);
        $purchase = $type === '6-1';
        $all = auth()->user()->can($purchase ? 'purchase.view' : 'sell.view');
        abort_unless($all || auth()->user()->can($purchase ? 'view_own_purchase' : 'view_own_sell_only'), 403);
        $query = MushakBook::where('business_id', $request->session()->get('user.business_id'))->where('type', $type);
        if (! $all) {
            $query->where('created_by', auth()->id());
        }
        return $query;
    }

    public function index(Request $request, $type)
    {
        $query = $this->scope($request, $type);
        if ($request->ajax()) {
            $request->validate(['start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d']);
            if ($request->filled('start_date')) {
                $query->whereDate('issued_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('issued_at', '<=', $request->end_date);
            }
            return DataTables::of($query)->addColumn('action', function ($book) use ($type) {
                return view('mushak.books.actions', compact('book', 'type'))->render();
            })->rawColumns(['action'])->make(true);
        }
        return view('mushak.books.index', compact('type'));
    }

    public function create(Request $request, $type)
    {
        $this->scope($request, $type);
        $header = $this->headerData($request->session()->get('user.business_id'), null);
        $book = new MushakBook([
            'issued_at' => date('Y-m-d'), 'start_date' => date('Y-m-01'), 'end_date' => date('Y-m-t'),
            'registered_name' => optional($header['business'])->name,
            'seller_address' => $header['seller_address'], 'seller_bin' => $header['seller_bin'], 'rows' => [[]],
        ]);
        return view('mushak.books.form', compact('book', 'type'));
    }

    public function edit(Request $request, $type, $id)
    {
        $book = $this->scope($request, $type)->findOrFail($id);
        return view('mushak.books.form', compact('book', 'type'));
    }

    public function store(Request $request, $type)
    {
        $this->scope($request, $type);
        $book = new MushakBook(['business_id' => $request->session()->get('user.business_id'), 'type' => $type, 'created_by' => auth()->id()]);
        return $this->saveBook($request, $type, $book);
    }

    public function update(Request $request, $type, $id)
    {
        return $this->saveBook($request, $type, $this->scope($request, $type)->findOrFail($id));
    }

    private function saveBook(Request $request, $type, MushakBook $book)
    {
        if ($request->has('rows_json')) {
            $request->validate(['rows_json' => 'required|json']);
            $request->merge(['rows' => json_decode($request->input('rows_json'), true)]);
        }
        $rules = [
            'document_no' => 'required|string|max:191', 'issued_at' => 'required|date_format:Y-m-d',
            'start_date' => 'required|date_format:Y-m-d', 'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'registered_name' => 'required|string|max:191', 'seller_address' => 'nullable|string|max:5000',
            'seller_bin' => 'nullable|string|max:191', 'rows' => 'required|array|min:1|max:200', 'rows.*' => 'required|array',
        ];
        foreach (MushakBook::fields($type) as $field => $kind) {
            $rules['rows.*.'.$field] = $kind === 'number' ? 'nullable|numeric|between:-999999999999,999999999999'
                : ($kind === 'date' ? 'required|date_format:Y-m-d' : 'nullable|string|max:5000');
        }
        $rules['rows.*.date'] .= '|after_or_equal:start_date|before_or_equal:end_date';
        $rules['rows.*.description'] = 'required|string|max:5000';
        $data = $request->validate($rules);
        $data['rows'] = MushakBook::calculateRows($type, $data['rows']);
        $data['total_amount'] = collect($data['rows'])->sum($type === '6-1' ? 'value' : 'taxable_value');
        $data['tax_amount'] = collect($data['rows'])->sum('vat');
        $book->fill($data)->save();
        return redirect()->route($type === '6-1' ? 'mushak.purchaseBook' : 'mushak.salesBook')
            ->with('status', ['success' => 1, 'msg' => 'Mushak '.str_replace('-', '.', $type).' saved successfully.']);
    }

    public function pdf(Request $request, $type, $id)
    {
        $book = $this->scope($request, $type)->findOrFail($id);
        $data = $this->headerData($book->business_id, null);
        $data['business'] = (object) ['name' => $book->registered_name];
        foreach (['seller_address', 'seller_bin', 'start_date', 'end_date'] as $key) {
            $data[$key] = $book->$key;
        }
        $data['rows'] = collect($book->rows);
        return $this->streamPdf('mushak.pdf.mushak_'.str_replace('-', '_', $type), $data, 'Mushak_'.str_replace('-', '.', $type));
    }

    public function destroy(Request $request, $type, $id)
    {
        $this->scope($request, $type)->findOrFail($id)->delete();
        return response()->json(['success' => true, 'msg' => 'Mushak deleted successfully.']);
    }
}
