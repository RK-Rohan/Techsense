<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investor;
use App\Account;
use App\AccountTransaction;
use App\Utils\Util;
use DB;
use PDF;

class InvestorController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        // Accounts for select
        $accounts = \App\Account::where('business_id', $business_id)->NotClosed()->pluck('name', 'id');

        // Invoices for dropdown (sales transactions) - list all
        $invoices = \App\Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->orderBy('transaction_date', 'desc')
            ->pluck('invoice_no', 'invoice_no');

        return view('account.investor')->with(compact('accounts', 'invoices'));
    }

    // AJAX data endpoint for DataTables - fetch from DB
    public function data(Request $request)
    {
        $q = Investor::with(['receivedAccount', 'returnAccount'])->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $q->whereDate('received_date', '>=', $request->input('start_date'))
                ->whereDate('received_date', '<=', $request->input('end_date'));
        }
        if ($request->filled('received_account_id')) {
            $q->where('received_account_id', $request->input('received_account_id'));
        }
        // Payment status filter: paid (return_amount > 0), due (return_amount null or 0)
        if ($request->filled('payment_status')) {
            $status = $request->input('payment_status');
            if ($status === 'paid') {
                $q->whereNotNull('return_amount')->where('return_amount', '>', 0);
            } elseif ($status === 'due') {
                $q->where(function($qq){
                    $qq->whereNull('return_amount')->orWhere('return_amount', 0);
                });
            }
        }

        $investors = $q->get();

        $rows = $investors->map(function($inv){
            $received_date = $inv->received_date ? \Carbon\Carbon::parse($inv->received_date) : null;
            $end_date = $inv->return_date ? \Carbon\Carbon::parse($inv->return_date) : \Carbon\Carbon::now();
            $loan_days = null;
            if ($received_date) {
                $loan_days = $end_date->diffInDays($received_date);
            }

            return [
                'id' => $inv->id,
                'name' => $inv->name,
                'phone' => $inv->phone,
                'nid' => $inv->nid,
                'invest_amount' => $inv->invest_amount,
                'received_date' => $inv->received_date,
                'invoice_no' => $inv->invoice_no,
                'received_account_id' => $inv->received_account_id,
                'received_account_name' => optional($inv->receivedAccount)->name,
                'return_amount' => $inv->return_amount,
                'return_date' => $inv->return_date,
                'return_account_id' => $inv->return_account_id,
                'return_account_name' => optional($inv->returnAccount)->name,
                'remarks' => $inv->remarks,
                'loan_duration_days' => $loan_days,
            ];
        });

        return response()->json([
            'data' => $rows
        ]);
    }

    // Store new investor from AJAX form
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'nullable|string|max:50',
            'nid' => 'nullable|string|max:100',
            'invest_amount' => 'required|numeric',
            'received_date' => 'nullable|date',
            'invoice_no' => 'nullable|string',
            'received_account_id' => 'nullable|integer',
            'return_amount' => 'nullable|numeric',
            'return_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'loan_duration' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $investor = Investor::create($data);

            // If received_account_id provided and amount > 0, create account transaction (credit = deposit)
            if (!empty($data['received_account_id']) && !empty($data['invest_amount'])) {
                $credit_data = [
                    'amount' => $data['invest_amount'],
                    'account_id' => $data['received_account_id'],
                    'type' => 'credit',
                    'sub_type' => 'deposit',
                    'operation_date' => !empty($data['received_date']) ? $data['received_date'] : \Carbon\Carbon::now(),
                    'created_by' => $user_id,
                    'note' => 'Investor deposit: ' . ($data['name'] ?? ''),
                ];

                AccountTransaction::createAccountTransaction($credit_data);
            }

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Investor added', 'data' => $investor]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    // Add return info to an existing investor
    public function addReturn(Request $request, $id)
    {
        $investor = Investor::findOrFail($id);

        $data = $request->validate([
            'return_amount' => 'required|numeric',
            'return_date' => 'required|date',
            'return_account_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $investor->return_amount = $data['return_amount'];
            $investor->return_date = $data['return_date'];
            if (!empty($data['return_account_id'])) {
                $investor->return_account_id = $data['return_account_id'];
            }
            $investor->save();

            // If return_account_id provided and amount > 0, create debit transaction (deduct)
            if (!empty($data['return_account_id']) && !empty($data['return_amount'])) {
                $user_id = $request->session()->get('user.id');
                $debit_data = [
                    'amount' => $data['return_amount'],
                    'account_id' => $data['return_account_id'],
                    'type' => 'debit',
                    'sub_type' => 'deposit',
                    'operation_date' => $data['return_date'],
                    'created_by' => $user_id,
                    'note' => 'Investor return: ' . ($investor->name ?? ''),
                ];

                AccountTransaction::createAccountTransaction($debit_data);
            }

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Return info updated', 'data' => $investor]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    // Update investor general fields
    public function update(Request $request, $id)
    {
        $investor = Investor::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'nullable|string|max:50',
            'nid' => 'nullable|string|max:100',
            'invest_amount' => 'required|numeric',
            'received_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $investor->update($data);

        return response()->json(['success' => true, 'msg' => 'Investor updated', 'data' => $investor]);
    }

    // Delete investor
    public function destroy($id)
    {
        $investor = Investor::findOrFail($id);
        $investor->delete();

        return response()->json(['success' => true, 'msg' => 'Investor deleted']);
    }

    // Generate PDF certificate for an investor
    public function pdf($id)
    {
        $investor = Investor::with(['receivedAccount', 'returnAccount'])->findOrFail($id);

        $pdf = PDF::loadView('account.investor_pdf', [
            'investor' => $investor,
        ])->setPaper('a4');

        return $pdf->stream('investor_certificate_'.$investor->id.'.pdf');
    }
}
