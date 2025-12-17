<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Role;
use App\Models\Customer;
use App\Models\Agent;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Code;
use App\Models\SpecialPrice;
use Rap2hpoutre\FastExcel\FastExcel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::view('/privacy-policy','privacy');

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/test', function() {
// 	$role = User::where('id', 1)->firstOrFail();
//     $role->assignRole('admin');
// });


// Route::get('/import', function () {
//     DB::beginTransaction();

//     $now = now();
//     $collection = (new FastExcel)->import('public/Customer.xlsx');

//     for ($i = 0; $i < count($collection); $i++) {
//         $x = strtolower($collection[$i]['Payment Term']);
//         if ($x == 'cash') {
//             $term = 1;
//         } else if ($x == 'credit') {
//             $term = 2;
//         } else if ($x == 'online bankin') {
//             $term = 3;
//         } else if ($x == 'e-wallet') {
//             $term = 4;
//         } else if ($x == 'cheque') {
//             $term = 5;
//         }
        
//         $group_id = Code::where('code', 'customer_group')->where(DB::raw('BINARY `description`'), $collection[$i]['Group'])->value('value');

//         if (!isset($term) || $group_id == null) {
//             dd($collection[$i], $group_id);
//         }
        
//         $customer = Customer::create([
//             'code' => $collection[$i]['Code'],
//             'company' => $collection[$i]['Company'],
//             'chinese_name' => null,
//             'paymentterm' => $term,
//             'group' => $group_id,
//             'agent_id' => null,
//             'phone' => $collection[$i]['Phone'],
//             'address' => $collection[$i]['Address'],
//             'tin' => null,
//             'sst' => null,
//             'status' => $collection[$i]['Status'] == 'Active' ? 1 : 0,
//             'created_at' => $now,
//             'updated_at' => $now,
//         ]);
        
//         for ($j = 1; $j < 3; $j++) {
//             if ($collection[$i]['Product Name ' . $j] == '') {
//                 continue;
//             }
            
//             $prod_name = $collection[$i]['Product Name ' . $j];
//             if ($prod_name == 'Ice 1 ') {
//                 $prod_name = 'Ice 1';
//             }
//             $prod_name = str_replace(' ', '0', $prod_name);
            
//             $product_id = Product::where(DB::raw('BINARY `code`'), $prod_name)->value('id');
            
//             if ($product_id == null) {
//                 dd($product_id, $prod_name, $collection[$i]);
//             }
            
//             SpecialPrice::create([
//                 'product_id' => $product_id,
//                 'customer_id' => $customer->id,
//                 'price' => $collection[$i]['RM ' . $j],
//                 'status' => 1,
//             ]);
//         }
//     }
    
//     DB::commit();

//     dd('end');
// });

// Route::get('/assign', function () {
//     DB::beginTransaction();

//     $now = now();

//     $drivers = Driver::get();
//     $data = [];
//     $ids = [];
//     $group_ids = [];
//     for ($i = 0; $i < count($drivers) ;$i++) {
//         $id = str_replace('JK_', '',$drivers[$i]->employeeid);
//         $ids[] = $id;
//         $group_id = Code::where('code', 'customer_group')->where(DB::raw('BINARY `description`'), 'Kedai ' . $id)->value('value');
//         $group_ids[] = $group_id;
//         $cus = Customer::get();

//         for ($j = 0; $j < count($cus) ;$j++) {
//             $cus_group_ids = explode(',', $cus[$j]->group);
            
//             if (in_array($group_id, $cus_group_ids) == true) {
//                 DB::table('assigns')->insert([
//                     'driver_id' => $drivers[$i]->id,
//                     'customer_id' => $cus[$j]->id,
//                     'sequence' => $j,
//                     'created_at' => $now,
//                     'updated_at' => $now,
//                 ]);
//             }
//         }
//     }
//     // DB::table('assigns')->where('id', '>', 768)->delete();
    
//     DB::commit();

//     dd('end');
// });

Auth::routes();

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return 'Application cache has been cleared';
});

