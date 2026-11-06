<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\Investor;
use App\Account;
use App\AccountTransaction;
use App\Utils\InvestmentLedger;
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

        // Pre-selected when opened from the investor master list
        $selected_investor_id = request()->input('investor_id');
        $selected_investor_name = $selected_investor_id ? ($investors[$selected_investor_id] ?? null) : null;

        return view('account.investments', compact('investors', 'accounts', 'invoices', 'selected_investor_id', 'selected_investor_name'));
    }

    // AJAX data endpoint for DataTables
    public function data(Request $request)
    {
        $q = Investment::with(['investor', 'receivedAccount', 'transactions.accountTransaction'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $q->whereDate('received_date', '>=', $request->input('start_date'))
              ->whereDate('received_date', '<=', $request->input('end_date'));
        }
        if ($request->filled('received_account_id')) {
            $q->where('received_account_id', $request->input('received_account_id'));
        }
        if ($request->filled('investor_id')) {
            $q->where('investor_id', $request->input('investor_id'));
        }
        // Same rule as the Payment Status column: paid once any return is recorded
        if ($request->filled('payment_status')) {
            if ($request->input('payment_status') === 'paid') {
                $q->where('return_amount', '>', 0);
            } elseif ($request->input('payment_status') === 'due') {
                $q->where(function ($qq) {
                    $qq->whereNull('return_amount')->orWhere('return_amount', '<=', 0);
                });
            }
        }

        $investments = $q->get();

        $rows = $investments->map(function($inv){
            $received_date = $inv->received_date ? \Carbon\Carbon::parse($inv->received_date) : null;
            $end_date = \Carbon\Carbon::now();
            $loan_days = null;
            if ($received_date) {
                $loan_days = $end_date->diffInDays($received_date);
            }

            // Same rule as the investor portal: profit and ROI only mean
            // something once a payout has been made.
            $amount = (float) $inv->amount;
            $returned = (float) $inv->return_amount;
            $profit = $returned > 0 ? max($returned - $amount, 0) : null;
            $roi = $profit !== null && $amount > 0 ? $profit / $amount * 100 : null;

            $entries = $inv->transactions;
            $top_ups = $entries->where('type', InvestmentTransaction::TYPE_TOP_UP);
            $initial = $entries->firstWhere('type', InvestmentTransaction::TYPE_INVESTMENT);

            $bank_issues = $entries->map(function ($e) {
                $issue = InvestmentLedger::bankIssue($e);

                return $issue ? $e->typeLabel().': '.$issue : null;
            })->filter()->values();

            return [
                'id' => $inv->id,
                'investor_id' => $inv->investor_id,
                'received_date' => $inv->received_date,
                'investor_name' => optional($inv->investor)->name,
                'invoice_no' => $inv->invoice_no,
                'txn_ref' => $inv->txn_ref,
                'amount' => $inv->amount,
                'initial_amount' => $initial ? (float) $initial->amount : $amount - (float) $top_ups->sum('amount'),
                'top_up_total' => (float) $top_ups->sum('amount'),
                'top_up_count' => $top_ups->count(),
                'received_account_id' => $inv->received_account_id,
                'received_account_name' => optional($inv->receivedAccount)->name,
                'return_amount' => $inv->return_amount,
                'profit' => $profit,
                'roi' => $roi,
                'return_date' => $inv->return_date,
                'return_account_id' => $inv->return_account_id,
                'remarks' => $inv->remarks,
                'loan_duration_days' => $loan_days,
                'bank_issues' => $bank_issues,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'investor_id' => 'required|integer|exists:investors,id',
            'amount' => 'required|numeric|gt:0',
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

            $entry = InvestmentTransaction::create([
                'investment_id' => $investment->id,
                'type' => InvestmentTransaction::TYPE_INVESTMENT,
                'amount' => $data['amount'],
                'transaction_date' => $data['received_date'] ?? null,
                'account_id' => $data['received_account_id'] ?? null,
                'created_by' => $user_id,
            ]);
            InvestmentLedger::syncBank($entry, $user_id);

            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Investment added', 'data' => $investment]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    /**
     * Update an existing investment. "amount" here is the initial amount;
     * top-ups are added on top of it. The bank transactions are brought in
     * line with the new values instead of being left behind.
     */
    public function update(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $data = $request->validate([
            'investor_id' => 'required|integer|exists:investors,id',
            'amount' => 'required|numeric|gt:0',
            'received_date' => 'nullable|date',
            'received_account_id' => 'nullable|integer',
            'invoice_no' => 'nullable|string',
            'txn_ref' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user_id = $request->session()->get('user.id');

            $initial = InvestmentLedger::initialEntry($investment);
            $initial->update([
                'amount' => $data['amount'],
                'transaction_date' => $data['received_date'] ?? null,
                'account_id' => $data['received_account_id'] ?? null,
            ]);

            unset($data['amount']);
            $investment->update($data);
            InvestmentLedger::recalculate($investment);

            // Re-sync every entry: refreshes notes if the investor or invoice
            // changed, and repairs any drift left from before entries were linked.
            InvestmentLedger::syncAll($investment, $user_id);

            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Investment updated', 'data' => $investment]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    /** Add more money to an existing investment (same invoice). */
    public function addAmount(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $data = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'transaction_date' => 'required|date',
            'account_id' => 'nullable|integer',
            'note' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $user_id = $request->session()->get('user.id');

            // Make sure the original amount is on the ledger before adding to it
            InvestmentLedger::initialEntry($investment);

            $entry = InvestmentTransaction::create([
                'investment_id' => $investment->id,
                'type' => InvestmentTransaction::TYPE_TOP_UP,
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'account_id' => $data['account_id'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $user_id,
            ]);
            InvestmentLedger::syncBank($entry, $user_id);
            InvestmentLedger::recalculate($investment);

            DB::commit();
            return response()->json([
                'success' => true,
                'msg' => 'Amount added. New total: '.number_format((float) $investment->amount, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    /** Ledger entries for one investment with their bank status. */
    public function history($id)
    {
        $investment = Investment::with(['investor', 'transactions.account', 'transactions.accountTransaction'])->findOrFail($id);

        $rows = $investment->transactions->map(function ($e) {
            return [
                'id' => $e->id,
                'type' => $e->type,
                'type_label' => $e->typeLabel(),
                'transaction_date' => $e->transaction_date,
                'amount' => (float) $e->amount,
                'account_name' => optional($e->account)->name,
                'note' => $e->note,
                'bank_issue' => InvestmentLedger::bankIssue($e),
            ];
        });

        return response()->json([
            'success' => true,
            'investor_name' => optional($investment->investor)->name,
            'invoice_no' => $investment->invoice_no,
            'total' => (float) $investment->amount,
            'data' => $rows,
        ]);
    }

    /** Remove an added amount (top-up) and its bank transaction. */
    public function deleteEntry(Request $request, $id)
    {
        $entry = InvestmentTransaction::findOrFail($id);
        if ($entry->type !== InvestmentTransaction::TYPE_TOP_UP) {
            return response()->json(['success' => false, 'msg' => 'Only added amounts can be removed here']);
        }

        DB::beginTransaction();
        try {
            $investment = $entry->investment;
            InvestmentLedger::deleteEntry($entry);
            InvestmentLedger::recalculate($investment);

            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Added amount removed']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    // Delete investment together with every bank transaction it posted
    public function destroy($id)
    {
        $investment = Investment::findOrFail($id);

        DB::beginTransaction();
        try {
            foreach ($investment->transactions()->get() as $entry) {
                InvestmentLedger::deleteEntry($entry);
            }
            $investment->delete();

            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Investment deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
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

    /**
     * Add or edit return info. There is one return per investment: editing it
     * updates the same bank debit instead of posting a new one each save.
     */
    public function addReturn(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        $data = $request->validate([
            'return_amount' => 'required|numeric|min:0',
            'return_date' => 'required|date',
            'return_account_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $user_id = $request->session()->get('user.id');

            $investment->return_amount = $data['return_amount'];
            $investment->return_date = $data['return_date'];
            if (!empty($data['return_account_id'])) {
                $investment->return_account_id = $data['return_account_id'];
            }
            $investment->save();

            $entry = InvestmentTransaction::firstOrNew([
                'investment_id' => $investment->id,
                'type' => InvestmentTransaction::TYPE_RETURN,
            ]);
            $entry->amount = $investment->return_amount;
            $entry->transaction_date = $investment->return_date;
            $entry->account_id = $investment->return_account_id;
            $entry->created_by = $entry->created_by ?: $user_id;
            $entry->save();
            InvestmentLedger::syncBank($entry, $user_id);

            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Return info updated', 'data' => $investment]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    /**
     * Mismatch report between the investment ledger and the bank accounts:
     * entries whose bank transaction is missing or differs, and investment
     * bank transactions that no entry points to.
     */
    public function reconcile(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $mismatches = [];
        InvestmentTransaction::with(['investment.investor', 'account', 'accountTransaction.account'])
            ->orderBy('investment_id')
            ->orderBy('id')
            ->chunk(500, function ($entries) use (&$mismatches) {
                foreach ($entries as $e) {
                    $issue = InvestmentLedger::bankIssue($e);
                    if (! $issue) {
                        continue;
                    }
                    $mismatches[] = [
                        'entry_id' => $e->id,
                        'investment_id' => $e->investment_id,
                        'investor_name' => optional(optional($e->investment)->investor)->name,
                        'invoice_no' => optional($e->investment)->invoice_no,
                        'type_label' => $e->typeLabel(),
                        'date' => $e->transaction_date,
                        'amount' => (float) $e->amount,
                        'account_name' => optional($e->account)->name,
                        'bank_amount' => $e->accountTransaction ? (float) $e->accountTransaction->amount : null,
                        'bank_account_name' => optional(optional($e->accountTransaction)->account)->name,
                        'issue' => $issue,
                    ];
                }
            });

        $orphans = InvestmentLedger::orphanBankTransactions($business_id)->map(function ($at) {
            return [
                'id' => $at->id,
                'account_name' => optional($at->account)->name,
                'type' => $at->type,
                'amount' => (float) $at->amount,
                'operation_date' => optional($at->operation_date)->toDateString(),
                'note' => $at->note,
            ];
        })->values();

        return response()->json(['success' => true, 'mismatches' => $mismatches, 'orphans' => $orphans]);
    }

    /** Bring one entry's bank transaction in line with the entry. */
    public function syncEntry(Request $request, $id)
    {
        $entry = InvestmentTransaction::findOrFail($id);
        InvestmentLedger::syncBank($entry, $request->session()->get('user.id'));

        return response()->json(['success' => true, 'msg' => 'Bank account updated']);
    }

    /** Fix every mismatched entry in one go. */
    public function syncAllEntries(Request $request)
    {
        $user_id = $request->session()->get('user.id');
        $fixed = 0;

        DB::beginTransaction();
        try {
            InvestmentTransaction::with('accountTransaction')->chunk(500, function ($entries) use ($user_id, &$fixed) {
                foreach ($entries as $e) {
                    if (InvestmentLedger::bankIssue($e)) {
                        InvestmentLedger::syncBank($e, $user_id);
                        $fixed++;
                    }
                }
            });

            DB::commit();
            return response()->json(['success' => true, 'msg' => $fixed.' entr'.($fixed === 1 ? 'y' : 'ies').' synced to bank']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong']);
        }
    }

    /** Remove an investment bank transaction that no ledger entry owns. */
    public function deleteOrphan(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $at = AccountTransaction::findOrFail($id);

        $owned = Account::where('business_id', $business_id)->where('id', $at->account_id)->exists();
        $linked = InvestmentTransaction::where('account_transaction_id', $at->id)->exists();
        if (! $owned || $linked || ! InvestmentLedger::isInvestmentNote($at->note)) {
            return response()->json(['success' => false, 'msg' => 'This bank entry is not an unlinked investment entry']);
        }

        $at->delete();

        return response()->json(['success' => true, 'msg' => 'Bank entry removed']);
    }
}
