<?php

namespace App\Http\Controllers;

use App\DataTables\DeliveryOrderDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateDeliveryOrderRequest;
use App\Http\Requests\UpdateDeliveryOrderRequest;
use App\Repositories\DeliveryOrderRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Lorry;
use App\Models\CommissionByVendors;
use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Auth;
use App\Models\Claim;
use App\Models\Task;
use App\Models\Company;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DeliveryOrderController extends AppBaseController
{
    /** @var DeliveryOrderRepository $deliveryOrderRepository*/
    private $deliveryOrderRepository;

    public function __construct(DeliveryOrderRepository $deliveryOrderRepo)
    {
        $this->middleware('auth');
        $this->deliveryOrderRepository = $deliveryOrderRepo;
    }

    /**
     * Display a listing of the DeliveryOrder.
     *
     * @param DeliveryOrderDataTable $deliveryOrderDataTable
     *
     * @return Response
     */
    public function index(DeliveryOrderDataTable $deliveryOrderDataTable)
    {
        return $deliveryOrderDataTable->render('delivery_orders.index');
    }

    
    /**
     * Show the form for creating a new DeliveryOrder.
     *
     * @return Response
     */
    public function create()
    {
        return view('delivery_orders.create');
    }

    /**
     * Store a newly created DeliveryOrder in storage.
     *
     * @param CreateDeliveryOrderRequest $request
     *
     * @return Response
     */
    public function store(CreateDeliveryOrderRequest $request)
    {
        $userRole = auth()->user()->roles()->pluck('name')->first();
        if($userRole !== 'normal admin'){
            Flash::error('Only admin can create delivery order.');

            return redirect(route('deliveryOrders.index'));
        }        

        $input = $request->all();
        $input['date'] = Carbon::parse($input['date'])->format('Y-m-d');

        $DO = [
            'date' => $input['date'],
            'dono' => $input['dono'],
            'customer_id' => $input['customer_id'],
            'place_name' => $input['place_name'],
            'place_address' => $input['place_address'],
            'place_latitude' => $input['place_latitude'],
            'place_longitude' => $input['place_longitude'],
            'product_id' => $input['product_id'],
            'company_id' => $input['company_id'],
            'total_order' => $input['total_order'],
            'progress_total' => isset($input['progress_total']) && $input['progress_total'] !== '' ? $input['progress_total'] : 0,
            'strength_at' => $input['strength_at'],
            'slump' => $input['slump'],
            'status' => 0, 
            'remark' => $input['remark'],
        ];

        DeliveryOrder::create($DO);           

        Flash::success('Delivery Order saved successfully.');

        return redirect(route('deliveryOrders.index'));
    }

    /**
     * Display the specified DeliveryOrder.
     *
     * @param int $id
     *
     * @return Response
     */
    public function approve($id)
    {
        $id = Crypt::decrypt($id);
        $deliveryOrder = $this->deliveryOrderRepository->find($id);

        if (empty($deliveryOrder)) {
            Flash::error('Delivery Order not found');
            return redirect(route('deliveryOrders.index'));
        }

        $userRole = auth()->user()->roles()->pluck('name')->first();
        if($userRole === 'normal admin'){
            Flash::error('You have no permission to approve delivery orders.');
            return redirect(route('deliveryOrders.index'));
        }

        if ($deliveryOrder->approve()) {
            Flash::success('Delivery Order approved successfully and is now ready for delivery.');
        } else {
            Flash::error('Failed to approve delivery order.');
        }

        return redirect(route('deliveryOrders.index'));
    }

    public function getNextDONumber(Request $request) 
    {
        try {
            $companyId = $request->get('company_id');
            
            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company ID is required'
                ], 400);
            }
            
            $doNumber = $this->generateNextDONumber($companyId);
            
            if (!$doNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate DO number'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'do_number' => $doNumber
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating DO number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate next sequential DO number for a company
     *
     * @param int $companyId
     * @return string|null
     */
    private function generateNextDONumber($companyId)
    {
        $company = Company::find($companyId);
        if (!$company) {
            return null;
        }
        
        $prefix = $company->do_prefix;
        
        if (empty($prefix)) {
            return null;
        }
        
        // Get the last DO number for this company with the same prefix
        $lastDO = DeliveryOrder::where('dono', 'like', $prefix . '-%')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastDO) {
            // Extract the number part using the prefix
            $numberPart = str_replace($prefix . '-', '', $lastDO->dono);
            $lastNumber = intval($numberPart);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function getTaskImages(Request $request)
    {
        try {
            $taskId = $request->task_id;
            $task = Task::with('deliveryImage')->find($taskId);
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ]);
            }

            $html = view('delivery_orders.task_images', compact('task'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading images'
            ]);
        }
    }

    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $deliveryOrder = $this->deliveryOrderRepository->find($id);

        if (empty($deliveryOrder)) {
            Flash::error('Delivery Order not found');
            return redirect(route('deliveryOrders.index'));
        }

        // Eager load tasks with their relationships
        $deliveryOrder->load([
            'tasks.driver',
            'tasks.deliveryImage'
        ]);

        return view('delivery_orders.show')->with('deliveryOrder', $deliveryOrder);
    }

    /**
     * Show the form for editing the specified DeliveryOrder.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $deliveryOrder = $this->deliveryOrderRepository->find($id);

        if (empty($deliveryOrder)) {
            Flash::error('Delivery Order not found');

            return redirect(route('deliveryOrders.index'));
        }

        return view('delivery_orders.edit')->with('deliveryOrder', $deliveryOrder);
    }

    /**
     * Update the specified DeliveryOrder in storage.
     *
     * @param int $id
     * @param UpdateDeliveryOrderRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateDeliveryOrderRequest $request)
    {
        $id = Crypt::decrypt($id);
        $deliveryOrder = $this->deliveryOrderRepository->find($id);

        if (empty($deliveryOrder)) {
            Flash::error('Delivery Order not found');

            return redirect(route('deliveryOrders.index'));
        }
        $input = $request->all();
        $deliveryOrder = $this->deliveryOrderRepository->update($request->all(), $id);

        Flash::success('Delivery Order updated successfully.');

        return redirect(route('deliveryOrders.index'));
    }

    /**
     * Remove the specified DeliveryOrder from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $deliveryOrder = $this->deliveryOrderRepository->find($id);

        if (empty($deliveryOrder)) {
            Flash::error('Delivery Order not found');

            return redirect(route('deliveryOrders.index'));
        }

        $this->deliveryOrderRepository->delete($id);

        Flash::success('Delivery Order deleted successfully.');

        return redirect(route('deliveryOrders.index'));
    }

    public function print($id, $task_id = null)
    {
        $id = Crypt::decrypt($id) ;

        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $company = $deliveryOrder->company;
        if($task_id){
            $task_id = Crypt::decrypt($task_id) ;
            $task = Task::find($task_id);
            if($task){
                $deliveryOrder->this_load = $task->this_load;
                $deliveryOrder->task_number = $task->task_number;
            }
        }
        try{
            $pdf = Pdf::loadView('delivery_orders.print', [
                'deliveryOrder' => $deliveryOrder,
                'company' => $company
            ]);

            return $pdf->setPaper('A4', 'portrait')
                ->setOptions([
                    'isPhpEnabled' => true, 
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Arial'
                ])
                ->stream('delivery_order_' . $deliveryOrder->dono . '.pdf');
        }
        catch(Exception $e){
            abort(404);
        }

    }

    public function masssave(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        
        $result = DB::select('CALL spViewDeliveryOrder('.Auth::id().',\'DeliveryOrders\',\''.implode(",",$ids).'\')')[0]->ID;

        return $result;
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        
        $count = DeliveryOrder::destroy($ids);
        $claim = Claim::whereIn('deliveryorder_id',$ids)->where('editable',0)->delete();

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];
        
        $count = DeliveryOrder::whereIn('id',$ids)->update(['status'=>$status]);

        foreach($ids as $id){
            $deliveryOrder = $this->deliveryOrderRepository->find($id);
    
            $claim_amount = $deliveryOrder->tol + $deliveryOrder->fees;
            $claim_no = 'DO_'.date_format(date_create($deliveryOrder->date),"Ymd").'_'.$deliveryOrder->dono;
            $claim_driverid = $deliveryOrder->driver_id;
            $claim_date = date_create($deliveryOrder->date);
            $claim_deliveryorderid = $deliveryOrder->id;
            $claim_description = 'Tol+Loading/Unloading Fees';

            if($status==1){
                if($claim_amount > 0){
                    $claim = Claim::where('deliveryorder_id',$claim_deliveryorderid)->where('editable',0);
        
                    if($claim->count() == 0){
                        $claimnew = Claim::where('deliveryorder_id',$claim_deliveryorderid)
                        ->where('editable',0)
                        ->create(['date'=>$claim_date,'no'=>$claim_no,'amount'=>$claim_amount,'driver_id'=>$claim_driverid,'description'=>$claim_description,'deliveryorder_id'=>$claim_deliveryorderid,'editable'=>0]);
                    }else{
                        $claimupdate = Claim::where('deliveryorder_id',$claim_deliveryorderid)
                        ->where('editable',0)
                        ->update(['date'=>$claim_date,'no'=>$claim_no,'amount'=>$claim_amount,'driver_id'=>$claim_driverid,'description'=>$claim_description,'deliveryorder_id'=>$claim_deliveryorderid,'editable'=>0]);
                    }
                }else{
                    $claim = Claim::where('deliveryorder_id',$claim_deliveryorderid)->where('editable',0)->delete();
                }
            }else{
                $claim = Claim::where('deliveryorder_id',$claim_deliveryorderid)->where('editable',0)->delete();
            }

        }

        return $count;
    }

    public function getDriverLorry(Request $request)
    {
        $data = $request->all();
        $driver_id = $data['driver_id'];
        $result = DB::select('select lorry_id from drivers where id='.$driver_id)[0];
        return $result;
    }

    public function getDriverInfo(Request $request)
    {
        $data = $request->all();
        $driver_id = $data['driver_id'];
        $result = DB::select('select name,ic,grouping,caption from drivers where id='.$driver_id)[0];
        return $result;
    }

    public function getLorryInfo(Request $request)
    {
        $data = $request->all();

        $lorry_id = $data['lorry_id'];
        $vendor_id = $data['vendor_id'];
        $result = DB::select('select l.lorryno,l.type,l.weightagelimit,coalesce(cbv.commissionlimit,l.commissionlimit) commissionlimit,coalesce(cbv.commissionpercentage,l.commissionpercentage) commissionpercentage from lorrys l left join commissionbyvendors cbv on  cbv.lorry_id = l.id and cbv.vendor_id='.$vendor_id.' where l.id='.$lorry_id)[0];
        return $result;
    }

    // public function getClaimInfo(Request $request)
    // {
    //     $data = $request->all();

    //     $dokey = $data['dokey'];
    //     $result = DB::select('select DATE_FORMAT(date, "%d-%m-%Y") as date,no "number",description,format(amount,2) as amount,case when status="1" then "Paid" else "Unpaid" end status from claims where deliveryorder_id='.$dokey);
    //     return $result;
    // }

    // public function getTotalAdvance(Request $request)
    // {
    //     $data = $request->all();

    //     $driverkey = $data['driverkey'];
    //     $result = DB::select('select DATE_FORMAT(date, "%d-%m-%Y") as date,no "number",description,format(amount,2) as amount,case when status="1" then "Paid" else "Unpaid" end status from advances where status = 0 and driver_id='.$driverkey);
    //     return $result;
    // }

    // public function getBillingRate(Request $request)
    // {
    //     $data = $request->all();
    //     $item_id = $data['item_id'];
    //     $vendor_id = $data['vendor_id'];
    //     $source_id = $data['source_id'];
    //     $destinate_id = $data['destinate_id'];
    //     $result = DB::select('select \'default\' as \'range\', format(i.billingrate,2) as \'billingrate\' from items i where i.id = \''.$item_id.'\' UNION (select concat(p.minrange,\' ~ \',p.maxrange) as \'range\', format(p.billingrate,2) as \'billingrate\' from prices p where p.item_id = \''.$item_id.'\' and p.vendor_id = \''.$vendor_id.'\' and p.source_id = \''.$source_id.'\' and p.destinate_id = \''.$destinate_id.'\' order by p.minrange);');
    //     return $result;
    // }

    // public function getCommissionRate(Request $request)
    // {
    //     $data = $request->all();
    //     $item_id = $data['item_id'];
    //     $vendor_id = $data['vendor_id'];
    //     $source_id = $data['source_id'];
    //     $destinate_id = $data['destinate_id'];
    //     $result = DB::select('select \'default\' as \'range\', format(i.commissionrate,2) as \'commissionrate\' from items i where i.id = \''.$item_id.'\' UNION (select concat(p.minrange,\' ~ \',p.maxrange) as \'range\', format(p.commissionrate,2) as \'commissionrate\' from prices p where p.item_id = \''.$item_id.'\' and p.vendor_id = \''.$vendor_id.'\' and p.source_id = \''.$source_id.'\' and p.destinate_id = \''.$destinate_id.'\' order by p.minrange);');
    //     return $result;
    // }

    // public function getBillingRateInfo(Request $request)
    // {
    //     $data = $request->all();
    //     $dokey = $data['dokey'];
    //     $param = DB::select('select dos.item_id, dos.vendor_id, dos.source_id, dos.destinate_id from deliveryorders dos where dos.id = \''.$dokey.'\';');
    //     if(sizeof($param) == 0){
    //         return response()->json(['message' => 'Develiery Order not found.'], 500);
    //     }
    //     $item_id = $param[0]->item_id;
    //     $vendor_id = $param[0]->vendor_id;
    //     $source_id = $param[0]->source_id;
    //     $destinate_id = $param[0]->destinate_id;
    //     $result = DB::select('select \'default\' as \'range\', format(i.billingrate,2) as \'billingrate\' from items i where i.id = \''.$item_id.'\' UNION (select concat(p.minrange,\' ~ \',p.maxrange) as \'range\', format(p.billingrate,2) as \'billingrate\' from prices p where p.item_id = \''.$item_id.'\' and p.vendor_id = \''.$vendor_id.'\' and p.source_id = \''.$source_id.'\' and p.destinate_id = \''.$destinate_id.'\' order by p.minrange);');
    //     return $result;
    // }

    // public function getCommissionRateInfo(Request $request)
    // {
    //     $data = $request->all();
    //     $dokey = $data['dokey'];
    //     $param = DB::select('select dos.item_id, dos.vendor_id, dos.source_id, dos.destinate_id from deliveryorders dos where dos.id = \''.$dokey.'\';');
    //     if(sizeof($param) == 0){
    //         return response()->json(['message' => 'Develiery Order not found.'], 500);
    //     }
    //     $item_id = $param[0]->item_id;
    //     $vendor_id = $param[0]->vendor_id;
    //     $source_id = $param[0]->source_id;
    //     $destinate_id = $param[0]->destinate_id;
    //     $result = DB::select('select \'default\' as \'range\', format(i.commissionrate,2) as \'commissionrate\' from items i where i.id = \''.$item_id.'\' UNION (select concat(p.minrange,\' ~ \',p.maxrange) as \'range\', format(p.commissionrate,2) as \'commissionrate\' from prices p where p.item_id = \''.$item_id.'\' and p.vendor_id = \''.$vendor_id.'\' and p.source_id = \''.$source_id.'\' and p.destinate_id = \''.$destinate_id.'\' order by p.minrange);');
    //     return $result;
    // }
}
