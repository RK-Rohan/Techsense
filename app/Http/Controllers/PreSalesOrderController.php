<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\CustomerGroup;
use App\BusinessLocation;
use App\Utils\ModuleUtil;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;

class PreSalesOrderController extends Controller
{
    // Display a listing of the resource.
        /**
     * All Utils instance.
     */
    protected $contactUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $productUtil;

    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;


    }
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_details = $this->businessUtil->getDetails($business_id);
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        $default_location = null;
        foreach ($business_locations as $id => $name) {
            $default_location = BusinessLocation::findOrFail($id);
            break;
        }

        $customers = [];

        return view('pre_sales.index', compact('business_details','business_locations','bl_attributes','default_location', 'customers'));
    }

    // Show the form for creating a new resource.
    public function create()
    {
        $sale_type = request()->get('sale_type', '');

        if ($sale_type == 'sales_order') {
            if (!auth()->user()->can('so.create')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (!auth()->user()->can('direct_sell.access')) {
                abort(403, 'Unauthorized action.');
            }
        }

        $business_id = request()->session()->get('user.business_id');
        $business_details = $this->businessUtil->getDetails($business_id);
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        $default_location = null;
        foreach ($business_locations as $id => $name) {
            $default_location = BusinessLocation::findOrFail($id);
            break;
        }


        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        $customer_groups = CustomerGroup::forDropdown($business_id);


        $default_datetime = $this->businessUtil->format_date('now', true);
        $statuses = Transaction::sell_statuses();
        return view('pre_sales.create', compact('business_details','sale_type','types','customer_groups','business_locations','bl_attributes','default_location','statuses', 'default_datetime'));
    }

    public function store(Request $request)
    {
        return 'on working';
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        return view('pre_sales.edit');
    }

    // Display the specified resource.
    public function view($id)
    {
        return view('pre_sales.view');
    }

    // Remove the specified resource from storage.
    public function delete($id)
    {
        // Code to delete the pre-sales order with id $id
    }
}
