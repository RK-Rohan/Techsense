<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use Illuminate\Http\Request;

class InvestorPortalController extends Controller
{
    /**
     * Resolve the investor bound to the logged in portal account.
     *
     * Every query in this controller is scoped through this id so an investor
     * can never read another investor's records.
     */
    protected function investorId()
    {
        return auth()->user()->investor_id;
    }

    /**
     * Portal dashboard: summary tiles plus the investor's own investment list.
     */
    public function index()
    {
        $investor = auth()->user()->investor;

        $summary = $this->summaryFor($this->investorId());

        return view('investor_portal.index', compact('investor', 'summary'));
    }

    /**
     * DataTables source for the logged in investor's investments only.
     */
    public function data(Request $request)
    {
        $q = Investment::with(['receivedAccount', 'returnAccount'])
            ->where('investor_id', $this->investorId())
            ->orderBy('received_date', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $q->whereDate('received_date', '>=', $request->input('start_date'))
              ->whereDate('received_date', '<=', $request->input('end_date'));
        }

        $investments = $q->get();
        $investor_name = optional(auth()->user()->investor)->name;

        $rows = $investments->map(function ($inv) use ($investor_name) {
            $amount = (float) $inv->amount;
            $returned = (float) $inv->return_amount;
            $due = $returned < $amount ? $amount - $returned : 0;

            // Days the money has been out: to the return date once repaid,
            // otherwise up to today.
            $loan_days = null;
            if ($inv->received_date) {
                $start = \Carbon\Carbon::parse($inv->received_date);
                $end = $inv->return_date ? \Carbon\Carbon::parse($inv->return_date) : \Carbon\Carbon::now();
                $loan_days = $end->diffInDays($start);
            }

            return [
                'id' => $inv->id,
                'received_date' => $inv->received_date,
                'investor_name' => $investor_name,
                'invoice_no' => $inv->invoice_no,
                'txn_ref' => $inv->txn_ref,
                'amount' => $amount,
                'received_account_name' => optional($inv->receivedAccount)->name,
                'return_amount' => $returned,
                'return_date' => $inv->return_date,
                'status' => $returned > 0 ? ($due > 0 ? 'partial' : 'paid') : 'due',
                'remarks' => $inv->remarks,
                'loan_duration_days' => $loan_days,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    /**
     * Dashboard totals for one investor.
     */
    protected function summaryFor($investor_id)
    {
        $investments = Investment::where('investor_id', $investor_id)
            ->get(['amount', 'return_amount']);

        $total_investment = 0;
        $total_principal_paid = 0;
        $total_paid_with_profit = 0;
        $total_profit = 0;
        $total_due = 0;

        foreach ($investments as $inv) {
            $amount = (float) $inv->amount;
            $returned = (float) $inv->return_amount;

            $total_investment += $amount;

            // Split each payout into principal repaid and profit, so a partly
            // settled investment never reports its outstanding balance as profit.
            $total_principal_paid += min($returned, $amount);
            $total_paid_with_profit += $returned;
            $total_profit += max($returned - $amount, 0);
            $total_due += max($amount - $returned, 0);
        }

        return [
            'total_investment' => $total_investment,
            'total_principal_paid' => $total_principal_paid,
            'total_paid_with_profit' => $total_paid_with_profit,
            'total_profit' => $total_profit,
            'total_due' => $total_due,
            'count' => $investments->count(),
        ];
    }
}
