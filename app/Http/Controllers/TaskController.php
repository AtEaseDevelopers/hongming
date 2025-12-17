<?php

namespace App\Http\Controllers;

use App\DataTables\TaskDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Repositories\TaskRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Models\Task;
use App\Models\DriverNotifications;
use App\Models\DeliveryOrder;
use App\Models\DriverCheckIn;
use App\Models\Driver;
use App\Models\Lorry;
use App\Models\Company;
use App\Models\DeliveryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\OneSignalNotificationService;

class TaskController extends AppBaseController
{
    /** @var TaskRepository $taskRepository*/
    private $taskRepository;

    public function __construct(TaskRepository $taskRepo)
    {
        $this->taskRepository = $taskRepo;
    }

    /**
     * Display a listing of the Task.
     *
     * @param TaskDataTable $taskDataTable
     *
     * @return Response
     */
    public function index(TaskDataTable $taskDataTable)
    {
        return $taskDataTable->render('tasks.index');
    }

    /**
     * Show the form for creating a new Task.
     *
     * @return Response
     */
    public function create()
    {
        $delivery_order_id = null;
        // Check if delivery_order_id is passed in the request
        if (request()->has('delivery_order_id')) {
            try {
                $encryptedId = request('delivery_order_id');
                $delivery_order_id = Crypt::decrypt($encryptedId); 
                // Verify the delivery order exists and is available for task creation
                $deliveryOrder = DeliveryOrder::where('id', $delivery_order_id)
                    ->where('status','!=', 6) // Only ready to deliver orders
                    ->first();

                if (!$deliveryOrder) {
                    Flash::error('Delivery Order not found or not available for task creation.');
                    // Continue with normal create but without pre-selection
                    $delivery_order_id = null;
                }
            } catch (\Exception $e) {
                Flash::error('Invalid delivery order ID.');
                $delivery_order_id = null;
            }
        }

        // Get delivery orders and drivers for dropdowns
        $delivery_order = DeliveryOrder::where('status', 1) // Only ready to deliver orders
            ->pluck('dono', 'id');
        $lorryItems = Lorry::pluck('lorryno', 'id');

        return view('tasks.create', compact('delivery_order', 'lorryItems', 'delivery_order_id'));
    }

    /**
     * Store a newly created Task in storage.
     *
     * @param CreateTaskRequest $request
     *
     * @return Response
     */
    public function store(CreateTaskRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $input = $request->all();
            
            $input['date'] = date_create($input['date']);
            
            $task = $this->taskRepository->create($input);

            // Handle image uploads if status is completed
            if ($input['status'] == 2) { // Completed status
                $this->handleImageUploads($request, $task->id);
            }
            
            $this->sendTaskAssignedNotification($task);
            DB::commit();
            
            Flash::success(__('tasks.task_saved_successfully'));
            return redirect(route('tasks.index'));

        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error saving task: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified Task.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $task = $this->taskRepository->find($id);

        if (empty($task)) {
            Flash::error(__('tasks.task_not_found'));
            return redirect(route('tasks.index'));
        }

        return view('tasks.show')->with('task', $task);
    }

