<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Utils\FinancialStatementUtil;
use Illuminate\Http\Request;
use PDF;

/**
 * Serves the statutory financial statements under the "New Report" menu.
 *
 * Each statement is computed from existing transactions for the period chosen,
 * alongside the comparative period that precedes it.
 */
class FinancialReportController extends Controller
{
    protected $statementUtil;

    /**
     * The statements this controller can render, keyed by url segment.
     */
    const REPORTS = [
        'financial-position' => [
            'view' => 'financial_report.financial_position',
            'title' => 'Statement of Financial Position',
        ],
        'profit-loss' => [
            'view' => 'financial_report.profit_loss',
            'title' => 'Profit or Loss and other Comprehensive Income A/c',
        ],
        'notes' => [
            'view' => 'financial_report.notes',
            'title' => 'Notes to the Accounts',
        ],
        'receipt-payment' => [
            'view' => 'financial_report.receipt_payment',
            'title' => 'Receipt & Payment',
        ],
        'cash-flow' => [
            'view' => 'financial_report.cash_flow',
            'title' => 'Cash Flow Statement',
        ],
    ];

    public function __construct(FinancialStatementUtil $statementUtil)
    {
        $this->statementUtil = $statementUtil;
    }

    /**
     * Renders a statement, or streams it as a PDF when ?pdf=1 is passed.
     */
    public function show(Request $request, $report)
    {
        $this->authorizeAccess();

        abort_unless(array_key_exists($report, self::REPORTS), 404);

        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'location_id' => 'nullable|integer',
        ]);

        $business_id = $request->session()->get('user.business_id');

        [$start_date, $end_date] = $this->period($request);
        $location_id = $request->filled('location_id') ? $request->location_id : null;

        $statements = $this->statementUtil->getStatements($business_id, $start_date, $end_date, $location_id);

        $config = self::REPORTS[$report];

        $data = [
            'report' => $report,
            'report_title' => $config['title'],
            'business_name' => $request->session()->get('business.name'),
            'statements' => $statements,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'location_id' => $location_id,
        ];

        if ($request->boolean('pdf')) {
            return $this->streamPdf($config['view'].'_pdf', $data, $config['title']);
        }

        $data['business_locations'] = BusinessLocation::forDropdown($business_id, true);
        $data['reports'] = self::REPORTS;

        return view($config['view'], $data);
    }

    /**
     * Only users who can reach the accounting reports may view the statements.
     */
    protected function authorizeAccess()
    {
        if (! auth()->user()->can('account.access') && ! auth()->user()->can('profit_loss_report.view')) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Defaults to the current financial year to date when no period is given.
     */
    protected function period(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        if (empty($start_date) || empty($end_date)) {
            $start_date = \Carbon::now()->startOfYear()->format('Y-m-d');
            $end_date = \Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        if ($start_date > $end_date) {
            [$start_date, $end_date] = [$end_date, $start_date];
        }

        return [$start_date, $end_date];
    }

    /**
     * Streams a statement as a portrait A4 PDF.
     */
    protected function streamPdf($view, array $data, $title)
    {
        $pdf = PDF::loadView($view, $data)->setPaper('a4', 'portrait');

        $filename = str_replace(' ', '_', $title).'_'.$data['end_date'].'.pdf';

        return $pdf->stream($filename);
    }
}