Route::get('/', [App\Http\Controllers\DeliveryOrderController::class, 'index']);
Route::get('/home', [App\Http\Controllers\DeliveryOrderController::class, 'index'])->name('home');
// Route::get('/archived', [App\Http\Controllers\ArcHomeController::class, 'index'])->name('home');
// Route::get('/scheduler/updatedo', [App\Http\Controllers\scheduler::class, 'updateDoRate']);
// Route::get('/scheduler/archiveddo', [App\Http\Controllers\scheduler::class, 'archivedDeliveryOrder']);
Route::get('/scheduler/checklorryservice', [App\Http\Controllers\scheduler::class, 'checkLorryService']);
Route::get('/scheduler/checkkelindanpermit', [App\Http\Controllers\scheduler::class, 'checkKelindanPermit']);
Route::get('/customers/get-destination', [App\Http\Controllers\CustomerController::class, 'getDestination'])->name('customers.getDestination');
Route::post('/trips/end-trip', [App\Http\Controllers\TripController::class, 'endTrip'])->name('trips.endTrip');

// Route::group(['middleware' => ['role:admin']], function() {
//     Route::resource('roles', App\Http\Controllers\RoleController::class);
//     Route::resource('codes', App\Http\Controllers\CodeController::class);
//     Route::resource('permissions', App\Http\Controllers\PermissionController::class);
// });