    /**
     * Show the form for editing the specified Task.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $task = $this->taskRepository->find($id);

        if (empty($task)) {
            Flash::error(__('tasks.task_not_found'));
            return redirect(route('tasks.index'));
        }

        // Get delivery orders and drivers for dropdowns
        $delivery_order = DeliveryOrder::where('status', 1)->pluck('dono', 'id');
        $lorryItems = Lorry::pluck('lorryno', 'id');
        return view('tasks.edit', compact('task', 'delivery_order', 'lorryItems'));
    }

    /**
     * Update the specified Task in storage.
     *
     * @param int $id
     * @param UpdateTaskRequest $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $id = Crypt::decrypt($id);
        $task = $this->taskRepository->find($id);

        if (empty($task)) {
            Flash::error(__('tasks.task_not_found'));
            return redirect(route('tasks.index'));
        }

        $request->validate([
            'date' => 'required',
            'task_number' => 'required|string|max:50',
            'lorry_id' => 'required',
            'company_id' => 'required',
            'invoice_id' => 'nullable',
            'status' => 'required',
            'countdown' => 'required|integer|min:1|max:1440',
            'delivery_order_id' => [
                'required',
                'exists:delivery_orders,id',
                function ($attribute, $value, $fail) {
                    $deliveryOrder = DeliveryOrder::find($value);
                    if ($deliveryOrder && $deliveryOrder->status == 6) {
                        $fail('The selected Project Number is already in Completed Status. You are not allowed to edit completed Project Number.');
                    }
                }
            ],            
            'this_load' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request, $task) {
                    $intValue = (int) $value;
                    $currentThisLoad = (int) $task->this_load;
                    
                    // Prevent editing this_load if task is already completed
                    if ($task->getStatusValue() == 2 && $intValue != $currentThisLoad) {
                        $fail("This Load cannot be changed for completed tasks.");
                        return;
                    }

                    $deliveryOrderId = $request->input('delivery_order_id');
                    if ($deliveryOrderId) {
                        $deliveryOrder = DeliveryOrder::find($deliveryOrderId);
                        if ($deliveryOrder) {
                            $maxLoad = $deliveryOrder->total_order - ($deliveryOrder->progress_total - $task->this_load);
                            if ($value > $maxLoad) {
                                $fail("This Load cannot be greater than {$maxLoad}.");
                            }
                        }
                    }
                }
            ],
            'return_reason' => [
                function ($attribute, $value, $fail) use ($request, $task) {
                    // Only require return_reason when changing status to returned (3)
                    // For already returned tasks, return_reason is optional during edit
                    if ($request->status == 3 && $task->status != 3 && empty($value)) {
                        $fail('The return reason is required when returning the task.');
                    }
                },
                'nullable',
                'string',
                'max:255'
            ],
            'return_remarks' => 'nullable|string|max:255',
            'signed_do_image' => [
                function ($attribute, $value, $fail) use ($request, $task) {
                    // Only require image when changing status to completed (2)
                    // For already completed tasks, image is optional during edit
                    if ($request->status == 2 && $task->status != 2 && !$request->hasFile('signed_do_image')) {
                        $fail('The signed DO image is required when completing the task.');
                    }
                },
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:5120'
            ],
            'proof_of_delivery_image' => [
                function ($attribute, $value, $fail) use ($request, $task) {
                    // Only require image when changing status to completed (2)
                    // For already completed tasks, image is optional during edit
                    if ($request->status == 2 && $task->status != 2 && !$request->hasFile('proof_of_delivery_image')) {
                        $fail('The proof of delivery image is required when completing the task.');
                    }
                },
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:5120'
            ],
        ], [
            'company_id.required' => 'The Branch field is required.',
            'company_id.exists' => 'The selected Branch is invalid.',
        ]);

        DB::beginTransaction();
        
        try {
            $input = $request->all();
            $input['date'] = date_create($input['date']);
            // If task is already completed, prevent changing this_load
            if ($task->status == 2) {
                $input['this_load'] = $task->this_load; // Keep original value
            }

            // If task is already returned, preserve original return_reason if not provided
            if ($task->status == 3 && empty($input['return_reason'])) {
                $input['return_reason'] = $task->return_reason; // Keep original value
            }
            
            $task = $this->taskRepository->update($input, $id);

            // OR if task is already completed and new images are provided
            if ($request->status == 2) {
                if ($request->hasFile('signed_do_image') || $request->hasFile('proof_of_delivery_image')) {
                    $this->handleImageUploads($request, $task->id);
                }            
            }

            DB::commit();
            
            Flash::success(__('tasks.task_updated_successfully'));
            return redirect(route('tasks.index'));

        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error updating task: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Handle image uploads for delivery images
     */
    private function handleImageUploads($request, $taskId)
    {
        $signedDoPath = null;
        $proofOfDeliveryPath = null;

        // Upload signed DO image
        if ($request->hasFile('signed_do_image')) {
            $signedDoPath = $request->file('signed_do_image')->store('delivery-images', 'public');
        }

        // Upload proof of delivery image
        if ($request->hasFile('proof_of_delivery_image')) {
            $proofOfDeliveryPath = $request->file('proof_of_delivery_image')->store('proof-of-delivery', 'public');
        }

        // Create or update delivery images record
        DeliveryImage::updateOrCreate(
            ['task_id' => $taskId],
            [
                'delivery_order_image_path' => $signedDoPath,
                'proof_of_delivery_image_path' => $proofOfDeliveryPath,
            ]
        );
    }   

