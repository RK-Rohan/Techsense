<?php

namespace App\Http\Controllers;

use PDF;
use App\Brands;

use App\TermsCondition;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Yajra\DataTables\Facades\DataTables;



class TermsConditionController extends Controller
{
   /**
    * All Utils instance.
    */
   protected $moduleUtil;

   /**
    * Constructor
    *
    * @param  ProductUtils  $product
    * @return void
    */
   public function __construct(ModuleUtil $moduleUtil)
   {
      $this->moduleUtil = $moduleUtil;
   }


   public function index()
   {
      if (request()->ajax()) {

         $data = TermsCondition::select(['id', 'description']);

         return Datatables::of($data)
            ->addColumn('action', function ($row) {
               return '
                <button data-href="' . action([\App\Http\Controllers\TermsConditionController::class, 'edit'], [$row->id]) . '" 
                        class="btn btn-xs btn-primary edit_button btn-modal">
                    <i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '
                </button>';
            })
            ->rawColumns([2])
            ->make(false);
      }

      return view('terms_conditions.index');
   }


   public function create()
   {

      $quick_add = false;
      if (!empty(request()->input('quick_add'))) {
         $quick_add = true;
      }

      return view('terms_conditions.create')
         ->with(compact('quick_add'));
   }

   public function store(Request $request)
   {
      try {
         $input = $request->only(['description']);
         $input['created_by'] = $request->session()->get('user.id');

         $data = TermsCondition::create($input);
         $output = [
            'success' => true,
            'data' => $data,
            'msg' => 'Succesfully Added',
         ];
      } catch (\Exception $e) {
         Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

         $output = [
            'success' => false,
            'msg' => __('messages.something_went_wrong'),
         ];
      }

      return $output;
   }

   public function edit($id)
   {
      try {
         // Retrieve the TermsCondition record by ID
         $termsCondition = TermsCondition::findOrFail($id);

         // Return the view with the data to be edited
         return view('terms_conditions.edit')->with(compact('termsCondition'));
      } catch (\Exception $e) {
         Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

         return redirect()->back()->with('status', [
            'success' => false,
            'msg' => __('messages.something_went_wrong')
         ]);
      }
   }

   public function update(Request $request, $id)
   {
       try {
           $termsCondition = TermsCondition::findOrFail($id);
           $termsCondition->update($request->only('description'));
   
           $output = [
               'success' => true,
               'data' => $termsCondition,
               'msg' => 'Successfully Updated',
           ];
       } catch (\Exception $e) {
           Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
   
           $output = [
               'success' => false,
               'msg' => __('messages.something_went_wrong'),
           ];
       }
   
       return $output;
   }
   
}
