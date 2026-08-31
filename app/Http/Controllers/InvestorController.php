<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investor;
use App\Account;
use App\AccountTransaction;
use App\Utils\Util;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;
use PDF;

class InvestorController extends Controller
{
    // Investor master list (name, nid, phone)
    public function masterIndex()
    {
        return view('account.investor_master');
    }

    public function masterData(Request $request)
    {
        $investors = Investor::with('user')->orderBy('name')
            ->get(['id','name','nid','phone','address','emergency_contact_name','emergency_contact_number']);

        $rows = $investors->map(function($inv){
            $total_investment = \App\Models\Investment::where('investor_id', $inv->id)->sum('amount');
            $total_pay = \App\Models\Investment::where('investor_id', $inv->id)->sum('return_amount');
            $diff = ($total_investment ?: 0) - ($total_pay ?: 0);
            $total_due = $diff > 0 ? $diff : 0; // never negative
            $profit = $diff < 0 ? abs($diff) : 0; // overflow treated as profit

            return [
                'id' => $inv->id,
                'name' => $inv->name,
                'nid' => $inv->nid,
                'phone' => $inv->phone,
                'address' => $inv->address,
                'emergency_contact_name' => $inv->emergency_contact_name,
                'emergency_contact_number' => $inv->emergency_contact_number,
                'total_investment' => (float)$total_investment,
                'total_pay' => (float)$total_pay,
                'total_due' => (float)$total_due,
                'profit' => (float)$profit,
                'has_login' => ! empty($inv->user),
                'login_username' => optional($inv->user)->username,
                'login_active' => optional($inv->user)->allow_login ? 1 : 0,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function masterStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'nid' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:191',
            'emergency_contact_number' => 'nullable|string|max:50',
        ]);

        $inv = Investor::create($data);
        return response()->json(['success' => true, 'msg' => 'Investor added', 'data' => $inv]);
    }

    public function masterUpdate(Request $request, $id)
    {
        $inv = Investor::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'nid' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:191',
            'emergency_contact_number' => 'nullable|string|max:50',
        ]);
        $inv->update($data);
        return response()->json(['success' => true, 'msg' => 'Investor updated', 'data' => $inv]);
    }

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
                'txn_ref' => $inv->txn_ref,
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
            'txn_ref' => 'nullable|string|max:191',
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
            'txn_ref' => 'nullable|string|max:191',
            'invest_amount' => 'required|numeric',
            'received_date' => 'nullable|date',
            'invoice_no' => 'nullable|string',
            'received_account_id' => 'nullable|integer',
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

    // Fetch the portal login state for an investor (for the Manage Login modal)
    public function getLogin($id)
    {
        $investor = Investor::with('user')->findOrFail($id);
        $user = $investor->user;

        return response()->json([
            'success' => true,
            'data' => [
                'investor_id' => $investor->id,
                'investor_name' => $investor->name,
                'has_login' => ! empty($user),
                'username' => optional($user)->username,
                'allow_login' => optional($user)->allow_login ? 1 : 0,
                'status' => optional($user)->status,
            ],
        ]);
    }

    // Create or update the portal login for an investor
    public function saveLogin(Request $request, $id)
    {
        $investor = Investor::with('user')->findOrFail($id);
        $user = $investor->user;

        $data = $request->validate([
            'username' => [
                'required', 'string', 'min:4', 'max:191',
                Rule::unique('users', 'username')->ignore(optional($user)->id),
            ],
            // Password is required only when creating the account; on edit an
            // empty value leaves the existing password untouched.
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'max:191'],
            'allow_login' => 'nullable',
        ]);

        $allow_login = ! empty($data['allow_login']) ? 1 : 0;

        DB::beginTransaction();
        try {
            $business_id = $request->session()->get('user.business_id');

            if (empty($user)) {
                $user = new User;
                $user->business_id = $business_id;
                $user->user_type = 'investor';
                $user->investor_id = $investor->id;
                $user->surname = '';
                $user->first_name = $investor->name;
                $user->last_name = '';
                $user->contact_number = $investor->phone;
                $user->language = 'en';
            }

            $user->username = $data['username'];
            $user->allow_login = $allow_login;
            $user->status = $allow_login ? 'active' : 'inactive';

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Investor login saved',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    // Remove the portal login for an investor
    public function deleteLogin($id)
    {
        $investor = Investor::with('user')->findOrFail($id);

        if (! empty($investor->user)) {
            $investor->user->forceDelete();
        }

        return response()->json(['success' => true, 'msg' => 'Investor login removed']);
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