    public function downloadImage($type, $taskId)
    {
        try {
            $taskId = Crypt::decrypt($taskId);
            $task = $this->taskRepository->find($taskId);

            if (empty($task)) {
                Flash::error(__('tasks.task_not_found'));
                return redirect(route('tasks.index'));
            }

            if (!$task->deliveryImage) {
                Flash::error('No delivery images found for this task.');
                return redirect()->back();
            }

            $imagePath = null;
            $filename = '';

            switch ($type) {
                case 'signed_do':
                    $imagePath = $task->deliveryImage->delivery_order_image_path;
                    $filename = 'signed-delivery-order-' . $task->id . '.' . pathinfo($imagePath, PATHINFO_EXTENSION);
                    break;
                case 'proof_of_delivery':
                    $imagePath = $task->deliveryImage->proof_of_delivery_image_path;
                    $filename = 'proof-of-delivery-' . $task->id . '.' . pathinfo($imagePath, PATHINFO_EXTENSION);
                    break;
                default:
                    Flash::error('Invalid image type.');
                    return redirect()->back();
            }

            if (!$imagePath || !Storage::disk('public')->exists($imagePath)) {
                Flash::error('Image file not found.');
                return redirect()->back();
            }

            return Storage::disk('public')->download($imagePath, $filename);

        } catch (\Exception $e) {
            Flash::error('Error downloading image: ' . $e->getMessage());
            return redirect()->back();
        }
    }

