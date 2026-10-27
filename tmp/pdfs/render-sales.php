<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Auth::setUser(new App\User(['first_name'=>'Tamim','last_name'=>'Khan','contact_number'=>'+880 1334-945850','custom_field_1'=>'Assistant Manager (Technical)']));
$receipt_details=(object)['display_name'=>'Techsense Bangladesh Ltd.','invoice_no'=>'2026/3544','invoice_date'=>'26-10-2026 12:36 PM','business_name'=>'Midland East Power Ltd.','customer_info'=>'Customer','address_line_1'=>'Youth Tower, 822/2 Rokeya Sarani (8th Floor) Dhaka-1216, Bangladesh','delivered_to'=>'Mr. Md. Abdul Matin','customer_mobile'=>'+8801715-137807','shipping_address'=>'Youth Tower, Dhaka-1216, Bangladesh','shipping_status'=>'Delivered','sales_person_label'=>'Sales By','sales_person'=>'Mr. Sabbir Ahmed Shuvo','sell_custom_field_1_label'=>'PO No','sell_custom_field_1_value'=>'PO-2026-001','subtotal_label'=>'Subtotal:','subtotal'=>'120,000.00','tax_label'=>'VAT (10%):','tax'=>'12,000.00','total_label'=>'Total:','total'=>'132,000.00','total_in_words'=>'One Hundred Thirty Two Thousand','total_paid_label'=>'Total Paid:','total_paid'=>'50,000.00','total_due_label'=>'Total Due:','total_due'=>'82,000.00','payments'=>[['method'=>'Bank transfer','amount'=>'50,000.00','date'=>'26-10-2026']]];
$line=['sub_sku'=>'6GK7542-5FX00-0XE0','name'=>'Communication Module CM 1542-5 Profibus DP','brand'=>'SIEMENS','origin'=>'Germany','product_description'=>'<ul><li>Industrial communication module</li><li>12 month warranty</li></ul>','units'=>'Pcs','quantity'=>'3.00','unit_price_inc_tax'=>'40,000.00','line_total'=>'120,000.00'];
$tc_description=collect(['100% Brand New and original Product.','Payment to Techsense Bangladesh Ltd.','Payment Mode: Maximum 30 days credits after delivery.'])->map(fn($description)=>(object)compact('description'));
foreach(['invoice','chalan'] as $kind){
 foreach([1,24] as $count){
  $receipt_details->lines=array_fill(0,$count,$line);
  $pdf=app('dompdf.wrapper')->loadView('sale_pos.receipts.download_'.$kind,compact('receipt_details','tc_description'))->setPaper('a4','portrait');
  file_put_contents(__DIR__."/$kind-$count.pdf",$pdf->output());
  echo "$kind $count rendered\n";
 }
}
echo 'Browser challan HTML: '.strlen(view('sale_pos.receipts.chalan',compact('receipt_details','tc_description'))->render())." bytes\n";
$receipt_details->lines=[$line];
echo 'Quotation HTML: '.strlen(view('sale_pos.receipts.quotation',compact('receipt_details','tc_description'))->render())." bytes\n";
