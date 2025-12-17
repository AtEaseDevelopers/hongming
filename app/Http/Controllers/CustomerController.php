<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Repositories\CustomerRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use App\Models\SpecialPrice;
use App\Models\foc;
use App\Models\Assign;
use Illuminate\Support\Facades\Session;
use Exception;

class CustomerController extends AppBaseController
{
    /** @var CustomerRepository $customerRepository*/
    private $customerRepository;

    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepository = $customerRepo;
    }

    /**
     * Display a listing of the Customer.
     *
     * @param CustomerDataTable $customerDataTable
     *
     * @return Response
     */
    public function index(CustomerDataTable $customerDataTable)
    {
        return $customerDataTable->render('customers.index');
    }

    /**
     * Show the form for creating a new Customer.
     *
     * @return Response
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created Customer in storage.
     *
     * @param CreateCustomerRequest $request
     *
     * @return Response
     */
    public function store(CreateCustomerRequest $request)
    {
        $input = $request->all();
        $input['group'] = implode(",",$input['group'] ?? []);
        
        // Handle destination data - ensure empty strings are converted to null
        $input['place_name'] = !empty($input['place_name']) ? $input['place_name'] : null;
        $input['place_address'] = !empty($input['place_address']) ? $input['place_address'] : null;
        $input['place_latitude'] = !empty($input['place_latitude']) ? $input['place_latitude'] : null;
        $input['place_longitude'] = !empty($input['place_longitude']) ? $input['place_longitude'] : null;
        $input['google_place_id'] = !empty($input['google_place_id']) ? $input['google_place_id'] : null;
        $input['destinate_id'] = !empty($input['destinate_id']) ? $input['destinate_id'] : null;

        $customer = $this->customerRepository->create($input);

        Flash::success(__('customers.customer_saved_successfully'));

        return redirect(route('customers.index'));
    }

    /**
     * Display the specified Customer.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            Flash::error(__('customers.invalid_customer_id'));
            return redirect(route('customers.index'));
        }
        
        $customer = $this->customerRepository->find($id);

        if (empty($customer)) {
            Flash::error(__('customers.customer_not_found'));
            return redirect(route('customers.index'));
        }

        $customer->group = DB::table('codes')
            ->where('codes.code', '=', 'customer_group')
            ->whereRaw('find_in_set(codes.value, "'.$customer->group.'")')
            ->selectRaw('GROUP_CONCAT(codes.description) as group_descr')
            ->get()->first()->group_descr;
        
        return view('customers.show')->with('customer', $customer);
    }

    /**
     * Show the form for editing the specified Customer.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            Flash::error(__('customers.invalid_customer_id'));
            return redirect(route('customers.index'));
        }
        
        $customer = $this->customerRepository->find($id);

        if (empty($customer)) {
            Flash::error(__('customers.customer_not_found'));
            return redirect(route('customers.index'));
        }
        
        return view('customers.edit')->with('customer', $customer);
    }

    /**
     * Update the specified Customer in storage.
     *
     * @param int $id
     * @param UpdateCustomerRequest $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            Flash::error(__('customers.invalid_customer_id'));
            return redirect(route('customers.index'));
        }
        
        $customer = $this->customerRepository->find($id);

        if (empty($customer)) {
            Flash::error(__('customers.customer_not_found'));
            return redirect(route('customers.index'));
        }

        // Create custom validation rules
        $rules = [
            'code' => 'required|string|max:255|unique:customers,code,' . $id,
            'company' => 'required|string|max:255',
            'paymentterm' => 'nullable',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:65535',
            'status' => 'required',
            'sst' => 'nullable|string|max:255',
            'tin' => 'nullable|string|max:255',
            'place_name' => 'nullable|string|max:255',
            'place_address' => 'nullable|string',
            'place_latitude' => 'nullable|numeric',
            'place_longitude' => 'nullable|numeric',
            'google_place_id' => 'nullable|string|max:255',
            'destinate_id' => 'nullable|string|max:255',
        ];

        // Validate the request
        $validatedData = $request->validate($rules);

        $input = $request->all();
        $input['group'] = implode(",",$input['group'] ?? []);
        
        // Handle destination data - ensure empty strings are converted to null
        $input['place_name'] = !empty($input['place_name']) ? $input['place_name'] : null;
        $input['place_address'] = !empty($input['place_address']) ? $input['place_address'] : null;
        $input['place_latitude'] = !empty($input['place_latitude']) ? $input['place_latitude'] : null;
        $input['place_longitude'] = !empty($input['place_longitude']) ? $input['place_longitude'] : null;
        $input['google_place_id'] = !empty($input['google_place_id']) ? $input['google_place_id'] : null;
        $input['destinate_id'] = !empty($input['destinate_id']) ? $input['destinate_id'] : null;

        $customer = $this->customerRepository->update($input, $id);

        Flash::success(__('customers.customer_updated_successfully'));

        return redirect(route('customers.index'));
    }

    /**
     * Remove the specified Customer from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            Flash::error(__('customers.invalid_customer_id'));
            return redirect(route('customers.index'));
        }
        
        $customer = $this->customerRepository->find($id);

        if (empty($customer)) {
            Flash::error(__('customers.customer_not_found'));
            return redirect(route('customers.index'));
        }

        $Invoice = Invoice::where('customer_id',$id)->get()->toArray();
        if(count($Invoice)>0){
            Flash::error('Unable to delete '.$customer->name.', '.$customer->name.' is being used in Invoice');
            return redirect(route('customers.index'));
        }

        $SpecialPrice = SpecialPrice::where('customer_id',$id)->get()->toArray();
        if(count($SpecialPrice)>0){
            Flash::error('Unable to delete '.$customer->name.', '.$customer->name.' is being used in Special Price');
            return redirect(route('customers.index'));
        }

        $foc = foc::where('customer_id',$id)->get()->toArray();
        if(count($foc)>0){
            Flash::error('Unable to delete '.$customer->name.', '.$customer->name.' is being used in Foc');
            return redirect(route('customers.index'));
        }

        $Assign = Assign::where('customer_id',$id)->get()->toArray();
        if(count($Assign)>0){
            Flash::error('Unable to delete '.$customer->name.', '.$customer->name.' is being used in Assign');
            return redirect(route('customers.index'));
        }

        $this->customerRepository->delete($id);

        Flash::success($customer->company.__('customers.deleted_successfully'));

        return redirect(route('customers.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];

        $count = 0;

        foreach ($ids as $id) {

            $Invoice = Invoice::where('customer_id',$id)->get()->toArray();
            if(count($Invoice)>0){
                continue;
            }

            $SpecialPrice = SpecialPrice::where('customer_id',$id)->get()->toArray();
            if(count($SpecialPrice)>0){
                continue;
            }

            $foc = foc::where('customer_id',$id)->get()->toArray();
            if(count($foc)>0){
                continue;
            }

            $Assign = Assign::where('customer_id',$id)->get()->toArray();
            if(count($Assign)>0){
                continue;
            }

            $customer = $this->customerRepository->find($id);

            $count = $count + Customer::destroy($id);
        }

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];

        $count = Customer::whereIn('id',$ids)->update(['status'=>$status]);

        return $count;
    }
    
    public function syncXero(Request $req)
    {
        try {
            $redirect_uri = config('app.url') . '/customers/sync-xero';
            $xero = new XeroController($redirect_uri);

            if ($req->has('ids')) {
                $ids = explode(',', $req->ids);
                Session::put('ids_to_sync_xero', $ids);
            }
            // Get Xero's access token
            if ($req->has('code')) {
                $res = $xero->getToken($req->code);
                if (!$res->ok()) {
                    throw new Exception('Failed to get xero access token.');
                }
            }
            // Xero auth
            $res = $xero->auth();
            if ($res !== true) {
                return $res;
            }
            // Sync customers
            $ids = Session::get('ids_to_sync_xero');
            $customers = (new CustomerDataTable)->query(new Customer)->whereIn('customers.id', $ids)->get();
            for ($i = 0; $i < count($customers) ;$i++) {
                $res = $xero->createContact($customers[$i]->company);

                if (!$res->ok()) {  
                    throw new Exception('Failed to sync customer.');
                }
            }
            
            Flash::success('Customers synced to Xero.');
            return redirect(route('customers.index'));
        } catch (\Throwable $th) {
            report($th);

            Flash::error('Something went wrong. Please contact administator.');
            return redirect(route('customers.index'));
        }
    }

    /**
     * Get customer destination data for Delivery Order form
     *
     * @param Request $request
     * @return Response
     */
    public function getDestination(Request $request)
    {
        $customerId = $request->customer_id;
        
        $customerId = $request->customer_id;
        $customer = Customer::find($customerId);
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ]);
        }
        
        // Check if customer has complete destination data
        $hasDestination = !empty($customer->place_name) && 
                        !empty($customer->place_address) && 
                        !empty($customer->place_latitude) && 
                        !empty($customer->place_longitude);
    
        
        return response()->json([
            'success' => true,
            'has_destination' => $hasDestination,
            'place_name' => $customer->place_name,
            'place_address' => $customer->place_address,
            'place_latitude' => $customer->place_latitude,
            'place_longitude' => $customer->place_longitude,
            'google_place_id' => $customer->google_place_id,
            'destinate_id' => $customer->destinate_id,
        ]);
    }
}