     public function getNextTaskNumber(Request $request)
    {
        try {
            $companyId = $request->get('company_id');
            
            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company ID is required'
                ], 400);
            }

            $taskNumber = $this->generateNextTaskNumber($companyId);
            
            if (!$taskNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate task number'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'task_number' => $taskNumber
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating task number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate next sequential task number for a company
     *
     * @param int $companyId
     * @return string|null
     */
    private function generateNextTaskNumber($companyId)
    {
        $company = Company::find($companyId);
        if (!$company) {
            return null;
        }
        
        $prefix = $company->task_prefix;
        
        if (empty($prefix)) {
            // Use default prefix if not set
            $prefix = 'DO';
        }
        
        
        // Get the last task number for this company with the same prefix
        // Tasks are linked to delivery orders, which are linked to companies
        $lastTask = Task::whereHas('deliveryOrder', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('task_number', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();
        if ($lastTask && $lastTask->task_number) {
            // Extract the number part using the prefix
            $numberPart = str_replace($prefix . '-', '', $lastTask->task_number);
            $lastNumber = intval($numberPart);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Remove the specified Task from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $task = $this->taskRepository->find($id);

        if (empty($task)) {
            Flash::error(__('tasks.task_not_found'));
            return redirect(route('tasks.index'));
        }

        $this->taskRepository->delete($id);

        Flash::success(__('tasks.task_deleted_successfully'));
        return redirect(route('tasks.index'));
    }

    public function getDeliveryOrderDetails(Request $request)
    {
        try {
            $deliveryOrderId = $request->delivery_order_id;
            $deliveryOrder = DeliveryOrder::with(['customer', 'product'])->find($deliveryOrderId);

            if (!$deliveryOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery order not found'
                ]);
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => $deliveryOrder->customer->name ?? 'N/A',
                    'product' => $deliveryOrder->product->name ?? 'N/A',
                    'product_countdown' => $deliveryOrder->product->countdown ?? 60, // Add product countdown
                    'progress_total' => $deliveryOrder->progress_total,
                    'total_order' => $deliveryOrder->total_order,
                    'destination' => $deliveryOrder->place_name,
                    'this_load' => $deliveryOrder->getRemainingQuantity(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading delivery order details'
            ]);
        }
    }

    private function sendTaskAssignedNotification(Task $task)
    {
        try {
            $driverCheckIn = DriverCheckIn::where('type', 'check_in')
                ->where('lorry_id', $task->lorry_id)
                ->latest('check_time') 
                ->first();
            if($driverCheckIn){
                $driver = Driver::find($driverCheckIn->driver_id);
                $notificationService  = new OneSignalNotificationService();

                $notificationService->sendToDriver(
                    $driver->id,
                    'New Task Assigned',
                    "You have been assigned a new task (" . $task->deliveryOrder->dono . ") for (".$task->deliveryOrder->customer->company.") on ". $task->date,
                    [
                        'task_id' => $task->id,
                        'delivery_order_id' => $task->delivery_order_id,
                        'customer_name' => $task->deliveryOrder->customer->company,
                        'do_number' => $task->deliveryOrder->dono,
                        'date' => $task->date,
                        'type' => 'task_assigned',
                        'action' => 'view_task', // For mobile app to handle
                    ]
                );
                \Log::info("Notification sent to driver {$driver->id} for task {$task->id}");
            }
            \Log::info("No checked-in driver found for lorry {$task->lorry_id}. Notification not sent.");
                return;
        } catch (\Exception $e) {
            \Log::error('Error sending notification: ' . $e->getMessage());
        }
    }

    /**
     * Update task status via modal (Complete/Return)
     *
     * @param Request $request
     * @return Response
     */
    public function updateStatusViaModal(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Decrypt task ID
            $taskId = $request->task_id;
            $task = $this->taskRepository->find($taskId);
            if (empty($task)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }

            // Validate request
            $validator = \Validator::make($request->all(), [
                'status' => 'required|in:2,3',
                'return_reason' => [
                    'required_if:status,3',
                    'nullable',
                    'string',
                    'max:255'
                ],
                'return_remarks' => 'nullable|string|max:255',
                'signed_do_image' => [
                    'required_if:status,2',
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,gif',
                    'max:5120'
                ],
                'proof_of_delivery_image' => [
                    'required_if:status,2',
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,gif',
                    'max:5120'
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => implode(' ', $validator->errors()->all())
                ], 422);
            }

            // Prevent updating if task is already completed (unless returning)
            if ($task->getStatusValue() == 2 && $request->status != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task is already completed and cannot be updated.'
                ], 422);
            }

            // Handle different status updates using existing methods
            if ($request->status == 2) {
                // COMPLETED STATUS - Use endTrip method
                if ($task->getStatusValue() !== Task::STATUS_DELIVERING) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Task must be in delivering status to be marked as completed.'
                    ], 422);
                }
                
                $success = $task->endTrip(now());
                
                if (!$success) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to complete task. Ensure task is in delivering status.'
                    ], 422);
                }
                
                // Handle image uploads for completed task
                $this->handleImageUploads($request, $taskId);
                
            } elseif ($request->status == 3) {
                // RETURNED STATUS - Use markAsReturned method
                if (!$task->canReturn()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Task cannot be returned. Only delivering or completed tasks can be returned.'
                    ], 422);
                }
                
                $success = $task->markAsReturned(
                    $request->return_reason,
                    $request->return_remarks,
                    now()
                );
                
                if (!$success) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to mark task as returned. Task may not be in a returnable state.'
                    ], 422);
                }
            }

            // Refresh task data from database
            $task->refresh();

            // Send notification to driver
            $this->sendTaskStatusUpdatedNotification($task);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating task status via modal: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating task status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to driver when task status is updated by admin
     *
     * @param Task $task
     * @return void
     */
    private function sendTaskStatusUpdatedNotification(Task $task)
    {
        try {
            $driverCheckIn = DriverCheckIn::where('type', 'check_in')
                ->where('lorry_id', $task->lorry_id)
                ->latest('check_time')
                ->first();
                
            if ($driverCheckIn) {
                $driver = Driver::find($driverCheckIn->driver_id);
                $notificationService = new OneSignalNotificationService();

                // Determine status message
                $statusMessages = [
                    2 => 'Completed',
                    3 => 'Returned'
                ];
                
                $statusText = $statusMessages[$task->getStatusValue()] ?? 'Updated';
                
                // Create notification message
                $message = "Your task ({$task->deliveryOrder->dono}) for ({$task->deliveryOrder->customer->company}) has been marked as {$statusText} by admin.";
                
                if ($task->getStatusValue() == 3 && $task->return_reason) {
                    $message .= " Reason: {$task->return_reason}";
                }
                
                $message .= " For further information, please contact administration.";

                $notificationService->sendToDriver(
                    $driver->id,
                    'Task Status Updated',
                    $message,
                    [
                        'task_id' => $task->id,
                        'delivery_order_id' => $task->delivery_order_id,
                        'customer_name' => $task->deliveryOrder->customer->company,
                        'do_number' => $task->deliveryOrder->dono,
                        'status' => $task->getStatusValue(),
                        'status_text' => $statusText,
                        'type' => 'task_status_updated',
                        'action' => 'view_task',
                    ]
                );
                
                \Log::info("Task status update notification sent to driver {$driver->id} for task {$task->id}");
            } else {
                \Log::info("No checked-in driver found for lorry {$task->lorry_id}. Notification not sent.");
            }
        } catch (\Exception $e) {
            \Log::error('Error sending task status update notification: ' . $e->getMessage());
        }
    }

}   