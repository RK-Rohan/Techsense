<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\Investor;
use App\Account;
use App\AccountTransaction;
use DB;
use PDF;

class InvestmentController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $investors = Investor::orderBy('name')->pluck('name', 'id');
        $accounts = Account::where('business_id', $business_id)->NotClosed()->pluck('name', 'id');
        // Invoices for dropdown (sales transactions) - list all
        $invoices = \App\Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->orderBy('transaction_date', 'desc')
            ->pluck('invoice_no', 'invoice_no');

        return view('account.investments', compact('investors', 'accounts', 'invoices'));
    }

    // AJAX data endpoint for DataTables
    public function data(Request $request)
    {
        $q = Investment::with(['investor', 'receivedAccount'])->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $q->whereDate('received_date', '>=', $request->input('start_date'))
              ->whereDate('received_date', '<=', $request->input('end_date'));
        }
        if ($request->filled('received_account_id')) {
            $q->where('received_account_id', $request->input('received_account_id'));
        }

        $investments = $q->get();

        $rows = $investments->map(function($inv){
            $received_date = $inv->received_date ? \Carbon\Carbon::parse($inv->received_date) : null;
            $end_date = \Carbon\Carbon::now();
            $loan_days = null;
            if ($received_date) {
                $loan_days = $end_date->diffInDays($received_date);
            }

            return [
                'id' => $inv->id,
                'investor_id' => $inv->investor_id,
                'received_date' => $inv->received_date,
                'investor_name' => optional($inv->investor)->name,
                'invoice_no' => $inv->invoice_no,
                'txn_ref' => $inv->txn_ref,
                'amount' => $inv->amount,
                'received_account_id' => $inv->received_account_id,
                'received_account_name' => optional($inv->receivedAccount)->name,
                'return_amount' => $inv->return_amount,
                'return_date' => $inv->return_date,
                'return_account_id' => $inv->return_account_id,
                'remarks' => $inv->remarks,
                'loan_duration_days' => $loan_days,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'investor_id' => 'required|integer|exists:investors,id',
            'amount' => 'required|numeric',
            'received_date' => 'nullable|date',
            'received_account_id' => 'nullable|integer',
            'invoice_no' => 'nullable|string',
            'txn_ref' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $data['business_id'] = $business_id;
            $data['created_by'] = $user_id;

            $investment = Investment::create($data);

            if (!empty($data['received_account_id']) && !empty($data['amount'])) {
                $inv = Investor::find($data['investor_id']);
                $credit_data = [
                    'amount' => $data['amount'],
                    'account_id' => $data['received_account_id'],
                    'type' => 'credit',
                    'sub_type' => 'deposit',
                    'operation_date' => !empty($data['received_date']) ? $data['received_date'] : \Carbon\Carbon::now(),
                    'created_by' => $user_id,
                    'note' => 'Investment deposit: ' . ($inv->name ?? ''),
                ];
                AccountTransaction::createAccountTransaction($credit_data);
            }

            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Investment added', 'data' => $investment]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    // Update an existing investment (AJAX)
    public function update(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $data = $request->validate([
            'investor_id' => 'required|integer|exists:investors,id',
            'amount' => 'required|numeric',
            'received_date' => 'nullable|date',
            'received_account_id' => 'nullable|integer',
            'invoice_no' => 'nullable|string',
            'txn_ref' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $investment->update($data);

        return response()->json(['success' => true, 'msg' => 'Investment updated', 'data' => $investment]);
    }

    // Delete investment (AJAX)
    public function destroy($id)
    {
        $investment = Investment::findOrFail($id);
        $investment->delete();
        return response()->json(['success' => true, 'msg' => 'Investment deleted']);
    }

    // PDF slip
    public function pdf($id)
    {
        $investment = Investment::with(['investor', 'receivedAccount'])->findOrFail($id);
        $total_investor_amount = Investment::where('investor_id', $investment->investor_id)->sum('amount');
        $pdf = PDF::loadView('account.investment_pdf', [
            'investment' => $investment,
            'investor' => $investment->investor,
            'total_investor_amount' => $total_investor_amount,
        ])->setPaper('a4');
        return $pdf->stream('investment_'.$investment->id.'.pdf');
    }

    // Add or edit return info for an investment
    public function addReturn(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $data = $request->validate([
            'return_amount' => 'required|numeric',
            'return_date' => 'required|date',
            'return_account_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $investment->return_amount = $data['return_amount'];
            $investment->return_date = $data['return_date'];
            if (!empty($data['return_account_id'])) {
                $investment->return_account_id = $data['return_account_id'];
            }
            $investment->save();

            if (!empty($data['return_account_id']) && !empty($data['return_amount'])) {
                $user_id = $request->session()->get('user.id');
                $debit_data = [
                    'amount' => $data['return_amount'],
                    'account_id' => $data['return_account_id'],
                    'type' => 'debit',
                    'sub_type' => 'deposit',
                    'operation_date' => $data['return_date'],
                    'created_by' => $user_id,
                    'note' => 'Investment return: ' . (optional($investment->investor)->name ?? ''),
                ];
                AccountTransaction::createAccountTransaction($debit_data);
            }

            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Return info updated', 'data' => $investment]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }
}
