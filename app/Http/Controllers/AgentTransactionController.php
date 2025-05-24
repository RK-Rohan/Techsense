<?php

namespace App\Http\Controllers;

use App\AgentTransaction;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AgentTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = AgentTransaction::with(['agent', 'customer', 'transaction'])->get();

            return Datatables::of($data)
                ->addColumn(
                    'action',
                    function ($row) {
                        $button = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                            data-toggle="dropdown" aria-expanded="false"> Action<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                        </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                        <li class="divider"></li>';

                        if ($row->payment_status != "paid") {
                            $button .= '<li><a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'addPayment'], [$row->sales_id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i> Add Payment</a></li>';
                        }

                        $button .= '</ul></div>';

                        return $button;
                    }
                )
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('agent_transaction.index');
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AgentTransaction  $agentTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(AgentTransaction $agentTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AgentTransaction  $agentTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(AgentTransaction $agentTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AgentTransaction  $agentTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AgentTransaction $agentTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AgentTransaction  $agentTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(AgentTransaction $agentTransaction)
    {
        //
    }
}