// Route::resource('codes', App\Http\Controllers\CodeController::class);
// Route::resource('users', UserController::class);
// Route::resource('userHasRoles', App\Http\Controllers\UserHasRoleController::class);
// Route::resource('roles', App\Http\Controllers\RoleController::class);
// Route::resource('roleHasPermissions', App\Http\Controllers\RoleHasPermissionController::class);
Route::group(['middleware' => ['auth']], function() {
    // Route::post('/home/getProductDelivered', [App\Http\Controllers\HomeController::class, 'getProductDelivered']);
    Route::post('/home/getTotalSales', [App\Http\Controllers\HomeController::class, 'getTotalSales']);
Route::post('/home/getTotalSalesQty', [App\Http\Controllers\HomeController::class, 'getTotalSalesQty']);
    // Route::post('/home/getDriverPerformance', [App\Http\Controllers\HomeController::class, 'getDriverPerformance']);
    // Route::post('/home/getDriverList', [App\Http\Controllers\HomeController::class, 'getDriverList']);
    // Route::post('/home/getProductType', [App\Http\Controllers\HomeController::class, 'getProductType']);
    // Route::resource('saveviews', App\Http\Controllers\saveviewsController::class);
    // Route::post('/saveviews/massdestroy', [App\Http\Controllers\saveviewsController::class, 'massdestroy']);
    // Route::get('/saveviews/view/{id}', [App\Http\Controllers\saveviewsController::class, 'view'])->name('showview');
    Route::group(['middleware' => ['permission:deliveryorder']], function() {
        Route::post('deliveryOrders/approve/{id}', [App\Http\Controllers\DeliveryOrderController::class, 'approve'])->name('deliveryOrders.approve');
        Route::resource('deliveryOrders', App\Http\Controllers\DeliveryOrderController::class);
        Route::post('/deliveryOrders/getDriverInfo', [App\Http\Controllers\DeliveryOrderController::class, 'getDriverInfo']);
        Route::post('/deliveryOrders/getDriverLorry', [App\Http\Controllers\DeliveryOrderController::class, 'getDriverLorry']);
        Route::post('/deliveryOrders/getLorryInfo', [App\Http\Controllers\DeliveryOrderController::class, 'getLorryInfo']);
        Route::post('/deliveryOrders/getClaimInfo', [App\Http\Controllers\DeliveryOrderController::class, 'getClaimInfo']);
        Route::post('/deliveryOrders/getBillingRateInfo', [App\Http\Controllers\DeliveryOrderController::class, 'getBillingRateInfo']);
        Route::post('/deliveryOrders/getCommissionRateInfo', [App\Http\Controllers\DeliveryOrderController::class, 'getCommissionRateInfo']);
        Route::post('/deliveryOrders/getBillingRate', [App\Http\Controllers\DeliveryOrderController::class, 'getBillingRate']);
        Route::post('/deliveryOrders/getCommissionRate', [App\Http\Controllers\DeliveryOrderController::class, 'getCommissionRate']);
        Route::post('/items/getBillingRate', [App\Http\Controllers\ItemController::class, 'getBillingRate']);
        Route::post('/items/getCommissionRate', [App\Http\Controllers\ItemController::class, 'getCommissionRate']);
        Route::post('/deliveryOrders/massdestroy', [App\Http\Controllers\DeliveryOrderController::class, 'massdestroy']);
        Route::post('/deliveryOrders/massupdatestatus', [App\Http\Controllers\DeliveryOrderController::class, 'massupdatestatus']);
        Route::post('/deliveryOrders/masssave', [App\Http\Controllers\DeliveryOrderController::class, 'masssave']);
        // Route::post('/delivery-orders/save-google-location', [App\Http\Controllers\DeliveryOrderController::class, 'saveGoogleLocation'])->name('deliveryOrders.saveGoogleLocation');
        Route::get('/deliveryOrders/print/{id}/{task_id?}', [App\Http\Controllers\DeliveryOrderController::class, 'print'])->name('deliveryOrders.print');
        Route::post('tasks/get-task-images', [App\Http\Controllers\DeliveryOrderController::class, 'getTaskImages'])->name('deliveryOrders.getTaskImages');
        Route::get('delivery-orders/get-next-do-number', [App\Http\Controllers\DeliveryOrderController::class, 'getNextDONumber'])->name('deliveryOrders.getNextDONumber');
        //Archived DeliveryOrder//
        Route::get('/archived/deliveryOrders', [App\Http\Controllers\ArcDeliveryOrderController::class, 'index']);
        Route::get('/archived/deliveryOrders/index', [App\Http\Controllers\ArcDeliveryOrderController::class, 'index']);
        Route::get('/archived/deliveryOrders/{id}', [App\Http\Controllers\ArcDeliveryOrderController::class, 'show']);
        Route::post('/archived/deliveryOrders/getClaimInfo', [App\Http\Controllers\ArcDeliveryOrderController::class, 'getClaimInfo']);
        //Archived DeliveryOrder//    
    });
    Route::group(['middleware' => ['permission:machinerental']], function() {
        Route::resource('machineRentals', App\Http\Controllers\MachineRentalController::class);
        Route::post('/machine-rentals/get-next-rental-number', [App\Http\Controllers\MachineRentalController::class, 'getNextRentalNumber'])->name('machineRentals.getNextRentalNumber');
        Route::post('/machineRentals/massdestroy', [App\Http\Controllers\MachineRentalController::class, 'massdestroy']);
        Route::post('/machineRentals/massupdatestatus', [App\Http\Controllers\MachineRentalController::class, 'massupdatestatus']);
        Route::post('/machineRentals/masssave', [App\Http\Controllers\MachineRentalController::class, 'masssave']);
        Route::get('/machineRentals/print/{id}', [App\Http\Controllers\MachineRentalController::class, 'print'])->name('machineRentals.print');
    });
    Route::group(['middleware' => ['permission:report']], function() {
        Route::resource('reports', App\Http\Controllers\ReportController::class);
        Route::post('/reports/run', [App\Http\Controllers\ReportController::class, 'run']);
        Route::get('/showreport/{id}', [App\Http\Controllers\ReportController::class, 'report'])->name('showreport');
        Route::get('/report/sellerinformationrecord', [App\Http\Controllers\ReportController::class, 'seller_information_record'])->name('seller_information_record');
        Route::get('/report/customerstatementofaccount', [App\Http\Controllers\ReportController::class, 'customer_statement_of_account'])->name('customer_statement_of_account');
        Route::get('/report/daily_sales_report_excel', [App\Http\Controllers\ReportController::class, 'daily_sales_report_excel'])->name('daily_sales_report_excel');
        Route::get('/report/late_delivery_report', [App\Http\Controllers\ReportController::class, 'late_delivery_report'])->name('late_delivery_report');

    });
    // Route::group(['middleware' => ['permission:report|paymentdetail']], function() {
    //     Route::resource('reports', App\Http\Controllers\ReportController::class);
    //     Route::post('/reports/run', [App\Http\Controllers\ReportController::class, 'run']);
        // Route::resource('reportdetails', App\Http\Controllers\ReportdetailController::class);
    //     Route::get('/showreport/{id}', [App\Http\Controllers\ReportController::class, 'report'])->name('showreport');
    //     Route::get('/reports/paymentoneview/{id}', [App\Http\Controllers\ReportController::class, 'paymentoneview'])->name('paymentoneview');
    //     // Route::get('/reports/paymentoneviewtemplate/{id}', [App\Http\Controllers\ReportController::class, 'paymentoneviewtemplate'])->name('paymentoneviewtemplate');
    //     Route::get('/reports/getPaymentoneviewPDF/{id}/{function}', [App\Http\Controllers\ReportController::class, 'getPaymentoneviewPDF'])->name('getPaymentoneviewPDF');
    //     Route::get('/newreports', [App\Http\Controllers\ReportController::class, 'newreport'])->name('newreport');
    //     Route::get('/report/massDownloadPaymentoneviewPDF/{ids}', [App\Http\Controllers\ReportController::class, 'massDownloadPaymentoneviewPDF'])->name('massDownloadPaymentoneviewPDF');
    // });
    // Route::group(['middleware' => ['permission:commissionbyvendor']], function() {
    //     Route::resource('commissionByVendors', App\Http\Controllers\CommissionByVendorsController::class);
    //     Route::post('/commissionByVendors/massdestroy', [App\Http\Controllers\CommissionByVendorsController::class, 'massdestroy']);
    //     Route::post('/commissionByVendors/massupdatestatus', [App\Http\Controllers\CommissionByVendorsController::class, 'massupdatestatus']);
    //     Route::post('/commissionByVendors/masssave', [App\Http\Controllers\CommissionByVendorsController::class, 'masssave']);
    // });

    Route::group(['middleware' => ['permission:lorry']], function() {
        Route::resource('lorries', App\Http\Controllers\LorryController::class);
        Route::post('/lorries/massdestroy', [App\Http\Controllers\LorryController::class, 'massdestroy']);
        Route::post('/lorries/massupdatestatus', [App\Http\Controllers\LorryController::class, 'massupdatestatus']);
        Route::resource('servicedetails', App\Http\Controllers\servicedetailsController::class);
        Route::post('/servicedetails/massdestroy', [App\Http\Controllers\servicedetailsController::class, 'massdestroy']);
        Route::post('/servicedetails/getTyreServiceInfo', [App\Http\Controllers\servicedetailsController::class, 'getTyreServiceInfo']);
        Route::post('/servicedetails/getInsuranceServiceInfo', [App\Http\Controllers\servicedetailsController::class, 'getInsuranceServiceInfo']);
        Route::post('/servicedetails/getPermitServiceInfo', [App\Http\Controllers\servicedetailsController::class, 'getPermitServiceInfo']);
        Route::post('/servicedetails/getRoadtaxServiceInfo', [App\Http\Controllers\servicedetailsController::class, 'getRoadtaxServiceInfo']);
        Route::post('/servicedetails/getInspectionServiceInfo', [App\Http\Controllers\servicedetailsController::class, 'getInspectionServiceInfo']);
        Route::post('/servicedetails/getOtherServiceInfo', [App\Http\Controllers\servicedetailsController::class, 'getOtherServiceInfo']);
        Route::post('/servicedetails/getFireExtinguisherServiceInfo', [App\Http\Controllers\servicedetailsController::class, 'getFireExtinguisherServiceInfo']);
    });
    Route::group(['middleware' => ['permission:driver']], function() {
        Route::get('/drivers/{id}/assign', [App\Http\Controllers\DriverController::class, 'assign'])->name('drivers.assign');
        Route::post('/drivers/{id}/addassign', [App\Http\Controllers\DriverController::class, 'addassign'])->name('drivers.addassign');
        Route::delete('/drivers/{id}/deleteassign', [App\Http\Controllers\DriverController::class, 'deleteassign'])->name('drivers.deleteassign');
        Route::resource('drivers', App\Http\Controllers\DriverController::class);
        Route::post('/drivers/massdestroy', [App\Http\Controllers\DriverController::class, 'massdestroy']);
        Route::post('/drivers/massupdatestatus', [App\Http\Controllers\DriverController::class, 'massupdatestatus']);
        Route::get('/driverLocations/getDriverSummary', [App\Http\Controllers\DriverLocationController::class, 'getDriverSummary'])->name('driverLocations.getDriverSummary');
        Route::resource('driverLocations', App\Http\Controllers\DriverLocationController::class);
    });
    Route::group(['middleware' => ['permission:kelindan']], function() {
        Route::resource('kelindans', App\Http\Controllers\KelindanController::class);
        Route::post('/kelindans/massdestroy', [App\Http\Controllers\KelindanController::class, 'massdestroy']);
        Route::post('/kelindans/massupdatestatus', [App\Http\Controllers\KelindanController::class, 'massupdatestatus']);
    });
    Route::group(['middleware' => ['permission:agent']], function() {
        Route::resource('agents', App\Http\Controllers\AgentController::class);
        Route::post('/agents/getattachment', [App\Http\Controllers\AgentController::class, 'getattachment']);
        Route::post('/agents/addattachment', [App\Http\Controllers\AgentController::class, 'addattachment']);
        Route::post('/agents/massdestroy', [App\Http\Controllers\AgentController::class, 'massdestroy']);
        Route::post('/agents/massupdatestatus', [App\Http\Controllers\AgentController::class, 'massupdatestatus']);
    });
    Route::group(['middleware' => ['permission:supervisor']], function() {
        Route::resource('supervisors', App\Http\Controllers\SupervisorController::class);
        Route::post('/supervisors/massdestroy', [App\Http\Controllers\SupervisorController::class, 'massdestroy']);
        Route::post('/supervisors/massupdatestatus', [App\Http\Controllers\SupervisorController::class, 'massupdatestatus']);
    });
    Route::group(['middleware' => ['permission:product']], function() {
        Route::get('/products/sync-xero', [App\Http\Controllers\ProductController::class, 'syncXero']);
        Route::resource('products', App\Http\Controllers\ProductController::class);
        Route::post('/products/massdestroy', [App\Http\Controllers\ProductController::class, 'massdestroy']);
        Route::post('/products/massupdatestatus', [App\Http\Controllers\ProductController::class, 'massupdatestatus']);
    });
    Route::group(['middleware' => ['permission:customer']], function() {
        Route::get('/customers/sync-xero', [App\Http\Controllers\CustomerController::class, 'syncXero']);
        Route::resource('customers', App\Http\Controllers\CustomerController::class);
        Route::post('/customers/massdestroy', [App\Http\Controllers\CustomerController::class, 'massdestroy']);
        Route::post('/customers/massupdatestatus', [App\Http\Controllers\CustomerController::class, 'massupdatestatus']);
    });
    Route::group(['middleware' => ['permission:company']], function() {
        Route::resource('companies', App\Http\Controllers\CompanyController::class);
        Route::post('/companies/massdestroy', [App\Http\Controllers\CompanyController::class, 'massdestroy']);
    });
    Route::group(['middleware' => ['permission:specialprice']], function() {
        Route::resource('specialPrices', App\Http\Controllers\SpecialPriceController::class);
        Route::post('/specialPrices/massdestroy', [App\Http\Controllers\SpecialPriceController::class, 'massdestroy']);
        Route::post('/specialPrices/massupdatestatus', [App\Http\Controllers\SpecialPriceController::class, 'massupdatestatus']);
    });
    Route::group(['middleware' => ['permission:foc']], function() {
        Route::resource('focs', App\Http\Controllers\focController::class);
        Route::post('/focs/massdestroy', [App\Http\Controllers\focController::class, 'massdestroy']);
        Route::post('/focs/massupdatestatus', [App\Http\Controllers\focController::class, 'massupdatestatus']);
    });
    Route::group(['middleware' => ['permission:assign']], function() {
        Route::get('/assigns/masscreate', [App\Http\Controllers\AssignController::class, 'masscreate'])->name('assigns.masscreate');
        Route::post('/assigns/massstore', [App\Http\Controllers\AssignController::class, 'massstore'])->name('assigns.massstore');
        Route::resource('assigns', App\Http\Controllers\AssignController::class);
        Route::post('/assigns/massdestroy', [App\Http\Controllers\AssignController::class, 'massdestroy']);
        Route::post('/customerfindgroup', [App\Http\Controllers\AssignController::class, 'customerfindgroup'])->name('assigns.customerfindgroup');
    });
    Route::group(['middleware' => ['permission:invoice']], function() {
        //Invoice
        Route::get('/invoices/sync-xero', [App\Http\Controllers\InvoiceController::class, 'syncXero']);
        Route::get('/invoices/{id}/detail', [App\Http\Controllers\InvoiceController::class, 'detail'])->name('invoices.detail');
        Route::post('/invoices/{id}/adddetail', [App\Http\Controllers\InvoiceController::class, 'adddetail'])->name('invoices.adddetail');
        Route::delete('/invoices/{id}/deletedetail', [App\Http\Controllers\InvoiceController::class, 'deletedetail'])->name('invoices.deletedetail');
        Route::get('/invoices/customer/{id}', [App\Http\Controllers\InvoiceController::class, 'getcustomer']);
        Route::resource('invoices', App\Http\Controllers\InvoiceController::class);
        Route::post('/invoices/massdestroy', [App\Http\Controllers\InvoiceController::class, 'massdestroy']);
        Route::post('/invoices/massupdatestatus', [App\Http\Controllers\InvoiceController::class, 'massupdatestatus']);
        //Invoice Detail
        Route::get('invoiceDetails/getprice/{invoice_id}/{product_id}', [App\Http\Controllers\InvoiceDetailController::class, 'getprice']);
        Route::resource('invoiceDetails', App\Http\Controllers\InvoiceDetailController::class);
        Route::post('/invoiceDetails/massdestroy', [App\Http\Controllers\InvoiceDetailController::class, 'massdestroy']);
        //Invoice Payment
        Route::get('/invoicePayments/customer-invoices/{id}', [App\Http\Controllers\InvoicePaymentController::class, 'getcustomerinvoice']);
        Route::post('invoicePayments/updatepayment/{id}', [App\Http\Controllers\InvoicePaymentController::class, 'updatepayment']);
        Route::get('invoicePayments/getpayment/{id}', [App\Http\Controllers\InvoicePaymentController::class, 'getpayment']);
        Route::get('invoicePayments/getinvoice/{id}', [App\Http\Controllers\InvoicePaymentController::class, 'getinvoice']);
        Route::resource('invoicePayments', App\Http\Controllers\InvoicePaymentController::class);
        Route::post('/invoicePayments/massupdatestatus', [App\Http\Controllers\InvoicePaymentController::class, 'massupdatestatus']);
        //Print Invoice
        Route::get('/print/invoices/getInvoiceViewPDF/{id}/{function}', [App\Http\Controllers\InvoiceController::class, 'getInvoiceViewPDF'])->name('invoice.print');
        //Print Receipt
        Route::get('/print/invoicePayments/getReceiptViewPDF/{id}/{function}', [App\Http\Controllers\InvoicePaymentController::class, 'getReceiptViewPDF'])->name('invoicePayments.print');

    });
    Route::group(['middleware' => ['permission:task']], function() {
        Route::get('tasks/download-image/{type}/{taskId}', [TaskController::class, 'downloadImage'])->name('tasks.downloadImage');
        Route::resource('tasks', App\Http\Controllers\TaskController::class);
        Route::post('/tasks/get-delivery-order-details', [App\Http\Controllers\TaskController::class, 'getDeliveryOrderDetails'])->name('tasks.getDeliveryOrderDetails');
        Route::resource('taskTransfers', App\Http\Controllers\TaskTransferController::class);
        Route::post('/tasks/get-next-task-number', [App\Http\Controllers\TaskController::class, 'getNextTaskNumber'])->name('tasks.getNextTaskNumber');
        Route::post('tasks/update-status-via-modal', [App\Http\Controllers\TaskController::class, 'updateStatusViaModal'])->name('tasks.updateStatusViaModal');
    });
    Route::group(['middleware' => ['permission:trip']], function() {
        Route::resource('trips', App\Http\Controllers\TripController::class);

    });
    Route::group(['middleware' => ['permission:checkin']], function() {
        Route::resource('checkins', App\Http\Controllers\CheckInController::class);
        Route::get('/checkins/{id}/details', [App\Http\Controllers\CheckInController::class, 'getDetails'])->name('checkins.details');
    });
    Route::group(['middleware' => ['permission:inventorybalance']], function() {
        Route::get('/inventoryBalances', [App\Http\Controllers\InventoryBalanceController::class, 'index'])->name('inventoryBalances.index');
        Route::post('/inventoryBalances/stockin', [App\Http\Controllers\InventoryBalanceController::class, 'stockin'])->name('inventoryBalances.stockin');
        Route::get('/inventoryBalances/getstock/{lorry_id}/{product_id}', [App\Http\Controllers\InventoryBalanceController::class, 'getstock'])->name('inventoryBalances.getstock');
        Route::post('/inventoryBalances/stockout', [App\Http\Controllers\InventoryBalanceController::class, 'stockout'])->name('inventoryBalances.stockout');
    });
    Route::group(['middleware' => ['permission:inventorytransaction']], function() {
        Route::get('/inventoryTransactions', [App\Http\Controllers\InventoryTransactionController::class, 'index'])->name('inventoryTransactions.index');
    });
    Route::group(['middleware' => ['permission:inventorytransfer']], function() {
        Route::get('/inventoryTransfers', [App\Http\Controllers\InventoryTransferController::class, 'index'])->name('inventoryTransfers.index');
    });


    Route::group(['middleware' => ['permission:code']], function() {
        Route::resource('codes', App\Http\Controllers\CodeController::class);
    });

    Route::group(['middleware' => ['permission:code']], function() {
        Route::prefix('language')->group(function() {
            Route::get('/', [App\Http\Controllers\LanguageController::class, 'index'])->name('language.index');
            Route::post('/change', [App\Http\Controllers\LanguageController::class, 'changeLanguage'])->name('language.change');
            Route::post('/save', [App\Http\Controllers\LanguageController::class, 'saveTranslations'])->name('language.save');
            Route::post('/import', [App\Http\Controllers\LanguageController::class, 'importLanguage'])->name('language.import');
            Route::delete('/language/{id}', [App\Http\Controllers\LanguageController::class, 'deleteLanguage'])->name('language.delete');
            Route::post('/language/export', [App\Http\Controllers\LanguageController::class, 'exportTranslations'])->name('language.export');
            Route::post('/language/import/file', [App\Http\Controllers\LanguageController::class, 'importTranslations'])->name('language.import.file');
        });
    });
    
    Route::group(['middleware' => ['permission:code']], function() {
        Route::prefix('mobile_language')->group(function() {
            Route::get('/', [App\Http\Controllers\MobileLanguageController::class, 'index'])->name('mobile_language.index');
            Route::get('/edit/{id}', [App\Http\Controllers\MobileLanguageController::class, 'edit'])->name('mobile_language.edit');
            Route::post('/save', [App\Http\Controllers\MobileLanguageController::class, 'saveTranslations'])->name('mobile_language.save');
            Route::delete('/delete/{id}', [App\Http\Controllers\MobileLanguageController::class, 'deleteLanguage'])->name('mobile_language.destroy');
            Route::post('/import', [App\Http\Controllers\MobileLanguageController::class, 'importLanguage'])->name('mobile_language.import');

            Route::post('/export', [App\Http\Controllers\MobileLanguageController::class, 'exportTranslations'])->name('mobile_language.export');
            Route::post('/import-file', [App\Http\Controllers\MobileLanguageController::class, 'importTranslations'])->name('mobile_language.import.file');
        });
    });

    Route::get('/language/load', [LanguageController::class, 'loadTranslations'])->name('language.load');   
    
    Route::group(['middleware' => ['permission:code']], function() {
        Route::resource('customer_group', App\Http\Controllers\CustomerGroupController::class);
    });
    Route::group(['middleware' => ['permission:code']], function() {
        Route::resource('commission_group', App\Http\Controllers\CommissionGroupController::class);
    });
    Route::group(['middleware' => ['permission:user']], function() {
        Route::resource('users', UserController::class);
    });
    Route::group(['middleware' => ['permission:userrole']], function() {
        Route::resource('userHasRoles', App\Http\Controllers\UserHasRoleController::class);
    });
    Route::group(['middleware' => ['permission:role']], function() {
        Route::resource('roles', App\Http\Controllers\RoleController::class);
    });
    Route::group(['middleware' => ['permission:rolepermission']], function() {
        Route::resource('roleHasPermissions', App\Http\Controllers\RoleHasPermissionController::class);
    });
    if( env('APP_ENV') == 'local'){
        Route::resource('permissions', App\Http\Controllers\PermissionController::class);
        // Route::resource('reportdetails', App\Http\Controllers\ReportdetailController::class);
    }
});

