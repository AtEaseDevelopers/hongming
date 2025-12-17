<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Datetime;
use App\Models\DeliveryOrder;
use App\Models\DeliveryImage;
use App\Models\DriverCheckIn;
use App\Models\DriverNotifications;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Kelindan;
use App\Models\Lorry;
use App\Models\Task;
use App\Models\TaskTransfer;
use App\Models\Assign;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SpecialPrice;
use App\Models\Customer;
use App\Models\InvoicePayment;
use App\Models\InvoiceDetail;
use App\Models\Code;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use App\Models\foc;
use App\Models\DriverLocation;
use App\Models\Language;
use App\Models\MobileTranslationVersion;
use App\Models\MobileTranslation;
use Illuminate\Support\Facades\Crypt; 

class DriverController extends Controller
{
    protected $message_separator = "|";
    //Auth
    public function login(Request $request){
        // return "000002" <=> "000002";
        try{
            //validation
            $validator = Validator::make($request->all(), [
                'employeeid' => 'required|string',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $data = $request->all();
            $driver = Driver::where('employeeid', $data['employeeid'])->where('password', $data['password'])->first();
            if(!empty($driver)){
                $session = $driver->session;
                $driver->session = session_create_id();
                $driver->save();

                $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
                if(!empty($trip)){
                    if($trip->type == 2){
                        $status = false;
                    }else{
                        $status = true;
                    }
                }else{
                    $status = false;
                }

                $colorcode = Code::where('code','color_code_'.date("D"))->first()['value'] ?? '';

                if($status){
                    if($session == null){
                        return response()->json([
                                'result' => true,
                                'message' => __LINE__.$this->message_separator.'api.message.login_successfully',
                                'data' => [
                                    'driver' => $driver,
                                    'trip' => [
                                        'status' => true,
                                        'trip' => $trip
                                    ],
                                'colorcode' => $colorcode
                            ]
                        ], 200);
                    }else{
                        return response()->json([
                                'result' => true,
                                'message' => __LINE__.$this->message_separator.'api.message.previous_session_override',
                                'data' => [
                                    'driver' => $driver,
                                    'trip' => [
                                        'status' => true,
                                        'trip' => $trip
                                    ],
                                'colorcode' => $colorcode
                            ]
                        ], 200);
                    }
                }else{
                    if($session == null){
                        return response()->json([
                                'result' => true,
                                'message' => __LINE__.$this->message_separator.'api.message.login_successfully',
                                'data' => [
                                    'driver' => $driver,
                                    'trip' => [
                                        'status' => false
                                    ],
                                'colorcode' => $colorcode
                            ]
                        ], 200);
                    }else{
                        return response()->json([
                                'result' => true,
                                'message' => __LINE__.$this->message_separator.'api.message.previous_session_override',
                                'data' => [
                                    'driver' => $driver,
                                    'trip' => [
                                        'status' => false
                                    ],
                                'colorcode' => $colorcode
                            ]
                        ], 200);
                    }
                }

            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_credential',
                    'data' => null
                ], 401);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function logout(Request $request){
        try{
            //validation
            $validator = Validator::make($request->all(), [
                'session' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $data = $request->all();
            $driver = Driver::where('session', $data['session'])->first();
            if(!empty($driver)){
                $driver->session = NULL;
                $driver->save();
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.login_successfully',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function session(Request $request){
        try{
            //validation
            $validator = Validator::make($request->all(), [
                'session' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $data = $request->all();
            $driver = Driver::where('session', $data['session'])->first();
            $colorcode = Code::where('code','color_code_'.date("D"))->first()['value'] ?? '';
            if(!empty($driver)){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.session_found',

                    'data' => $driver,
                    'colorcode' => $colorcode
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function location(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
    
            $validator = Validator::make($request->all(), [
                'lorry_id' => 'required|numeric',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
             $lorry = Lorry::where('id', $data['lorry_id'])->first();
            if (empty($lorry)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'api.message.invalid_lorry',
                    'data' => null
                ], 400);
            }
            
            //process
            $driverLocation = new DriverLocation();
            $driverLocation->driver_id = $driver->id;
            $driverLocation->lorry_id = $data['lorry_id'];
            $driverLocation->latitude = $data['latitude'];
            $driverLocation->longitude = $data['longitude'];
            $driverLocation->date = date("Y-m-d H:i:s"); // Store current datetime
            $driverLocation->save();

            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.driver_location_had_been_updated_successfully',
                'data' => $driverLocation
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    //Trip
    public function checktrip(Request $request){
        try{
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //process
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => [
                            'status' => false
                        ]
                    ], 200);
                }else{
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_started',
                        'data' => [
                            'status' => true,
                            'trip' => $trip
                        ]
                    ], 200);
                }
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => [
                        'status' => false
                    ]
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function starttrip(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'lorry_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
           
            $lorry = Lorry::where('id', $data['lorry_id'])->first();
            if(empty($lorry)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_lorry',
                    'data' => null
                ], 400);
            }
            //process
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    //insert trip
                    $newtrip = new Trip();
                    $newtrip->driver_id = $driver->id;
                    $newtrip->lorry_id = $data['lorry_id'];
                    $newtrip->type = 1;
                    $newtrip->date = date("Y-m-d H:i:s");
                    $newtrip->save();
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_started_successfully',
                        'data' => $newtrip
                    ], 200);
                }else{
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_started',
                        'data' => null
                    ], 401);
                }
            }else{
                //insert trip
                $newtrip = new Trip();
                $newtrip->driver_id = $driver->id;
                $newtrip->lorry_id = $data['lorry_id'];
                $newtrip->type = 1;
                $newtrip->date = date("Y-m-d H:i:s");
                $newtrip->save();
                
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_started_successfully',
                    'data' => $newtrip
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function endtrip(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'lorry_id' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $lorry = Lorry::where('id', $data['lorry_id'])->first();
            if(empty($lorry)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_lorry',
                    'data' => null
                ], 400);
            }
            $task = Task::where('driver_id', $driver->id)->where('date',date('Y-m-d'))->whereIn('status',1)->first();
            if($task){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'You have unfinished task, please complete it before ending the trip.1',
                    'data' => null
                ], 400);
            }

            //process
            DB::beginTransaction();
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    DB::rollback();
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'Trip had not started',
                        'data' => null
                    ], 400);
                }else{
                    $newtrip = new Trip();
                    $newtrip->driver_id = $driver->id;
                    $newtrip->lorry_id = $data['lorry_id'];
                    $newtrip->type = 2;
                    $newtrip->date = date("Y-m-d H:i:s");
                    $newtrip->save();

                    DB::commit();
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'Trip had been ended successfully',
                        'data' => $newtrip
                    ], 200);
                }
            }else{
                DB::rollback();
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'Trip had not started',
                    'data' => null
                ], 400);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function trip(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $validator = Validator::make($request->all(), [
            'kelindan_id' => 'required|numeric',
            'lorry_id' => 'required|numeric',
            'type' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        // $kelindan = Kelindan::where('id', $data['kelindan_id'])->first();
        // if(empty($kelindan)){
        //     return response()->json([
        //         'result' => false,
        //         'message' => __LINE__.$this->message_separator.'Invalid Kelindan',
        //         'data' => null
        //     ], 400);
        // }
        $lorry = Lorry::where('id', $data['lorry_id'])->first();
        if(empty($lorry)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_lorry',
                'data' => null
            ], 400);
        }
        if(!($data['type'] == 1 || $data['type'] == 2)){
            return response()->json([
               'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_type',
                'data' => null
            ], 400);
        }
        //process
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if($data['type'] == 1){
            if(!empty($trip)){
                if($trip->type == 2){
                    //insert trip
                    $newtrip = new Trip();
                    $newtrip->driver_id = $driver->id;
                    $newtrip->kelindan_id = $data['kelindan_id'];
                    $newtrip->lorry_id = $data['lorry_id'];
                    $newtrip->type = 1;
                    $newtrip->date = date("Y-m-d H:i:s");
                    $newtrip->save();
                    //generate task
                    $assigns = Assign::where('driver_id', $driver->id)->orderby('sequence','asc')->get()->toarray();
                    $count = 1;
                    foreach($assigns as $assign){
                        $task = new Task();
                        $task->date = date("Y-m-d");
                        $task->driver_id = $driver->id;
                        $task->customer_id = $assign['customer_id'];
                        $task->sequence = $count;
                        $task->status = 0;
                        $task->save();
                        $count = $count + 1;
                    }
                    $invoices = Invoice::where('driver_id', $driver->id)->where('status',0)->where('date',date('Y-m-d'))->get()->toarray();
                    foreach($invoices as $invoice){
                        $task = new Task();
                        $task->date = date("Y-m-d");
                        $task->driver_id = $driver->id;
                        $task->customer_id = $invoice['customer_id'];
                        $task->invoice_id = $invoice['id'];
                        $task->sequence = $count;
                        $task->status = 0;
                        $task->save();
                        $count = $count + 1;
                    }
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_started_successfully',
                        'data' => $newtrip
                    ], 200);
                }else{
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_started',
                        'data' => null
                    ], 401);
                }
            }else{
                //insert trip
                $newtrip = new Trip();
                $newtrip->driver_id = $driver->id;
                $newtrip->kelindan_id = $data['kelindan_id'];
                $newtrip->lorry_id = $data['lorry_id'];
                $newtrip->type = 1;
                $newtrip->date = date("Y-m-d H:i:s");
                $newtrip->save();
                //generate task
                $assigns = Assign::where('driver_id', $driver->id)->orderby('sequence','asc')->get()->toarray();
                $count = 1;
                foreach($assigns as $assign){
                    $task = new Task();
                    $task->date = date("Y-m-d");
                    $task->driver_id = $driver->id;
                    $task->customer_id = $assign['customer_id'];
                    $task->sequence = $count;
                    $task->status = 0;
                    $task->save();
                    $count = $count + 1;
                }
                $invoices = Invoice::where('driver_id', $driver->id)->where('status',0)->where('date',date('Y-m-d'))->get()->toarray();
                foreach($invoices as $invoice){
                    $task = new Task();
                    $task->date = date("Y-m-d");
                    $task->driver_id = $driver->id;
                    $task->customer_id = $invoice['customer_id'];
                    $task->invoice_id = $invoice['id'];
                    $task->sequence = $count;
                    $task->status = 0;
                    $task->save();
                    $count = $count + 1;
                }
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_started_successfully',
                    'data' => $newtrip
                ], 200);
            }
        }else if($data['type'] == 2){
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 401);
                }else{
                    $newtrip = new Trip();
                    $newtrip->driver_id = $driver->id;
                    $newtrip->kelindan_id = $data['kelindan_id'];
                    $newtrip->lorry_id = $data['lorry_id'];
                    $newtrip->type = 2;
                    $newtrip->date = date("Y-m-d H:i:s");
                    $newtrip->save();
                    //cancelled task
                    $task = Task::where('driver_id', $driver->id)->where('date',date('Y-m-d'))->whereIn('status',[0,1])->update(['status' => 9]);
                    return response()->json([
                        'result' => true,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_been_ended_successfully',
                        'data' => $newtrip
                    ], 200);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 401);
            }
        }
    }

    //Kelindan
    public function getkelindan(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //process
            // $kelindan = Kelindan::where('status',1)->select('id','name')->get()->toarray();
            $kelindan = DB::select("select k.id, k.name from kelindans k left join ( select driver_id, type, kelindan_id from trips where id in ( select max(id) as id from trips group by driver_id ) ) b on k.id = b.kelindan_id and b.type = 1 where b.kelindan_id is null;");
            if(count($kelindan) != 0){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.kelindan_found',
                    'data' => $kelindan
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.kelindan_not_found',
                    'data' => null
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    //Lorry
    public function getlorry(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //process
            $lorry = Lorry::available() // This uses the scopeAvailable method
                ->select('id', 'lorryno', 'jpj_registration')
                ->get()
                ->toArray();

            if(count($lorry) != 0){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.lorry_found',
                    'data' => $lorry
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.lorry_not_found',
                    'data' => null
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    //Task
    public function gettask(Request $request)
    {
        try {
            $data = $request->all();
            
            // Check driver session
            $driver = Driver::where('session', $request->header('session'))->first();
            if (empty($driver)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                    'data' => null
                ], 401);
            }

            // Check if the driver has checkin or not 
            $driverCheckIn = DriverCheckIn::where('driver_id', $driver->id)
                ->where('type', DriverCheckIn::TYPE_CHECK_IN) 
                ->whereDate('check_time', now()->format('Y-m-d'))
                ->orderBy('check_time', 'desc') 
                ->first();

            if (empty($driverCheckIn)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'Driver havent check in yet. Please check in first.',
                    'data' => null
                ], 400);
            }

            // Get today's tasks for the driver with delivery images
            $tasks = Task::where('lorry_id', $driverCheckIn->lorry_id)
                ->where('date', date('Y-m-d'))
                ->with([
                    'deliveryOrder.customer', 
                    'deliveryOrder.product:id,code,name',
                ])
                ->get();

            // Process tasks and add additional data
            $processedTasks = [];
            $hasTasks = $tasks->count() > 0;

            if ($hasTasks) {
                foreach ($tasks as $task) {
                    // Convert status to string BEFORE converting to array
                    if ($task->deliveryOrder) {
                        $task->deliveryOrder->status = DeliveryOrder::$statusOptions[$task->deliveryOrder->status] ?? 'Unknown';
                    }
                    
                    if ($task->deliveryOrder && $task->deliveryOrder->customer) {
                        $task->deliveryOrder->customer->status = ($task->deliveryOrder->customer->status == 1) ? 'Active' : 'Inactive';
                    }

                    // Now convert to array
                    $taskData = $task->toArray();
                    
                    // Add delivery images only if task status is "Completed"
                    if ($task->status === 'Completed' && $task->deliveryImage) {
                        $taskData['delivery_images'] = [
                            'delivery_order_image' => [
                                'url' => $task->deliveryImage->delivery_order_image_url,
                                'path' => $task->deliveryImage->delivery_order_image_path,
                            ],
                            'proof_of_delivery_image' => [
                                'url' => $task->deliveryImage->proof_of_delivery_image_url,
                                'path' => $task->deliveryImage->proof_of_delivery_image_path,
                            ]
                        ];
                    } else {
                        $taskData['delivery_images'] = null;
                    }
                    
                    // Add customer credit and product information
                    if (isset($taskData['deliveryOrder']['customer']['id'])) {
                        $customerId = $taskData['deliveryOrder']['customer']['id'];
                        
                        // Get company group information
                        if (isset($taskData['deliveryOrder']['customer']['group']) && !empty($taskData['deliveryOrder']['customer']['group'])) {
                            $groupId = explode(',', $taskData['deliveryOrder']['customer']['group'])[0];
                            $groupCompany = DB::table('companies')
                                ->where('companies.group_id', $groupId)
                                ->select('companies.*')
                                ->first();

                            if ($groupCompany) {
                                $taskData['deliveryOrder']['customer']['groupcompany'] = (array) $groupCompany;
                            } else {
                                $taskData['deliveryOrder']['customer']['groupcompany'] = null;
                            }
                        } else {
                            $taskData['deliveryOrder']['customer']['groupcompany'] = null;
                        }
                    }
                    
                    // Add timing information only for tasks with status "Delivering"
                    if ($task->getStatusValue() === Task::STATUS_DELIVERING) {
                        $taskData['countdown_minutes'] = $task->getCountdownInMinutes();
                        $taskData['countdown_formatted'] = $task->getCountdownFormatted();
                        $taskData['start_time'] = $task->start_time;
                        $taskData['estimated_completion_time'] = $task->getEstimatedCompletionTime();
                    } else {
                        $taskData['countdown_minutes'] = null;
                        $taskData['countdown_formatted'] = null;
                        $taskData['start_time'] = null;
                        $taskData['estimated_completion_time'] = null;
                    }

                    // Add is_late flag
                    $taskData['is_late'] = $task->is_late ?? false;

                    // Generate PDF URL for delivery order
                    $pdfContent = null;
                    if (isset($taskData['delivery_order_id'])) {
                        $pdfContent = $this->generateDeliveryOrderPDF($taskData['delivery_order_id'], $taskData['id']);
                    }
                    $taskData['pdf_url'] = $pdfContent;
                    
                    $processedTasks[] = $taskData;
                }
            }
        
            if ($hasTasks) {
                return response()->json([
                    'result' => true,
                    'message' => __LINE__ . $this->message_separator . 'api.message.task_found',
                    'data' => [
                        'tasks' => $processedTasks,
                    ]
                ], 200);
            } else {
                return response()->json([
                    'result' => true,
                    'message' => __LINE__ . $this->message_separator . 'api.message.task_not_found',
                    'data' => [
                        'tasks' => [],
                    ]
                ], 200);
            }

        } catch (Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function gettaskDetails(Request $request)
    {
        try {
            $data = $request->all();
            
            // Check driver session
            $driver = Driver::where('session', $request->header('session'))->first();
            if (empty($driver)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'api.message.invalid_session',
                    'data' => null
                ], 401);
            }

            // Check if driver has an active trip
            $trip = Trip::where('driver_id', $driver->id)
                ->where('type', 1) 
                ->orderBy('date', 'desc')
                ->first();

            if (empty($trip)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }

            // Get today's tasks for the driver with delivery images
            $tasks = Task::where('driver_id', $driver->id)
                ->where('id', $data['task_id'])
                ->with([
                    'deliveryOrder.customer', 
                    'deliveryOrder.product:id,code,name',
                ])
                ->first();
                
            if (empty($tasks)) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__ . $this->message_separator . 'api.message.task_not_found',
                    'data' => [
                        'task' => null,
                    ]
                ], 200);
            }

            // Convert status to string BEFORE converting to array
            if ($tasks->deliveryOrder) {
                $tasks->deliveryOrder->status = DeliveryOrder::$statusOptions[$tasks->deliveryOrder->status] ?? 'Unknown';
            }
            
            if ($tasks->deliveryOrder && $tasks->deliveryOrder->customer) {
                $tasks->deliveryOrder->customer->status = ($tasks->deliveryOrder->customer->status == 1) ? 'Active' : 'Inactive';
            }

            // Now convert to array
            $taskData = $tasks->toArray();

            // Add delivery images only if task status is "Completed"
            if ($tasks->status === 'Completed' && $tasks->deliveryImage) {
                $taskData['delivery_images'] = [
                    'delivery_order_image' => [
                        'url' => $tasks->deliveryImage->delivery_order_image_url,
                        'path' => $tasks->deliveryImage->delivery_order_image_path,
                    ],
                    'proof_of_delivery_image' => [
                        'url' => $tasks->deliveryImage->proof_of_delivery_image_url,
                        'path' => $tasks->deliveryImage->proof_of_delivery_image_path,
                    ]
                ];
            } else {
                $taskData['delivery_images'] = null;
            }
                
            // Add customer credit and product information
            if (isset($taskData['deliveryOrder']['customer']['id'])) {
                $customerId = $taskData['deliveryOrder']['customer']['id'];

                // Get company group information
                if (isset($taskData['deliveryOrder']['customer']['group']) && !empty($taskData['deliveryOrder']['customer']['group'])) {
                    $groupId = explode(',', $taskData['deliveryOrder']['customer']['group'])[0];
                    $groupCompany = DB::table('companies')
                        ->where('companies.group_id', $groupId)
                        ->select('companies.*')
                        ->first();

                    if ($groupCompany) {
                        $taskData['deliveryOrder']['customer']['groupcompany'] = (array) $groupCompany;
                    } else {
                        $taskData['deliveryOrder']['customer']['groupcompany'] = null;
                    }
                } else {
                    $taskData['deliveryOrder']['customer']['groupcompany'] = null;
                }
            }

            // Add timing information only for tasks with status "Delivering"
            if ($tasks->getStatusValue() === Task::STATUS_DELIVERING) {
                $taskData['countdown_minutes'] = $tasks->getCountdownInMinutes();
                $taskData['countdown_formatted'] = $tasks->getCountdownFormatted();
                $taskData['estimated_completion_time'] = $tasks->getEstimatedCompletionTime();
            } else {
                $taskData['countdown_minutes'] = null;
                $taskData['countdown_formatted'] = null;
                $taskData['estimated_completion_time'] = null;
            }
            // Generate PDF URL for delivery order
             $pdfContent = null;
            if (isset($taskData['delivery_order_id'])) {
                $pdfContent = $this->generateDeliveryOrderPDF($taskData['delivery_order_id'], $taskData['id']);
            }
            $taskData['pdf_url'] = $pdfContent;

            return response()->json([
                'result' => true,
                'message' => __LINE__ . $this->message_separator . 'api.message.task_found',
                'data' => [
                    'task' => $taskData,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'result' => false,
                'message' => __LINE__ . $this->message_separator . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    private function generateDeliveryOrderPDF($deliveryOrderId, $taskId = null)
    {
        try {
            $deliveryOrder = DeliveryOrder::findOrFail($deliveryOrderId);
            $company = $deliveryOrder->company;
            
            if ($taskId) {
                $task = Task::find($taskId);
                if ($task) {
                    $deliveryOrder->this_load = $task->this_load;
                }
            }

            $pdf = Pdf::loadView('delivery_orders.print', [
                'deliveryOrder' => $deliveryOrder,
                'company' => $company
            ]);

            $pdf->setPaper('A4', 'portrait')
                ->setOptions([
                    'isPhpEnabled' => true, 
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Arial'
                ]);

            // Return as base64 encoded string
            return base64_encode($pdf->output());
            
        } catch (Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return null;
        }
    }


    public function gettaskpage(Request $request){
        try{
            $data = $request->all();
            $size = 20;
            if(isset($data['size']))
            {
                $size = $data['size'];
            }
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validate
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $task = Task::where('driver_id', $driver->id)
                ->where('date',date('Y-m-d'))
                ->paginate($size);

            if($task){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.task_found',
                    'data' => [
                        'task' => $task,
                    ]
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.task_not_found',
                    'data' => [
                        'task' => null,
                    ]
                ], 200);

            }

        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function starttask(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validate
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $task = Task::where('id',$data['task_id'])->first();
            if(empty($task)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_task',
                    'data' => null
                ], 400);
            }else{
                if($task->status == 8){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_completed',
                        'data' => null
                    ], 400);
                }
                if($task->status == 9){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_cancelled',
                        'data' => null
                    ], 400);
                }
                if($task->status == 1){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_in_progress',
                        'data' => null
                    ], 400);
                }
            }
            //process
            $task->status = 1;
            $task->save();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.task_had_been_started_successfully',
                'data' => $task
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function canceltask(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validate
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $task = Task::where('id',$data['task_id'])->first();
            if(empty($task)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_task',
                    'data' => null
                ], 400);
            }else{
                if($task->status == 8){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_completed',
                        'data' => null
                    ], 400);
                }
                if($task->status == 9){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_cancelled',
                        'data' => null
                    ], 400);
                }
            }
            //process
            $task->status = 9;
            $task->save();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.task_had_been_cancelled_successfully',
                'data' => $task
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getproduct(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            if(isset($data['customer_id'])){
                $customer = Customer::where('id', $data['customer_id'])->first();
                if(empty($customer)){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                        'data' => null
                    ], 400);
                }
            }
            //process
            if(isset($data['customer_id'])){
                $product = DB::table('products')
                ->leftJoin('special_prices', function($join) use($data)
                    {
                        $join->on('special_prices.customer_id','=',DB::raw("'".$data['customer_id']."'"));
                        $join->on('special_prices.product_id', '=', 'products.id');
                        $join->on('special_prices.status', '=', DB::raw("'1'"));
                    })
                ->where('products.status','1')
                ->select('products.id','products.code','products.name',DB::raw('coalesce(special_prices.price,products.price) as "price"'))
                ->get();
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.product_found',
                    'data' => $product
                ], 200);
            }else{
                $product = DB::table('products')
                ->where('products.status','1')
                ->select('products.id','products.code','products.name',DB::raw('products.price as "price"'))
                ->get();
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.product_found',
                    'data' => $product
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getcustomer(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //process
            $customer = DB::select("SELECT customers.*,COALESCE(b.credit,0) as credit FROM customers customers RIGHT JOIN ( SELECT customer_id FROM assigns assigns WHERE driver_id = ".$driver->id." UNION SELECT customer_id FROM invoices invoices WHERE driver_id = ".$driver->id." ) a on a.customer_id = customers.id LEFT JOIN ( select invoices.customer_id, sum(invoice_details.totalprice) as totalprice, COALESCE(paymentsummary.amount,0) as paid, ( sum(invoice_details.totalprice) - COALESCE(paymentsummary.amount,0) ) as credit from invoices left join invoice_details on invoices.id = invoice_details.invoice_id left join ( select invoice_payments.customer_id, sum(COALESCE(invoice_payments.amount,0)) as amount from invoice_payments where invoice_payments.status = 1 group by invoice_payments.customer_id ) as paymentsummary on invoices.customer_id = paymentsummary.customer_id where invoices.status = 1 group by invoices.customer_id, paymentsummary.customer_id, paymentsummary.amount ) b on b.customer_id = customers.id");
            if(count($customer) != 0){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.customer_found',
                    'data' => $customer
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.customer_not_found',
                    'data' => null
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function customerdetail(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $customer = Customer::where('id', $data['customer_id'])->first();
            if(empty($customer)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                    'data' => null], 400);
            }
            //process
            $customer->customerdetail = DB::select("select i.date,i.id,'Invoice' as type, i.invoiceno as name, sum(COALESCE(id.totalprice,0)) as amount from invoices i left join invoice_details id on i.id = id.invoice_id where i.customer_id = ".$customer->id." group by i.date, i.id, i.invoiceno, i.customer_id union select ip.created_at as date,ip.id, 'Payment' as type, '' as name, ip.amount as amount from invoice_payments ip where ip.customer_id = ".$customer->id.";");
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.customer_found',
                'data' => $customer
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function customermakepayment(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|numeric',
                'amount' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $customer = Customer::where('id', $data['customer_id'])->first();
            if(empty($customer)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                    'data' => null
                ], 400);
            }
            //process
            $invoicepayment = New InvoicePayment();
            $invoicepayment->customer_id = $customer->id;
            $invoicepayment->amount = $data['amount'];
            $invoicepayment->type = 1;
            $invoicepayment->status = 1;
            $invoicepayment->driver_id = $driver->id;
            $invoicepayment->approve_by = $driver->name;
            $invoicepayment->approve_at = date('Y-m-d H:i:s');
            $invoicepayment->save();
            $invoicepayment->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$invoicepayment->customer_id.');')[0]->credit,2);
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.payment_insert_successfully_found',
                'data' => $invoicepayment
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function customerinvoice(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|numeric',
                'invoice_id' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            //process
            $invoice = Invoice::where('customer_id', $data['customer_id'])
            ->where('id', $data['invoice_id'])
            ->with('invoicedetail.product')
            ->with('customer')
            ->with('driver')
            ->with('invoicepayment')
            ->first();
            if(empty($invoice)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invoice_not_found',
                    'data' => null
                ], 200);
            }else{
               
               
                  
             try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.$invoice->updated_at.'",'.$invoice->customer_id.');');
                
                if($credit)
                {
                    $invoice->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $invoice->newcredit  = 0;
            }
            
               
               //$invoice->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.$invoice->updated_at.'",'.$invoice->customer_id.');')[0]->credit,2);
               
               
               
                $invoice->customer->groupcompany = DB::table('companies')
                ->where('companies.group_id',explode(',',$invoice->customer->group)[0])
                ->select('companies.*')
                ->first() ?? null;
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.invoice_found',
                    'data' => $invoice
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function customerpayment(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|numeric',
            'payment_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        //process
        $invoicepayment = InvoicePayment::where('customer_id', $data['customer_id'])->where('id', $data['payment_id'])->with('customer')->first();
        if(empty($invoicepayment)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invoice_payment_not_found',
                'data' => null
            ], 200);
        }else{
            
            
              try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.$invoicepayment->updated_at.'",'.$invoicepayment->customer_id.');');
                
                if($credit)
                {
                    $invoicepayment->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $invoicepayment->newcredit  = 0;
            }
            
            //$invoicepayment->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.$invoicepayment->created_at.'",'.$invoicepayment->customer_id.');')[0]->credit,2);
            
            
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.invoice_payment_found',
                'data' => $invoicepayment
            ], 200);
        }
    }

    public function addinvoice(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 401);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'date' => 'date_format:Y-m-d H:i:s',
                'customer_id' => 'required|numeric',
                'type' => 'required|numeric|gt:0|lt:6',
                'remark' => 'present|nullable|string',
                'invoice_id' => 'present|nullable|numeric',
                'invoiceno' => 'present|nullable|string',
                'invoicedetail' => 'required|array',
                'invoicedetail.*.product_id' => 'required',
                'invoicedetail.*.quantity' => 'required',
                'invoicedetail.*.price' => 'required',
                'invoicedetail.*.foc' => 'required|boolean'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            $customer = Customer::where('id',$data['customer_id'])->first();
            if(empty($customer)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                    'data' => null
                ], 400);
            }
            //process
            $runningno = Code::where('code','invoicerunningnumber')->first();
            $runningno->value = intval($runningno->value) + 1;
            $runningno->save();
            DB::beginTransaction();
            $extinvoice = Invoice::where('id',$data['invoice_id'])->where('status',0)->first();
            $invoiceno = null;
            $id = null;
            if(!empty($extinvoice)){
                if($extinvoice->invoiceno != $data['invoiceno'] && $data['invoiceno'] != null){
                    $invoiceno = $data['invoiceno'] . "(" . $extinvoice->invoiceno . ")";
                }else{
                    $invoiceno = $extinvoice->invoiceno;
                }
                $id = $extinvoice->id;
                Invoice::where('id',$data['invoice_id'])->delete();
                InvoiceDetail::where('invoice_id',$data['invoice_id'])->delete();
            }else{
                if($data['invoiceno'] != null){
                    $invoiceno = $data['invoiceno'];
                    $invoicerunningnumber = substr($invoiceno, -6);
                    if(($driver->invoice_runningnumber <=> $invoicerunningnumber) == -1){
                        Driver::where('id',$driver->id)->update(['invoice_runningnumber' => $invoicerunningnumber]);
                    }

                }else{
                    $invoiceno = "INV".str_pad($runningno->value, 7, '0', STR_PAD_LEFT);
                }
            }
            $invoice = new Invoice();
            if($id != null){
                $invoice->id = $id;
            }
            $invoice->date = $data['date'] ?? date('Y-m-d H:i:s');
            $invoice->invoiceno = $invoiceno;
            $invoice->customer_id = $data['customer_id'];
            $invoice->driver_id = $trip->driver_id;
            $invoice->kelindan_id = $trip->kelindan_id;
            $invoice->agent_id = $customer->agent_id;
            $invoice->supervisor_id = $customer->supervisor_id;
            $invoice->paymentterm = $data['type'];
            $invoice->status = 1;
            $invoice->chequeno = $data['cheque_no'];
            $invoice->remark = $data['remark'];
            $invoice->save();
            $totalprice = 0;
            foreach($data['invoicedetail'] as $id){
                $product = Product::where('id',$id['product_id'])->first();
                if(empty($product)){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.invalid_product',
                        'data' => null
                    ], 400);
                    DB::rollback();
                }
                $invoicedetail = new InvoiceDetail();
                $invoicedetail->invoice_id = $invoice->id;
                $invoicedetail->product_id = $id['product_id'];
                $invoicedetail->quantity = $id['quantity'];
                $invoicedetail->price = $id['price'];
                $invoicedetail->totalprice = $id['quantity'] * $id['price'];
                $totalprice = $totalprice + $invoicedetail->totalprice;
                if($id['foc']) {
                    $invoicedetail->remark = "FOC"; // Mark as FOC but do NOT count towards achievequantity
                } else {
                    // Only update FOC achievequantity if the product is NOT FOC
                    $foc = Foc::where('customer_id', $customer->id)
                        ->where('product_id', $id['product_id'])
                        ->where('startdate', '<=', date('Y-m-d H:i:s'))
                        ->where('enddate', '>', date('Y-m-d H:i:s'))
                        ->where('status', 1)
                        ->first();

                    if($foc) {
                        $newAchieveQuantity = $foc->achievequantity + $id['quantity'];
                        $newStatus = ($newAchieveQuantity >= $foc->quantity) ? 0 : 1;

                        $foc->update([
                            'achievequantity' => $newAchieveQuantity,
                            'status' => $newStatus
                        ]);
                    }
                }
                $invoicedetail->save();
                $inventorybalance = InventoryBalance::where('lorry_id', $trip->lorry_id)->where('product_id', $id['product_id'])->first();
                if(empty($inventorybalance)){
                    $newinventorybalance = New InventoryBalance();
                    $newinventorybalance->lorry_id = $trip->lorry_id;
                    $newinventorybalance->product_id = $id['product_id'];
                    $newinventorybalance->quantity = 0 - $id['quantity'];
                    $newinventorybalance->save();
                }else{
                    $inventorybalance->quantity = $inventorybalance->quantity - $id['quantity'];
                    $inventorybalance->save();
                }
                $inventorytransaction = New InventoryTransaction();
                $inventorytransaction->lorry_id = $trip->lorry_id;
                $inventorytransaction->product_id = $id['product_id'];
                $inventorytransaction->quantity = $id['quantity'] * -1;
                $inventorytransaction->type = 3;
                $inventorytransaction->user = $driver->employeeid . " (".$driver->name.")";
                $inventorytransaction->date = date('Y-m-d H:i:s');
                $inventorytransaction->save();
            }
            if($data['type'] == 1){
                $invoicepayment = New InvoicePayment();
                $invoicepayment->invoice_id = $invoice->id;
                $invoicepayment->type = 1;
                $invoicepayment->customer_id = $invoice->customer_id;
                $invoicepayment->amount = $totalprice;
                $invoicepayment->status = 1;
                $invoicepayment->driver_id = $driver->id;
                $invoicepayment->approve_by = $driver->name;
                $invoicepayment->approve_at = date('Y-m-d H:i:s');
                $invoicepayment->save();
            }
            $task = Task::where('customer_id', $data['customer_id'])->where('driver_id',$driver->id)->update(['status' => 8]);
            DB::commit();
            $iv = Invoice::where('id',$invoice->id)->with('invoicedetail.product')->get()->first();
            
             
             try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$iv->customer_id.');');
                
                if($credit)
                {
                    $iv->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $iv->newcredit  = 0;
            }
            
            
           //$iv->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$iv->customer_id.');')[0]->credit,2);
            
            
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.invoice_add_successfully',
                'data' => $iv
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
      public function invoicepdf(Request $request)
	{
	    try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'invoice_id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            
            $id = $data['invoice_id'];
            
            
            $invoice = Invoice::where('id',$id)
            ->with('customer')
            ->with('driver')
            ->with('invoicedetail.product')
            ->first();
    
            if (empty($invoice)) {
                abort('404');
            }
    
            $min = 450;
            $each = 23;
            $height = (count($invoice['invoicedetail']) * $each) + $min;
    
            try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.$invoice->updated_at.'",'.$invoice->customer_id.');');
                
                if($credit)
                {
                    $invoice->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $invoice->newcredit  = 0;
            }
            $invoice->customer->groupcompany = DB::table('companies')
            ->where('companies.group_id',explode(',',$invoice->customer->group)[0])
            ->select('companies.*')
            ->first() ?? null;
            
              $pdf = Pdf::loadView('invoices.print', array(
                    'invoice' => $invoice
                ));
    
            $pdf->setPaper(array(0, 0, 300, $height), 'portrait')->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
    
            $invoiceFilename = 'invoice-' . $invoice->invoiceno . '.pdf';
            $path = 'invoices-pdf/' . $invoiceFilename;
            
            Storage::disk('public')->put($path, $pdf->output());
            $url = url($path);

            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.load_success',
                'data' => $url
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
        
	  
	}
	
	
	
     public function addpayment(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null,
                    'color_code' => ''
                ], 401);
            }
            //validation
            
            $validator = Validator::make($request->all(), [
                'date' => 'date_format:Y-m-d H:i:s',
                'customer_id' => 'required|numeric',
                'type' => 'required|numeric|gt:0|lt:6',
                'remark' => 'present|nullable|string',
                'amount' =>'required|numeric',
                
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null,
                ], 400);
            }
            $customer = Customer::where('id',$data['customer_id'])->first();
            if(empty($customer)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_customer',
                    'data' => null,
                ], 400);
            }
            //process
            
            DB::beginTransaction();
            
            $invoicepayment = New InvoicePayment();
            if(isset($data['invoice_id'])){
                $invoicepayment->invoice_id = $data['invoice_id'];
            }
            
            $invoicepayment->type = $data['type'];
            $invoicepayment->customer_id = $data['customer_id'];
            $invoicepayment->amount = $data['amount'];
            $invoicepayment->status = 1;
            $invoicepayment->chequeno = $data['cheque_no'];
            $invoicepayment->driver_id = $driver->id;
            $invoicepayment->approve_by = $driver->name;
            $invoicepayment->approve_at = date('Y-m-d H:i:s');
            //$invoicepayment->created_at = $data['date'];
            $invoicepayment->save();
            
            DB::commit();
            $iv = InvoicePayment::where('id',$invoicepayment->id)->get()->first();
           
            $iv['payment_no'] = sprintf('PR%05d',$iv->id);
            
            
             try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$iv->customer_id.');');
                
                if($credit)
                {
                    $iv->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $iv->newcredit  = 0;
            }
            
           
           // $iv->newcredit = round(DB::select('call ice_spGetCustomerCreditByDate("'.date('Y-m-d H:i:s').'",'.$iv->customer_id.');')[0]->credit,2);
           
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.invoice_add_successfully',
                'data' => $iv
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
      public function paymentpdf(Request $request)
	{
	    try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'payment_id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            
            $id = $data['payment_id'];
            
            
            $invoice = InvoicePayment::where('id',$id)
                    ->with('customer')
                    ->first();
    
            if (empty($invoice)) {
                abort('404');
            }
    
            $min = 450;
            $each = 23;
    
            try
            {
                $credit = DB::select('call ice_spGetCustomerCreditByDate("'.$invoice->updated_at.'",'.$invoice->customer_id.');');
                
                if($credit)
                {
                    $invoice->newcredit = round($credit[0]->credit,2);
    
                }
    
            }
            catch(Exception $ex)
            {
                 $invoice->newcredit  = 0;
            }
            
            $invoice->customer->groupcompany = DB::table('companies')
            ->where('companies.group_id',explode(',',$invoice->customer->group)[0])
            ->select('companies.*')
            ->first() ?? null;
            
            $pdf = Pdf::loadView('invoice_payments.print', array(
                'invoice' => $invoice
            ));

    
            $pdf->setPaper(array(0, 0, 300, $min), 'portrait')->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
            
            $invoiceFilename = 'payment-' . $invoice->id . '.pdf';
            $path = 'payments/' . $invoiceFilename;
            
            Storage::disk('public')->put($path, $pdf->output());
            $url = url($path);
            
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.load_success',
                'data' => $url
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
        
	  
	}
	
	
    public function getstock(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            //if(!empty($trip)){
            //    if($trip->type == 2){
            //        return response()->json([
            //            'result' => false,
            //            'message' => __LINE__.$this->message_separator.'Trip had not started',
            //            'data' => null
            //        ], 401);
            //    }
            //}else{
            //    return response()->json([
            //        'result' => false,
            //        'message' => __LINE__.$this->message_separator.'Trip had not started',
            //        'data' => null
            //    ], 401);
            //}
            //process
            $inventorybalance = InventoryBalance::where('lorry_id',$trip->lorry_id)
            ->leftjoin('products','products.id','=','inventory_balances.product_id')
            ->get(['inventory_balances.id','inventory_balances.quantity','inventory_balances.product_id','products.name'])->toarray();
            if(count($inventorybalance) == 0){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.no_stock_found',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.stock_found',
                    'data' => $inventorybalance
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    // public function getstock(Request $request){
    //     try{
    //         $data = $request->all();
    //         //check session
    //         $driver = Driver::where('session', $request->header('session'))->first();
    //         if(empty($driver)){
    //             return response()->json([
    //                 'result' => false,
    //                 'message' => __LINE__.$this->message_separator.'Invalid session',
    //                 'data' => null
    //             ], 401);
    //         }
    //         //validation
    //         $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
    //         if(!empty($trip)){
    //             if($trip->type == 2){
    //                 return response()->json([
    //                     'result' => false,
    //                     'message' => __LINE__.$this->message_separator.'Trip had not started',
    //                     'data' => null
    //                 ], 401);
    //             }
    //         }else{
    //             return response()->json([
    //                 'result' => false,
    //                 'message' => __LINE__.$this->message_separator.'Trip had not started',
    //                 'data' => null
    //             ], 401);
    //         }
    //         //process
    //         $inventorybalance = InventoryBalance::where('lorry_id',$trip->lorry_id)
    //         ->leftjoin('products','products.id','=','inventory_balances.product_id')
    //         ->get(['inventory_balances.id','inventory_balances.quantity','inventory_balances.product_id','products.name'])->toarray();
    //         if(count($inventorybalance) == 0){
    //             return response()->json([
    //                 'result' => false,
    //                 'message' => __LINE__.$this->message_separator.'No stock found',
    //                 'data' => null
    //             ], 200);
    //         }else{
    //             return response()->json([
    //                 'result' => true,
    //                 'message' => __LINE__.$this->message_separator.'Stock found',
    //                 'data' => $inventorybalance
    //             ], 200);
    //         }
    //     }
    //     catch(Exception $e){
    //         return response()->json([
    //             'result' => false,
    //             'message' => __LINE__.$this->message_separator.$e->getMessage(),
    //             'data' => null
    //         ], 500);
    //     }
    // }

    public function listotherdriver(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $drivers = Trip::where('driver_id','!=',$trip->driver_id)
            ->select('driver_id','drivers.name','drivers.employeeid')
            ->groupby('driver_id','drivers.name','drivers.employeeid')
            ->havingRaw('(count(driver_id) % 2) > 0')
            ->leftjoin('drivers','drivers.id','=','trips.driver_id')
            ->get()->toarray();
            if(count($drivers) == 0){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.no_driver_found',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.driver_found',
                    'data' => $drivers
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function transferstock(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|numeric',
            'transferdetail' => 'present|array',
            'transferdetail.*.product_id' => 'required|numeric',
            'transferdetail.*.quantity' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        $todriver = Driver::where('id',$data['driver_id'])->first();
        if(empty($todriver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        $totrip = Trip::where('driver_id', $data['driver_id'])->orderby('date','desc')->first();
        if(!empty($totrip)){
            if($totrip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.selected_driver_trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.selected_driver_trip_had_not_started',
                'data' => null
            ], 400);
        }
        //process
        try{

            DB::beginTransaction();
            foreach($data['transferdetail'] as $td){
                $product = Product::where('id',$td['product_id'])->first();
                if(empty($product)){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.invalid_product',
                        'data' => null
                    ], 400);
                }
                $inventorytransfer = New InventoryTransfer();
                $inventorytransfer->date = date('Y-m-d H:i:s');
                $inventorytransfer->from_driver_id = $trip->driver_id;
                $inventorytransfer->from_lorry_id = $trip->lorry_id;
                $inventorytransfer->to_driver_id = $totrip->driver_id;
                $inventorytransfer->to_lorry_id = $totrip->lorry_id;
                $inventorytransfer->product_id = $td['product_id'];
                $inventorytransfer->quantity = $td['quantity'];
                $inventorytransfer->status = 1;
                $inventorytransfer->save();
            }
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.pending_driver_accept_transfer',
                'data' => null
            ], 200);
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function gettransfer(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $request = InventoryTransfer::where('from_driver_id', $trip->driver_id)
            ->where('date', '>=', date('Y-m-d 00:00:00'))
            ->with('product:id,name')
            ->with('todriver:id,name')
            ->orderby('date','desc')
            ->get(['id','date','status','quantity','product_id','to_driver_id'])
            ->toarray();
            $pending = InventoryTransfer::where('to_driver_id', $trip->driver_id)
            ->where('date', '>=', date('Y-m-d 00:00:00'))
            // ->where('status', 1)
            ->with('product:id,name')
            ->with('fromdriver:id,name')
            ->orderby('date','desc')
            ->get(['id','date','status','quantity','product_id','from_driver_id'])
            ->toarray();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.transfer_found',
                'data' => [
                    'request' => $request,
                    'pending' => $pending
                ]
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function updatetransfer(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'transfer_id' => 'required|numeric',
            'status' => 'required|numeric|gt:1|lt:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        // $inventorytransfer = InventoryTransfer::where('id', $data['transfer_id'])->where('to_driver_id',$driver->id)->first();
        $inventorytransfer = InventoryTransfer::where('id', $data['transfer_id'])->first();
        if(empty($inventorytransfer)){
            return response()->json([
               'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.transfer_not_found',
                'data' => null
            ], 400);
        }
        if($inventorytransfer->status == 2){
            return response()->json([
              'result' => false,
              'message' => __LINE__.$this->message_separator.'api.message.transfer_already_accepted',
                'data' => null
            ], 400);
        }
        if($inventorytransfer->status == 3){
            return response()->json([
              'result' => false,
              'message' => __LINE__.$this->message_separator.'api.message.transfer_already_rejected',
              'data' => null
            ], 400);
        }
        $fromdriver = Driver::where('id',$inventorytransfer->from_driver_id)->first();
        if(empty($fromdriver)){
            return response()->json([
              'result' => false,
              'message' => __LINE__.$this->message_separator.'api.message.from_driver_not_found',
                'data' => null
            ], 400);
        }
        $todriver = Driver::where('id',$inventorytransfer->to_driver_id)->first();
        if(empty($fromdriver)){
            return response()->json([
              'result' => false,
              'message' => __LINE__.$this->message_separator.'api.message.to_driver_not_found',
                'data' => null
            ], 400);
        }
        //process
        try{

            DB::beginTransaction();
            if($data['status'] == 3){
                $inventorytransfer->status = 3;
                $inventorytransfer->save();
                DB::commit();
                return response()->json([
                   'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.transfer_rejecet_successfully',
                    'data' => null
                ], 200);
            }
            if($data['status'] == 2){
                $inventorytransfer->status = 2;
                $inventorytransfer->save();
                 //from
                 $frominventorybalance = Inventorybalance::where('lorry_id',$inventorytransfer->from_lorry_id)
                 ->where('product_id',$inventorytransfer->product_id)->first();
                 if(empty($frominventorybalance)){
                     $newfrominventorybalance = New Inventorybalance();
                     $newfrominventorybalance->lorry_id = $inventorytransfer->from_lorry_id;
                     $newfrominventorybalance->product_id = $inventorytransfer->product_id;
                     $newfrominventorybalance->quantity = 0 - $inventorytransfer->quantity;
                     $newfrominventorybalance->save();
                 }else{
                     $frominventorybalance->quantity = $frominventorybalance->quantity - $inventorytransfer->quantity;
                     $frominventorybalance->save();
                 }
                 $frominventorytransaction = New InventoryTransaction();
                 $frominventorytransaction->lorry_id = $inventorytransfer->from_lorry_id;
                 $frominventorytransaction->product_id = $inventorytransfer->product_id;
                 $frominventorytransaction->quantity = $inventorytransfer->quantity * -1;
                 $frominventorytransaction->type = 4;
                 $frominventorytransaction->user = $fromdriver->employeeid . " (".$fromdriver->name.") => " . $todriver->employeeid . " (".$todriver->name.")";
                 $frominventorytransaction->date = date('Y-m-d H:i:s');
                 $frominventorytransaction->save();
                 //to
                 $toinventorybalance = Inventorybalance::where('lorry_id',$inventorytransfer->to_lorry_id)
                 ->where('product_id',$inventorytransfer->product_id)->first();
                 if(empty($toinventorybalance)){
                     $newtoinventorybalance = New Inventorybalance();
                     $newtoinventorybalance->lorry_id = $inventorytransfer->to_lorry_id;
                     $newtoinventorybalance->product_id = $inventorytransfer->product_id;
                     $newtoinventorybalance->quantity = $inventorytransfer->quantity;
                     $newtoinventorybalance->save();
                 }else{
                     $toinventorybalance->quantity = $toinventorybalance->quantity + $inventorytransfer->quantity;
                     $toinventorybalance->save();
                 }
                 $toinventorytransaction = New InventoryTransaction();
                 $toinventorytransaction->lorry_id = $inventorytransfer->to_lorry_id;
                 $toinventorytransaction->product_id = $inventorytransfer->product_id;
                 $toinventorytransaction->quantity = $inventorytransfer->quantity;
                 $toinventorytransaction->type = 4;
                 $toinventorytransaction->user = $fromdriver->employeeid . " (".$fromdriver->name.") => " . $todriver->employeeid . " (".$todriver->name.")";
                 $toinventorytransaction->date = date('Y-m-d H:i:s');
                 $toinventorytransaction->save();
                 DB::commit();
                 return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.transfer_accept_successfully',
                     'data' => null
                 ], 200);
            }
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getstocktransaction(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            if($data['date'] > date('Y-m-d H:i:s')){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.date_cannot_be_future_date',
                    'data' => null
                ], 400);
            }
            //process
            $inventorytransaction = InventoryTransaction::where('lorry_id',$trip->lorry_id)
            ->leftjoin('products','products.id','=','inventory_transactions.product_id')
            ->where('date','>=',$data['date'])
            ->where('date','<',date('Y-m-d', strtotime("+1 day", strtotime($data['date']))))
            ->orderby('date','desc')
            // ->select('lorry_id','product_id','quantity','type','date');
            ->select('inventory_transactions.id','inventory_transactions.quantity','inventory_transactions.type','inventory_transactions.date','products.name');

            $finalinventorytransaction = InventoryTransaction::where('lorry_id',$trip->lorry_id)
            ->leftjoin('products','products.id','=','inventory_transactions.product_id')
            ->where('date','<',$data['date'])
            ->groupby('inventory_transactions.product_id','products.id','products.name')
            // ->select('lorry_id','product_id',DB::raw('sum(quantity) as quantity'),DB::raw('0 as type'),DB::raw('"'.$data['date'].'" as date'))
            ->select(DB::raw('0 as id'),DB::raw('sum(inventory_transactions.quantity) as quantity'),DB::raw('0 as type'),DB::raw('"'.$data['date'].'" as date'),'products.name')
            ->union($inventorytransaction)
            ->orderby('date','desc')
            ->get()
            ->toarray();
            if(count($finalinventorytransaction) == 0){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.transaction_not_found',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.transaction_found',
                    'data' => $finalinventorytransaction
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function listalldriver(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
            if(!empty($trip)){
                if($trip->type == 2){
                    return response()->json([
                        'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                        'data' => null
                    ], 400);
                }
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
            //process
            $driver = Driver::where('id','!=',$trip->driver_id)->get()->toarray();
            if(count($driver) == 0){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_driver',
                    'data' => null
                ], 200);
            }else{
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.driver_found',
                    'data' => $driver
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    //NA
    public function getdrivertask(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json(['result' => false, 'message' => 'Session not found', 'data' => null], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json(['result' => false, 'message' => 'Trip had not started', 'data' => null], 400);
            }
        }else{
            return response()->json(['result' => false, 'message' => 'Trip had not started', 'data' => null], 400);
        }
        $messages = array(
            'driver_id.required' => 'Driver ID is required',
        );
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }
        $fromdriver = Driver::where('id',$data['driver_id'])->first();
        if(empty($fromdriver)){
            return response()->json(['result' => false,'message' => 'Driver not found', 'data' => null], 400);
        }
        //process
        $fromdrivertrip = Trip::where('driver_id', $fromdriver->id)->orderby('date','desc')->first();
        if(!empty($fromdrivertrip)){
            if($fromdrivertrip->type == 2){
                //Take from assign & invoice
                $assigns = Assign::where('driver_id', $fromdriver->id)
                ->orderby('sequence','asc')
                ->select('customer_id','sequence',DB::RAW('0 as invoice_id'));
                $task = Invoice::where('driver_id', $fromdriver->id)
                ->where('status',0)
                ->where('date',date('Y-m-d'))
                ->select('customer_id',DB::RAW('0 as sequence'),DB::RAW('id as invoice_id'))
                ->union($assigns)
                ->with('customer')
                ->get()->toarray();
                if(empty($task)){
                    return response()->json(['result' => false,'message' => 'Task not found', 'data' => null], 200);
                }else{
                    return response()->json(['result' => true,'message' => 'Task found', 'data' => $task], 200);
                }
            }else{
                //Take from task
                $task = Task::where('driver_id',$fromdriver->id)
                ->wherein('status',[0,1])
                ->select('customer_id','sequence','invoice_id')
                ->with('customer')
                ->get()->toarray();
                if(empty($task)){
                    return response()->json(['result' => false,'message' => 'Task not found', 'data' => null], 200);
                }else{
                    return response()->json(['result' => true,'message' => 'Task found', 'data' => $task], 200);
                }
            }
        }else{
            //Take from assign & invoice
            $assigns = Assign::where('driver_id', $fromdriver->id)
            ->orderby('sequence','asc')
            ->select('customer_id','sequence',DB::RAW('0 as invoice_id'));
            $task = Invoice::where('driver_id', $fromdriver->id)
            ->where('status',0)
            ->where('date',date('Y-m-d'))
            ->select('customer_id',DB::RAW('0 as sequence'),DB::RAW('id as invoice_id'))
            ->union($assigns)
            ->with('customer')
            ->get()->toarray();
            if(empty($task)){
                return response()->json(['result' => false,'message' => 'Task not found', 'data' => null], 200);
            }else{
                return response()->json(['result' => true,'message' => 'Task found', 'data' => $task], 200);
            }
        }
    }

    //NA
    public function pulldrivertask(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json(['result' => false, 'message' => 'Session not found', 'data' => null], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json(['result' => false, 'message' => 'Trip had not started', 'data' => null], 400);
            }
        }else{
            return response()->json(['result' => false, 'message' => 'Trip had not started', 'data' => null], 400);
        }
        $messages = array(
            'driver_id.required' => 'Driver ID is required',
            'transferdetail.*.customer_id.required' => 'Customer ID is required',
        );
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required',
            'transferdetail.*.customer_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }
        try{
            if(count($data['transferdetail']) == 0){
                return response()->json(['result' => false, 'message' => 'Invalid format, transfer detail is empty', 'data' => null], 400);
            }
        }
        catch(Exception $e){
            return response()->json(['result' => false, 'message' => 'Invalid format', 'data' => null], 400);
        }
        $fromdriver = Driver::where('id', $data['driver_id'])->first();
        if(empty($fromdriver)){
            return response()->json(['result' => false,'message' => 'Driver not found', 'data' => null], 400);
        }
        //process
        try{
            DB::beginTransaction();
            foreach($data['transferdetail'] as $key => $c){
                $customer = Customer::where('id',$c['customer_id'])->first();
                if(empty($customer)){
                    DB::rollback();
                    return response()->json(['result' => false,'message' => 'Customer not found', 'data' => null], 400);
                }else{
                    $fromdrivertrip = Trip::where('driver_id', $fromdriver->id)->orderby('date','desc')->first();
                    if(!empty($fromdrivertrip)){
                        if($fromdrivertrip->type == 2){
                            //take from assign & invoice
                            $invoice = Invoice::where('driver_id', $fromdriver->id)
                            ->where('status',0)
                            ->where('date',date('Y-m-d'))
                            ->where('customer_id',$customer->id)
                            ->get()->toarray();
                            if(empty($invoice)){
                                $newtask =  New Task();
                                $newtask->driver_id = $driver->id;
                                $newtask->customer_id = $customer->id;
                                $newtask->status = 0;
                                $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                                if(empty($sequence)){
                                    $sequence = 0;
                                }else{
                                    $sequence = $sequence->sequence;
                                }
                                $newtask->sequence =  $sequence + 1;
                                $newtask->date = date('Y-m-d');
                                $newtask->save();
                            }else{
                                foreach($invoice as $i){
                                    $newtask =  New Task();
                                    $newtask->driver_id = $driver->id;
                                    $newtask->customer_id = $customer->id;
                                    $newtask->invoice_id = $i['id'];
                                    $newtask->status = 0;
                                    $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                                    if(empty($sequence)){
                                        $sequence = 0;
                                    }else{
                                        $sequence = $sequence->sequence;
                                    }
                                    $newtask->sequence =  $sequence + 1;
                                    $newtask->date = date('Y-m-d');
                                    $newtask->save();
                                }
                            }
                        }else{
                            //take from task
                            $task = Task::where('driver_id',$fromdriver->id)
                            ->wherein('status',[0,1])
                            ->where('customer_id',$customer->id)->first();
                            $newtask =  New Task();
                            $newtask->driver_id = $driver->id;
                            $newtask->customer_id = $customer->id;
                            $newtask->status = 0;
                            $newtask->invoice_id = $task->invoice_id;
                            $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                            if(empty($sequence)){
                                $sequence = 0;
                            }else{
                                $sequence = $sequence->sequence;
                            }
                            $newtask->sequence =  $sequence + 1;
                            $newtask->date = date('Y-m-d');
                            $newtask->save();
                            $task->update(['status' => 9]);
                        }
                    }else{
                        //take from assign & invoice
                        $invoice = Invoice::where('driver_id', $fromdriver->id)
                        ->where('status',0)
                        ->where('date',date('Y-m-d'))
                        ->where('customer_id',$customer->id)
                        ->get()->toarray();
                        if(empty($invoice)){
                            $newtask =  New Task();
                            $newtask->driver_id = $driver->id;
                            $newtask->customer_id = $customer->id;
                            $newtask->status = 0;
                            $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                            if(empty($sequence)){
                                $sequence = 0;
                            }else{
                                $sequence = $sequence->sequence;
                            }
                            $newtask->sequence =  $sequence + 1;
                            $newtask->date = date('Y-m-d');
                            $newtask->save();
                        }else{
                            foreach($invoice as $i){
                                $newtask =  New Task();
                                $newtask->driver_id = $driver->id;
                                $newtask->customer_id = $customer->id;
                                $newtask->invoice_id = $i['id'];
                                $newtask->status = 0;
                                $sequence = Task::where('driver_id',$driver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                                if(empty($sequence)){
                                    $sequence = 0;
                                }else{
                                    $sequence = $sequence->sequence;
                                }
                                $newtask->sequence =  $sequence + 1;
                                $newtask->date = date('Y-m-d');
                                $newtask->save();
                            }
                        }
                    }

                }
            }
            DB::commit();
            return response()->json(['result' => true, 'message' => 'Pulled task successfully', 'data' => null], 200);
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json(['result' => false,'message' => $e->getMessage(), 'data' => null], 400);
        }
    }

    public function pushdrivertask(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|numeric',
            'transferdetail' => 'present|array',
            'transferdetail.*.task_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        $todriver = Driver::where('id', $data['driver_id'])->first();
        if(empty($todriver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_driver',
                'data' => null
            ], 400);
        }
        //process
        try{
            DB::beginTransaction();
            foreach($data['transferdetail'] as $key => $c){
                $task = Task::where('id',$c['task_id'])->first();
                if(empty($task)){
                    DB::rollback();
                    return response()->json([
                       'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.invalid_task',
                        'data' => null
                    ], 400);
                }
                if($task->status == 9){
                    DB::rollback();
                    return response()->json([
                       'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_cancelled',
                        'data' => null
                    ], 400);
                }
                if($task->status == 8){
                    DB::rollback();
                    return response()->json([
                       'result' => false,
                        'message' => __LINE__.$this->message_separator.'api.message.task_had_been_completed',
                        'data' => null
                    ], 400);
                }
                $sequence = Task::where('driver_id',$todriver->id)->where('date',date('Y-m-d'))->orderby('sequence','desc')->first();
                if(empty($sequence)){
                    $sequence = 0;
                }else{
                    $sequence = $sequence->sequence;
                }
                $task->sequence = $sequence + 1;
                $task->driver_id = $todriver->id;
                $task->status = 0;
                $task->based = 0;
                $task->trip_id = null;
                $task->save();

                $tasktransfer = new TaskTransfer();
                $tasktransfer->date = date("Y-m-d H:i:s");
                $tasktransfer->from_driver_id = $driver->id;
                $tasktransfer->to_driver_id = $todriver->id;
                $tasktransfer->task_id = $c['task_id'];
                $tasktransfer->save();
            }
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.push_task_successfully',
                'data' => null
            ], 200);
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function listtranfer(Request $request){
        $data = $request->all();
        //check session
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $trip = Trip::where('driver_id', $driver->id)->orderby('date','desc')->first();
        if(!empty($trip)){
            if($trip->type == 2){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                    'data' => null
                ], 400);
            }
        }else{
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.trip_had_not_started',
                'data' => null
            ], 400);
        }
        //process
        try{
            $tasktransfer = TaskTransfer::where('from_driver_id',$driver->id)
            ->where('date', '>=', date('Y-m-d 00:00:00'))
            ->with('fromdriver:id,name')
            ->with('todriver:id,name')
            ->with('task.customer')
            ->get()->toArray();
            if(!empty($tasktransfer)){
                return response()->json([
                    'result' => true,
                    'message' => __LINE__.$this->message_separator.'api.message.task_transfer_found',
                    'data' => $tasktransfer
                ], 200);
            }else{
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.task_transfer_not_found',
                    'data' => null
                ], 200);
            }
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function dashboard_bk(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            if($data['date'] > date('Y-m-d H:i:s')){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.date_cannot_be_future_date',
                    'data' => null
                ], 400);
            }
            //process
            $sales = DB::Select('select sum(a.totalprice) as sales from(select i.id,sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' group by i.id) a')[0]->sales;
            $cash = DB::Select('select coalesce(sum(coalesce(amount,0)),0) as cash from invoice_payments where type = 1 and status = 1 and driver_id = '.$driver->id.' and approve_at >= "'.$data['date'].'" and approve_at < "'.date('Y-m-d', strtotime("+1 day", strtotime($data['date']))).'";')[0]->cash;
            // $credit = DB::select('select sum(a.totalprice) as credit from ( select i.id,sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id left join invoice_payments ip on ip.invoice_id = i.id where i.status = 1 and i.date = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and ip.id is null group by i.id ) a')[0]->credit;
            $credit = DB::select('select sum(a.totalprice) as credit from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and i.paymentterm = 2 group by i.id ) a')[0]->credit;
            $productsold = DB::Select('select sum(id.quantity) as productsold from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id)[0]->productsold;
            $solddetail = DB::select('select p.name, sum(id.quantity) as quantity from invoices i left join invoice_details id on id.invoice_id = i.id left join products p on p.id = id.product_id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' group by id.product_id, p.id, p.name');
            $trip = DB::select('select t.id, d.name as driver_name, k.name as kelindan_name, l.lorryno from trips t left join drivers d on d.id = t.driver_id left join kelindans k on k.id = t.kelindan_id left join lorrys l on l.id = t.lorry_id where t.driver_id = '.$driver->id.' and t.type = 1 and t.date >= "'.$data['date'].'" and t.date < "'.$data['date'].' 23:59:59"');
            // $trip = Trip::where('driver_id', $driver->id)
            // ->where('date','>=',$data['date'].' 00:00:00')
            // ->where('date','<',$data['date'].' 23:59:59')
            // ->where('type',1)
            // ->with('driver')
            // ->with('kelindan')
            // ->with('lorry')
            // ->get()
            // ->toArray();
            $result = [
                'sales' => round($sales,2),
                'cash' => round($cash,2),
                'credit' => round($credit,2),
                'productsold' => [
                    'total_quantity' =>round($productsold,2),
                    'details' =>$solddetail
                ],
                'trip' => $trip
            ];
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.get_dashboard_successfully',
                'data' => $result
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
     public function dashboard(Request $request){
        try{
            $data = $request->all();
            //check session
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }
            //validation
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                    'data' => null
                ], 400);
            }
            if($data['date'] > date('Y-m-d H:i:s')){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.date_cannot_be_future_date',
                    'data' => null
                ], 400);
            }
            //process
            $sales = DB::Select('select sum(a.totalprice) as sales from(select i.id,sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' group by i.id) a')[0]->sales;
            $cash = DB::Select('select coalesce(sum(coalesce(amount,0)),0) as cash from invoice_payments where type = 1 and status = 1 and driver_id = '.$driver->id.' and approve_at >= "'.$data['date'].'" and approve_at < "'.date('Y-m-d', strtotime("+1 day", strtotime($data['date']))).'";')[0]->cash;
            $bank_in = DB::Select('select coalesce(sum(coalesce(bank_in,0)),0) as bank_in from trips where type = 2 and driver_id = '.$driver->id.' and created_at >= "'.$data['date'].'" and created_at < "'.date('Y-m-d', strtotime("+1 day", strtotime($data['date']))).'";')[0]->bank_in;
            $cash_left = DB::Select('select coalesce(sum(coalesce(cash,0)),0) as cash from trips where type = 2 and driver_id = '.$driver->id.' and created_at >= "'.$data['date'].'" and created_at < "'.date('Y-m-d', strtotime("+1 day", strtotime($data['date']))).'";')[0]->cash;
            // $credit = DB::select('select sum(a.totalprice) as credit from ( select i.id,sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id left join invoice_payments ip on ip.invoice_id = i.id where i.status = 1 and i.date = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and ip.id is null group by i.id ) a')[0]->credit;
            $credit = DB::select('select sum(a.totalprice) as credit from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and i.paymentterm = 2 group by i.id ) a')[0]->credit;
            $bank = DB::select('select sum(a.totalprice) as bank from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and i.paymentterm = 3 group by i.id ) a')[0]->bank;
            $tng = DB::select('select sum(a.totalprice) as tng from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and i.paymentterm = 4 group by i.id ) a')[0]->tng;
            $cheque = DB::select('select sum(a.totalprice) as cheque from ( select i.id, sum(id.totalprice) as totalprice from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' and i.paymentterm = 5 group by i.id ) a')[0]->cheque;
            $productsold = DB::Select('select sum(id.quantity) as productsold from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and id.totalprice > 0 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id)[0]->productsold;
            $solddetail = DB::select('select p.name, sum(id.quantity) as quantity, sum(id.totalprice) as price from invoices i left join invoice_details id on id.invoice_id = i.id  left join products p on p.id = id.product_id where i.status = 1 and id.totalprice > 0 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' group by id.product_id, p.id, p.name');
            $productfoc = DB::Select('select sum(id.quantity) as productsold from invoices i left join invoice_details id on id.invoice_id = i.id where i.status = 1 and id.totalprice = 0 and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id)[0]->productsold;
            $focdetail = DB::select('select p.name, sum(id.quantity) as quantity, sum(id.totalprice) as price from invoices i left join invoice_details id on id.invoice_id = i.id left join products p on p.id = id.product_id where i.status = 1 and id.totalprice = 0  and DATE(i.date) = "'.$data['date'].'" and i.driver_id = '.$driver->id.' group by id.product_id, p.id, p.name');
            $trip = DB::table('trips as t')
                ->select([
                    't.id',
                    't.advance_amount',  // Make sure this matches your column name exactly
                    'd.name as driver_name',
                    'k.name as kelindan_name', 
                    'l.lorryno'
                ])
                ->leftJoin('drivers as d', 'd.id', '=', 't.driver_id')
                ->leftJoin('kelindans as k', 'k.id', '=', 't.kelindan_id')
                ->leftJoin('lorrys as l', 'l.id', '=', 't.lorry_id')
                ->where('t.driver_id', $driver->id)
                ->where('t.type', 1)
                ->whereDate('t.date', $data['date'])  // Better date filtering
                ->get()
                ->map(function ($trip) {
                    // Convert null advance_amount to 0 if needed
                    $trip->advance_amount = $trip->advance_amount ?? 0;
                    return $trip;
                });                        
            $transaction = DB::table('inventory_transactions as i_t')
            ->join('products as p', 'p.id', '=', 'i_t.product_id')
            ->join('drivers as d', function($join) use ($driver) {
                $join->where('d.id', '=', $driver->id)
                    ->where(DB::raw("SUBSTRING_INDEX(i_t.user, ' ', 1)"), '=', DB::raw('d.employeeid'))
                    ->where(DB::raw("REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(i_t.user, '(', -1), ')', 1), ')', '')"), '=', DB::raw('d.name'));
            })
            ->where('i_t.type', 5)
            ->where('i_t.created_at', '>=', $data['date'] . ' 00:00:00')
            ->where('i_t.created_at', '<', $data['date'] . ' 23:59:59')
            ->select('p.name', 'i_t.quantity')
            ->get();

            // $trip = Trip::where('driver_id', $driver->id)
            // ->where('date','>=',$data['date'].' 00:00:00')
            // ->where('date','<',$data['date'].' 23:59:59')
            // ->where('type',1) 
            // ->with('driver')
            // ->with('kelindan')
            // ->with('lorry')
            // ->get()
            // ->toArray();
            $result = [
                'sales' => round($sales,2),
                'cash' => round($cash,2),
                'cash_left' =>  ceil($cash_left),
                'bank_in' => round($bank_in,2),
                'wastage' => $transaction,
                'credit' => round($credit,2),
                'onlinebank' =>round($bank,2),
                'tng' =>round($tng,2),
                'cheque' =>round($cheque,2),
                'productsold' => [
                    'total_quantity' =>round($productsold,2),
                    'details' =>$solddetail
                ],
                'productfoc' => [
                    'total_quantity' =>round($productfoc,2),
                    'details' =>$focdetail
                ],
                'trip' => $trip
            ];
            return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.get_dashboard_successfully',
                'data' => $result
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getAllLanguages(Request $request)
    {
        $data = $request->all();
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }

        $languages = MobileTranslationVersion::with('language')->get();

        $translations = [];

        foreach ($languages as $languageVersion) {
            $translations[] = [
                'language' => $languageVersion->language->name, 
                'code'     => $languageVersion->language->code,  
                'version'  => $languageVersion->version,
            ];
        }
        return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator,
                'data' => $translations
            ], 200);
    }

    public function getTranslations(Request $request)
    {
        $data = $request->all();
        $driver = Driver::where('session', $request->header('session'))->first();
        if(empty($driver)){
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                'data' => null
            ], 401);
        }
        //validation
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]); 
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => __LINE__.$this->message_separator.$validator->errors()->first(),
                'data' => null
            ], 400);
        }
        $code = $data['code'];
        $language = Language::where('code', $code)->first();

        if(empty($language)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'Invalid Language Code',
                    'data' => null
                ], 401);
            }
        $version = MobileTranslationVersion::where('language_id', $language->id)->first();
        $translations = MobileTranslation::where('language_id', $language->id)
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        $result = [
            'version' => $version->version,
            'translation' => $translations
        ];

        return response()->json([
                'result' => true,
                'message' => __LINE__.$this->message_separator.'api.message.language_update_successfully',
                'data' => $result
            ], 200);
       
    }  
    
    //New function 
    public function getNotifications(Request $request)
    {
        try {
            $driver = Driver::where('session', $request->header('session'))->first();
            if(empty($driver)){
                return response()->json([
                    'result' => false,
                    'message' => __LINE__.$this->message_separator.'api.message.invalid_session',
                    'data' => null
                ], 401);
            }

            $notifications = DriverNotifications::where('driver_id', $driver->id)
                ->orderBy('created_at', 'desc')
                ->take(50) // Last 50 notifications
                ->get();

            return response()->json([
                'result' => true,
                'message' => 'Notifications retrieved',
                'data' => [
                    'notifications' => $notifications,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving notifications',
                'data' => null
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $driver = Driver::where('session', $request->header('session'))->first();
            if (empty($driver)) {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid session',
                    'data' => null
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'notification_id' => 'required|exists:driver_notifications,id'
            ]);
          
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }

            $notification = DriverNotifications::where('id', $request->notification_id)
                ->where('driver_id', $driver->id)
                ->first();

            if ($notification) {
                $notification->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
            }

            return response()->json([
                'result' => true,
                'message' => 'Notification marked as read',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error marking notification as read',
                'data' => null
            ], 500);
        }
    }

    public function registerFCMToken(Request $request)
    {
        try {

            $driver = Driver::where('session', $request->header('session'))->first();
            if (empty($driver)) {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid session',
                    'data' => null
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'fcm_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }
            // Update driver with FCM token
            $driver->update([
                'fcm_token' => $request->fcm_token,
                'fcm_token_updated_at' => now(),
            ]);

            return response()->json([
                'result' => true,
                'message' => 'FCM token registered successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Failed to register FCM token: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function removeFCMToken(Request $request)
    {
        try {
            $driver = Driver::where('session', $request->header('session'))->first();
            if (empty($driver)) {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid session',
                    'data' => null
                ], 401);
            }

            // Remove FCM token
            $driver->update([
                'fcm_token' => null,
                'fcm_token_updated_at' => null,
            ]);

            return response()->json([
                'result' => true,
                'message' => 'FCM token removed successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Failed to remove FCM token: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function bulkCheckInOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check_records' => 'required|array',
            'check_records.*.timestamp' => 'required|numeric', 
            'check_records.*.latitude' => 'required|numeric',
            'check_records.*.longitude' => 'required|numeric',
            'check_records.*.action' => 'required|in:checkin,checkout',
            'check_records.*.lorry_id' => 'nullable|sometimes|numeric', 
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null
            ], 422);
        }

        $driver = Driver::where('session', $request->header('session'))->first();
        if (empty($driver)) {
            return response()->json([
                'result' => false,
                'message' => 'Invalid session',
                'data' => null
            ], 401);
        }

        return $this->handleBulkCheckAction($request, $driver);
    }

    private function handleBulkCheckAction(Request $request, $driver)
    {
        try {
            $driver_id = $driver->id;
            $checkRecords = $request->input('check_records');
            $createdRecords = [];
            $errors = [];
            $skippedRecords = [];

            // Sort records by timestamp to ensure chronological processing
            usort($checkRecords, function($a, $b) {
                return $a['timestamp'] <=> $b['timestamp'];
            });

            foreach ($checkRecords as $index => $record) {
                try {
                    $timestamp = $record['timestamp'];
                    $latitude = $record['latitude'];
                    $longitude = $record['longitude'];
                    $action = $record['action'];
                    $lorryId = $record['lorry_id'];

                    // Convert timestamp to datetime
                    $timestampInSeconds = intval($timestamp / 1000);

                    // Convert timestamp to datetime
                    $checkTime = \Carbon\Carbon::createFromTimestamp($timestampInSeconds);
                    // Determine the type based on action
                    $type = ($action === 'checkin') ? DriverCheckIn::TYPE_CHECK_IN : DriverCheckIn::TYPE_CHECK_OUT;

                    $lastRecord = DriverCheckIn::where('driver_id', $driver_id)
                    ->orderBy('check_time', 'desc')
                    ->first();

                    // Handle lorry_id based on action
                    if ($action === 'checkin') {
                        // For check-in, lorry_id is required
                        if (!isset($record['lorry_id'])) {
                            $errors[] = "Record {$index}: Lorry ID is required for check-in";
                            continue;
                        }
                        $lorryId = $record['lorry_id'];
                    } else {
                        // For check-out, get lorry_id from last check-in record
                        if (!$lastRecord || $lastRecord->type !== DriverCheckIn::TYPE_CHECK_IN) {
                            $errors[] = "Record {$index}: No previous check-in found. Cannot determine lorry for check-out";
                            continue;
                        }
                        $lorryId = $lastRecord->lorry_id;
                    }
                    
                    // Check for duplicate record (same driver, lorry, action, and timestamp within 30 seconds)
                    $existingRecord = DriverCheckIn::where('driver_id', $driver_id)
                        ->where('lorry_id', $lorryId)
                        ->where('type', $type)
                        ->whereBetween('check_time', [
                            $checkTime->copy()->subSeconds(30),
                            $checkTime->copy()->addSeconds(30)
                        ])
                        ->first();

                    if ($existingRecord) {
                        $skippedRecords[] = [
                            'index' => $index,
                            'task_id' => $record['task_id'] ?? null,
                            'action' => $action,
                            'status' => 'skipped',
                            'message' => 'Duplicate record already exists'
                        ];
                        continue;
                    }

                    // Validate lorry exists
                    $lorry = Lorry::find($lorryId);
                    if (!$lorry) {
                        $errors[] = "Record {$index}: Lorry not found";
                        continue;
                    }

                    // Validate check-in/check-out sequence
                    if ($action === 'checkin') {
                        // For check-in: Verify that the last action was a check-out or no previous records
                        if ($lastRecord && $lastRecord->type !== DriverCheckIn::TYPE_CHECK_OUT) {
                            $errors[] = "Record {$index}: You must check out before checking in again";
                            continue;
                        }
                        
                        // Check if lorry is already in use by another driver
                        if ($lorry->in_use) {
                            $errors[] = "Record {$index}: Lorry is already in use by another driver";
                            continue;
                        }
                    }

                    if ($action === 'checkout') {
                        // For check-out: Verify that the last action was a check-in
                        if (!$lastRecord || $lastRecord->type !== DriverCheckIn::TYPE_CHECK_IN) {
                            $errors[] = "Record {$index}: You must check in before checking out";
                            continue;
                        }

                        // Verify that the checkout is for the same lorry as the last check-in
                        if ($lastRecord->lorry_id != $lorryId) {
                            $errors[] = "Record {$index}: Check-out must be for the same lorry as your last check-in (Lorry ID: {$lastRecord->lorry_id})";
                            continue;
                        }
                    }

                    // Create the record
                    $createdRecord = DriverCheckIn::create([
                        'driver_id' => $driver_id,
                        'lorry_id' => $lorryId,
                        'type' => $type,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'check_time' => $checkTime,
                    ]);

                    // Update lorry in_use status
                    if ($action === 'checkin') {
                        $lorry->markAsInUse();
                    } else {
                        $lorry->markAsAvailable();
                    }

                    $createdRecords[] = $createdRecord;

                } catch (\Exception $e) {
                    $errors[] = "Record {$index}: " . $e->getMessage();
                }
            }

            $response = [
                'result' => true,
                'message' => 'Bulk check-in/check-out processing completed',
                'data' => [
                    'processed_count' => count($checkRecords),
                    'success_count' => count($createdRecords),
                    'failed_count' => count($errors),
                    'skipped_count' => count($skippedRecords),
                    'records' => $createdRecords,
                    'skipped_records' => $skippedRecords
                ]
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            $statusCode = (count($errors) === count($checkRecords)) ? 422 : 
                        (count($errors) > 0 ? 207 : 200);

            return response()->json($response, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Bulk check-in/check-out failed',
                'error' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getDriverStatus(Request $request)
    {
        $driver = Driver::where('session', $request->header('session'))->first();
        if (empty($driver)) {
            return response()->json([
                'result' => false,
                'message' => 'Invalid session',
                'data' => null
            ], 401);
        }
        
        try {
             $driver_id = $driver->id;

            // Check if user already performed this action for this task
            $lastRecord = DriverCheckIn::where('driver_id', $driver_id)
            ->orderBy('check_time', 'desc')
            ->first();   
            if (!$lastRecord) {
                return response()->json([
                    'result' => false,
                    'message' => 'No check-in/check-out records found',
                    'data' => null
                ], 422);
            }
            return response()->json([
                'result' => true,
                'message' => 'Driver status retrieved successfully',
                'data' => $lastRecord,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Get Driver status failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }   

    public function startDelivery(Request $request)
    {
        $driver = Driver::where('session', $request->header('session'))->first();
        if (empty($driver)) {
            return response()->json([
                'result' => false,
                'message' => 'Invalid session',
                'data' => null
            ], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|numeric',
            'lorry_id' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null
            ], 422);
        }

        $taskId = $request->input('task_id');
        $lorryId = $request->input('lorry_id');

        try {
            // Find the task
            $task = Task::where('id', $taskId)
                ->where('lorry_id', $lorryId)
                ->first();

            if (!$task) {
                return response()->json([
                    'result' => false,
                    'message' => 'Task not found',
                    'data' => null
                ], 404);
            }

            // Check if task can be started (status should be 0 - New)
            if ($task->getStatusValue() != Task::STATUS_NEW) {
                return response()->json([
                    'result' => false,
                    'message' => 'Task cannot be started. Current status: ' . $task->status,
                    'data' => null
                ], 422);
            }

            // Start the delivery
            $started = $task->startTrip();

            $task['driver_id'] = $driver->id;
            $task->save();

            Trip::create([
                'date' => now(),
                'driver_id' => $driver->id,
                'lorry_id' => $lorryId,
                'task_id' => $taskId,
                'type' => 1, 
            ]);

            if ($started) {
                return response()->json([
                    'result' => true,
                    'message' => 'Delivery started successfully',
                    'data' => [
                        'task' => $task,
                        'countdown_minutes' => $task->getCountdownInMinutes(),
                        'countdown_formatted' => $task->getCountdownFormatted(),
                        'start_time' => $task->start_time,
                        'estimated_completion_time' => $task->getEstimatedCompletionTime(),
                    ]
                ], 200);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => 'Failed to start delivery',
                    'data' => null
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Start delivery failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }   

    public function completeDelivery(Request $request)
    {
        try {
            // Validate session
            $driver = Driver::where('session', $request->header('session'))->first();
            if (empty($driver)) {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid session',
                    'data' => null
                ], 401);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|numeric',
                'lorry_id' => 'required|numeric',
                'delivery_order_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'proof_of_delivery_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }
            $taskId = $request->input('task_id');
            $lorryId = $request->input('lorry_id');

            // Find the task
            $task = Task::where('id', $taskId)
                ->where('driver_id', $driver->id)
                ->first();

            if (!$task) {
                return response()->json([
                    'result' => false,
                    'message' => 'Task not found',
                    'data' => null
                ], 404);
            }

            // Check if task can be completed (status should be 1 - Delivering)
            if ($task->getStatusValue() !== Task::STATUS_DELIVERING) {
                return response()->json([
                    'result' => false,
                    'message' => 'Task cannot be completed. Current status: ' . $task->status,
                    'data' => null
                ], 422);
            }

            // Upload images and get file details
            $deliveryOrderImageDetails = null;
            $proofOfDeliveryImageDetails = null;

            if ($request->hasFile('delivery_order_image')) {
                $deliveryOrderImageFile = $request->file('delivery_order_image');
                $deliveryOrderImagePath = $deliveryOrderImageFile->store('delivery-images', 'public');
                
                $deliveryOrderImageDetails = [
                    'url' => asset($deliveryOrderImagePath),
                    'path' => $deliveryOrderImagePath,
                    'original_name' => $deliveryOrderImageFile->getClientOriginalName(),
                    'size' => $deliveryOrderImageFile->getSize(),
                    'mime_type' => $deliveryOrderImageFile->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }

            if ($request->hasFile('proof_of_delivery_image')) {
                $proofOfDeliveryImageFile = $request->file('proof_of_delivery_image');
                $proofOfDeliveryImagePath = $proofOfDeliveryImageFile->store('proof-of-delivery', 'public');
                
                $proofOfDeliveryImageDetails = [
                    'url' => asset($proofOfDeliveryImagePath),
                    'path' => $proofOfDeliveryImagePath,
                    'original_name' => $proofOfDeliveryImageFile->getClientOriginalName(),
                    'size' => $proofOfDeliveryImageFile->getSize(),
                    'mime_type' => $proofOfDeliveryImageFile->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }

            // End the trip (this updates status to completed but doesn't change progress)
            // Progress was already added when task was created
            $ended = $task->endTrip();

            if (!$ended) {
                return response()->json([
                    'result' => false,
                    'message' => 'Failed to complete delivery',
                    'data' => null
                ], 500);
            }

            // Check if trip already ended (admin might have ended it)
            $existingEndTrip = Trip::where('task_id', $taskId)
                ->where('driver_id', $driver->id)
                ->where('lorry_id', $lorryId)
                ->where('type', 0) // End trip
                ->first();

            if ($existingEndTrip) {
                // Update the existing end trip date to now
                $existingEndTrip->update([
                    'date' => now()
                ]);
            } else {
                // Create new end trip record
                Trip::create([
                    'date' => now(),
                    'driver_id' => $driver->id,
                    'lorry_id' => $lorryId,
                    'task_id' => $taskId,
                    'type' => 0, 
                ]);
            }

            // Save delivery images
            $deliveryImage = DeliveryImage::updateOrCreate(
                ['task_id' => $task->id],
                [
                    'delivery_order_image_path' => $deliveryOrderImagePath ?? null,
                    'proof_of_delivery_image_path' => $proofOfDeliveryImagePath ?? null,
                ]
            );

            return response()->json([
                'result' => true,
                'message' => 'Delivery completed successfully',
                'data' => [
                    'task' => $task,
                    'submitted_images' => [
                        'delivery_order_image' => $deliveryOrderImageDetails,
                        'proof_of_delivery_image' => $proofOfDeliveryImageDetails,
                    ],
                    'completion_details' => [
                        'end_time' => $task->end_time,
                        'time_taken' => $task->time_taken,
                        'time_taken_formatted' => $task->getTimeTakenFormatted(),
                        'is_late' => $task->is_late,
                        'completion_time' => now()->toDateTimeString(),
                    ],
                    // IMPORTANT: No progress update here - already done when task was created
                    'progress_info' => [
                        'delivery_order_progress' => $task->deliveryOrder->progress_total ?? 0,
                        'total_order' => $task->deliveryOrder->total_order ?? 0,
                        'this_load' => $task->this_load,
                        'note' => 'Progress was already added when task was created'
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Complete delivery failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function returnDelivery(Request $request)
    {
        try {
            // Validate session
            $driver = Driver::where('session', $request->header('session'))->first();
            if (empty($driver)) {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid session',
                    'data' => null
                ], 401);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|numeric',
                'lorry_id' => 'required|numeric',
                'return_reason' => 'required|string|max:255',
                'return_remarks' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }
            $taskId = $request->input('task_id');
            $lorryId = $request->input('lorry_id');

            // Find the task
            $task = Task::where('id', $taskId)
                ->where('driver_id', $driver->id)
                ->first();

            if (!$task) {
                return response()->json([
                    'result' => false,
                    'message' => 'Task not found',
                    'data' => null
                ], 404);
            }

            // Check if task can be returned
            if (!$task->canReturn()) {
                return response()->json([
                    'result' => false,
                    'message' => 'Task cannot be returned. Current status: ' . $task->status,
                    'data' => null
                ], 422);
            }

            $returned = $task->markAsReturned(
                $request->return_reason,
                $request->return_remarks,
                now()
            );
            
            if (!$returned) {
                return response()->json([
                    'result' => false,
                    'message' => 'Failed to mark delivery as returned',
                    'data' => null
                ], 500);
            }

            Trip::create([
                'date' => now(),
                'driver_id' => $driver->id,
                'lorry_id' => $lorryId,
                'task_id' => $taskId,
                'type' => 0, 
            ]);

            // Refresh task to get updated delivery order progress
            $task->refresh();
            $deliveryOrder = $task->deliveryOrder;

            return response()->json([
                'result' => true,
                'message' => 'Delivery marked as returned successfully',
                'data' => [
                    'task' => $task,
                    'return_details' => [
                        'return_reason' => $task->return_reason,
                        'return_remarks' => $task->return_remarks,
                        'effective_delivered_quantity' => $task->getEffectiveDeliveredQuantity(),
                    ],
                    'progress_update' => [
                        'previous_progress' => ($deliveryOrder->progress_total + $task->this_load) ?? 0,
                        'current_progress' => $deliveryOrder->progress_total ?? 0,
                        'deducted_amount' => $task->this_load,
                        'delivery_order_status' => $deliveryOrder->status ?? 'N/A',
                        'note' => 'Progress deducted for returned task'
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Return delivery failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }


    public function bulkDelivery(Request $request)
    {   
        
        $validator = Validator::make($request->all(), [
            'delivery_records' => 'sometimes|array',
            'delivery_records.*.task_id' => 'required|numeric',
            'delivery_records.*.timestamp' => 'required|numeric',
            'delivery_records.*.action' => 'required|in:start,submit,return',
            'delivery_records.*.lorry_id' => 'required|numeric', 
            'delivery_records.*.delivery_order_image' => 'sometimes|required_if:delivery_records.*.action,submit|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delivery_records.*.proof_of_delivery_image' => 'sometimes|required_if:delivery_records.*.action,submit|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delivery_records.*.return_reason' => 'required_if:delivery_records.*.action,return|string|max:255',
            'delivery_records.*.return_remarks' => 'nullable|string|max:500',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null
            ], 422);
        }

        $driver = Driver::where('session', $request->header('session'))->first();
        if (empty($driver)) {
            return response()->json([
                'result' => false,
                'message' => 'Invalid session',
                'data' => null
            ], 401);
        }   

        $deliveryRecords = $request->input('delivery_records', []);
    
        // Handle empty array
        if (empty($deliveryRecords)) {
            return response()->json([
                'result' => true,
                'message' => 'No delivery records to process',
                'data' => [
                    'processed_count' => 0,
                    'success_count' => 0,
                    'failed_count' => 0,
                    'skipped_count' => 0,
                    'records' => []
                ]
            ], 200);
        }
        
        return $this->handleBulkDeliveryAction($request, $driver);
    }

    private function handleBulkDeliveryAction(Request $request, $driver)
    {
        try {
            $driver_id = $driver->id;
            $deliveryRecords = $request->input('delivery_records');
            $processedRecords = [];
            $errors = [];

            foreach ($deliveryRecords as $index => $record) {
                try {
                    $taskId = $record['task_id'];
                    $timestamp = $record['timestamp'];
                    $action = $record['action'];

                    $deliveryOrderImage = $request->file("delivery_records.{$index}.delivery_order_image");
                    $proofOfDeliveryImage = $request->file("delivery_records.{$index}.proof_of_delivery_image");
                    
                    // Replace the file info with actual file objects in the record
                    if ($deliveryOrderImage) {
                        $record['delivery_order_image'] = $deliveryOrderImage;
                    }
                    if ($proofOfDeliveryImage) {
                        $record['proof_of_delivery_image'] = $proofOfDeliveryImage;
                    }
                    
                    // Convert timestamp to datetime
                    $timestampInSeconds = intval($timestamp / 1000);

                    // Convert timestamp to datetime
                    $actionTime = \Carbon\Carbon::createFromTimestamp($timestampInSeconds);
                    // Find the task
                    $task = Task::where('id', $taskId)
                        ->first();

                    if (!$task) {
                        $errors[] = "Record {$index}: Task not found";
                        $processedRecords[] = [
                            'index' => $index,
                            'task_id' => $taskId,
                            'action' => $action,
                            'status' => 'failed',
                            'error' => 'Task not found'
                        ];
                        continue;
                    }

                    // Check if action is already completed (skip duplicates)
                    if ($this->isActionAlreadyCompleted($task, $action)) {
                        $processedRecords[] = [
                            'index' => $index,
                            'task_id' => $taskId,
                            'action' => $action,
                            'status' => 'skipped',
                            'message' => 'Action already completed'
                        ];
                        continue;
                    }

                    // Handle different actions
                    if ($action === 'start') {
                        $result = $this->processDeliveryStart($task, $record, $driver, $actionTime);
                    } elseif ($action === 'submit') {
                        $result = $this->processDeliverySubmission($task, $record, $driver, $actionTime);
                    } else {
                        $result = $this->processDeliveryReturn($task, $record, $driver, $actionTime);
                    }

                    if ($result['success']) {
                        $processedRecords[] = [
                            'index' => $index,
                            'task_id' => $taskId,
                            'action' => $action,
                            'status' => 'success',
                            'data' => $result['data']
                        ];
                    } else {
                        $errors[] = "Record {$index}: " . $result['message'];
                        $processedRecords[] = [
                            'index' => $index,
                            'task_id' => $taskId,
                            'action' => $action,
                            'status' => 'failed',
                            'error' => $result['message']
                        ];
                    }

                } catch (\Exception $e) {
                    $errors[] = "Record {$index}: " . $e->getMessage();
                    $processedRecords[] = [
                        'index' => $index,
                        'task_id' => $taskId ?? 'unknown',
                        'action' => $action ?? 'unknown',
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                }
            }

            $response = [
                'result' => true,
                'message' => 'Bulk delivery processing completed',
                'data' => [
                    'processed_count' => count($processedRecords),
                    'success_count' => count(array_filter($processedRecords, function($item) {
                        return $item['status'] === 'success';
                    })),
                    'failed_count' => count(array_filter($processedRecords, function($item) {
                        return $item['status'] === 'failed';
                    })),
                    'skipped_count' => count(array_filter($processedRecords, function($item) {
                        return $item['status'] === 'skipped';
                    })),
                    'processed_records' => $processedRecords
                ]
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            $statusCode = (count($errors) === count($deliveryRecords)) ? 422 : 
                        (count($errors) > 0 ? 207 : 200);

            return response()->json($response, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Bulk delivery processing failed',
                'error' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Check if the action is already completed to prevent duplicates
     */
    private function isActionAlreadyCompleted($task, $action)
    {
        $currentStatus = $task->getStatusValue();
        
        switch ($action) {
            case 'start':
                // If task is already delivering or completed/returned, start is already done
                return $currentStatus !== Task::STATUS_NEW;
                
            case 'submit':
                // If task is already completed, submit is already done
                return $currentStatus === Task::STATUS_COMPLETED;
                
            case 'return':
                // If task is already returned, return is already done
                return $currentStatus === Task::STATUS_RETURNED;
                
            default:
                return false;
        }
    }

    private function processDeliveryStart($task, $record, $driver, $actionTime)
    {
        // Check if task can be started (status should be 0 - New)
        if ($task->getStatusValue() !== Task::STATUS_NEW) {
            return [
                'success' => false,
                'message' => 'Task cannot be started. Current status: ' . $task->status
            ];
        }

        // Validate lorry_id exists
        $lorryId = $record['lorry_id'] ?? null;
        if (!$lorryId) {
            return [
                'success' => false,
                'message' => 'Lorry ID is required'
            ];
        }

        $lorry = Lorry::find($lorryId);
        if (!$lorry) {
            return [
                'success' => false,
                'message' => 'Invalid lorry ID'
            ];
        }

        // Start the delivery with provided timestamp
        $started = $task->startTrip($actionTime);

        if (!$started) {
            return [
                'success' => false,
                'message' => 'Failed to start delivery'
            ];
        }

        // Store driver_id in the task
        $task->driver_id = $driver->id;
        $task->save();


        // Create trip record with lorry_id and action time
        Trip::create([
            'date' => $actionTime,
            'driver_id' => $driver->id,
            'lorry_id' => $lorryId,
            'task_id' => $task->id,
            'type' => 1, 
        ]);

        return [
            'success' => true,
            'message' => 'Delivery started successfully',
            'data' => [
                'task' => $task,
                'countdown_minutes' => $task->getCountdownInMinutes(),
                'countdown_formatted' => $task->getCountdownFormatted(),
                'start_time' => $task->start_time,
                'estimated_completion_time' => $task->getEstimatedCompletionTime(),
            ]
        ];
    }

  private function processDeliverySubmission($task, $record, $driver, $actionTime)
    {
        // Check if task can be completed (status should be 1 - Delivering)
        if ($task->getStatusValue() !== Task::STATUS_DELIVERING) {
            return [
                'success' => false,
                'message' => 'Task cannot be completed. Current status: ' . $task->status
            ];
        }

        // Validate lorry_id exists
        $lorryId = $record['lorry_id'] ?? null;
        if (!$lorryId) {
            return [
                'success' => false,
                'message' => 'Lorry ID is required'
            ];
        }

        $lorry = Lorry::find($lorryId);
        if (!$lorry) {
            return [
                'success' => false,
                'message' => 'Invalid lorry ID'
            ];
        }

        // Upload images only if provided and task is not already completed
        $deliveryOrderImagePath = null;
        $proofOfDeliveryImagePath = null;

        if (isset($record['delivery_order_image']) && $record['delivery_order_image'] instanceof \Illuminate\Http\UploadedFile) {
            $deliveryOrderImagePath = $record['delivery_order_image']->store('delivery-images', 'public');
        } else {
            // If no image provided, check if images already exist in database
            $existingImages = DeliveryImage::where('task_id', $task->id)->first();
            if ($existingImages) {
                $deliveryOrderImagePath = $existingImages->delivery_order_image_path;
                $proofOfDeliveryImagePath = $existingImages->proof_of_delivery_image_path;
            }
        }

        if (isset($record['proof_of_delivery_image']) && $record['proof_of_delivery_image'] instanceof \Illuminate\Http\UploadedFile) {
            $proofOfDeliveryImagePath = $record['proof_of_delivery_image']->store('proof-of-delivery', 'public');
        }

        // End the trip with the provided timestamp
        // This will NOT update progress (already done when task was created)
        $ended = $task->endTrip($actionTime);

        if (!$ended) {
            return [
                'success' => false,
                'message' => 'Failed to complete delivery'
            ];
        }

        // Create trip record with lorry_id and action time
        Trip::create([
            'date' => $actionTime,
            'driver_id' => $driver->id,
            'lorry_id' => $lorryId,
            'task_id' => $task->id,
            'type' => 0, 
        ]);

        // Save delivery images only if we have new paths
        if ($deliveryOrderImagePath || $proofOfDeliveryImagePath) {
            $deliveryImage = DeliveryImage::updateOrCreate(
                ['task_id' => $task->id],
                [
                    'delivery_order_image_path' => $deliveryOrderImagePath,
                    'proof_of_delivery_image_path' => $proofOfDeliveryImagePath,
                ]
            );
        }

        return [
            'success' => true,
            'message' => 'Delivery submitted successfully',
            'data' => [
                'task' => $task,
                'completion_details' => [
                    'end_time' => $task->end_time,
                    'time_taken' => $task->time_taken,
                    'time_taken_formatted' => $task->getTimeTakenFormatted(),
                    'is_late' => $task->is_late,
                    'completion_time' => $actionTime->toDateTimeString(),
                ],
                'progress_note' => 'No progress change - already added when task was created'
            ]
        ];
    }

    private function processDeliveryReturn($task, $record, $driver, $actionTime) 
    {
        // Check if task can be returned
        if (!$task->canReturn()) {
            return [
                'success' => false,
                'message' => 'Task cannot be returned. Current status: ' . $task->status
            ];
        }
        $lorryId = $record['lorry_id'] ?? null;

        // Update task as returned with the provided timestamp
        // This will trigger handleReturn() which calls deductProgress()
        $returned = $task->markAsReturned(
            $record['return_reason'],
            $record['return_remarks'] ?? null,
            $actionTime
        );

        Trip::create([
            'date' => $actionTime,
            'driver_id' => $driver->id,
            'lorry_id' => $lorryId,
            'task_id' => $task->id,
            'type' => 0, 
        ]);

        if (!$returned) {
            return [
                'success' => false,
                'message' => 'Failed to mark delivery as returned'
            ];
        }

        // Refresh to get updated delivery order data
        $task->refresh();
        $deliveryOrder = $task->deliveryOrder;

        return [
            'success' => true,
            'message' => 'Delivery marked as returned successfully',
            'data' => [
                'task' => $task,
                'return_details' => [
                    'return_reason' => $task->return_reason,
                    'return_remarks' => $task->return_remarks,
                    'effective_delivered_quantity' => $task->getEffectiveDeliveredQuantity(),
                    'return_time' => $actionTime->toDateTimeString(),
                ],
                'progress_update' => [
                    'deducted_amount' => $task->this_load,
                    'delivery_order_progress' => $deliveryOrder->progress_total ?? 0,
                    'delivery_order_status' => $deliveryOrder->status ?? 'N/A'
                ]
            ]
        ];
    }


}   
