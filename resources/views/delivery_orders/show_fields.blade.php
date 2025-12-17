@push('styles')
<style>
    /* Set base font size for the entire page */
    body {
        font-size: 14px; /* You can adjust this value as needed */
    }
    
    .form-group label {
        font-weight: bold;
        text-decoration: underline;
    }
    
    .task-card {
        border-left: 4px solid #007bff;
        margin-bottom: 15px;
    }
    
    .task-status-badge {
        font-size: 0.8em;
    }
    
    .progress-container {
        background: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .task-actions .btn {
        margin-right: 5px;
        margin-bottom: 5px;
        background-color: transparent !important; /* Remove background */
        border: 2px solid; /* Add border */
        border-radius: 4px;
        padding: 6px 8px;
        transition: all 0.3s ease;
    }
    
    /* View button - Green border matching the eye icon */
    .btn-ghost-primary {
        color: #007bff !important;
        border-color: #007bff !important;
    }
    .
    .btn-ghost-success {
        color: #28a745 !important;
        border-color: #28a745 !important;
    }
    
    .btn-ghost-success:hover {
        background-color: #28a745 !important;
        color: white !important;
    }
    
    /* Edit button - Blue border matching the edit icon */
    .btn-ghost-info {
        color: #17a2b8 !important;
        border-color: #17a2b8 !important;
    }
    
    .btn-ghost-info:hover {
        background-color: #17a2b8 !important;
        color: white !important;
    }
    
    /* Picture button - Yellow border matching the picture icon */
    .btn-ghost-warning {
        color: #ffc107 !important;
        border-color: #ffc107 !important;
    }
    
    .btn-ghost-warning:hover {
        background-color: #ffc107 !important;
        color: white !important;
    }
    
    /* Scrollable tasks section */
    .tasks-scrollable {
        max-height: 600px;
        overflow-y: auto;
        padding-right: 10px;
    }
    
    /* Custom scrollbar styling */
    .tasks-scrollable::-webkit-scrollbar {
        width: 8px;
    }
    
    .tasks-scrollable::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .tasks-scrollable::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .tasks-scrollable::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush

<div class="row">
    <!-- Delivery Order Details -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">📋 Project Details</h3>
                <div class="card-tools">
                    @if($deliveryOrder->getRemainingQuantity() > 0)
                        <a href="{{ route('tasks.create', ['delivery_order_id' => Crypt::encrypt($deliveryOrder->id)]) }}" 
                            class='btn btn-ghost-primary btn-sm'
                            title="Create New Task for this DO">
                            <i class="fa fa-plus-square"></i> Create Delivery Order
                        </a>
                    @else
                        <button class='btn btn-success btn-sm' disabled 
                                title="Project is fully completed">
                            <i class="fa fa-check"></i> Completed
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Delivery Progress -->
                <div class="progress-container">
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Project Progress:</strong></span>
                        <span>{{ $deliveryOrder->progress_total }} / {{ $deliveryOrder->total_order }} 
                              ({{ intval(($deliveryOrder->progress_total / $deliveryOrder->total_order) * 100) }}%)</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar 
                            @if($deliveryOrder->progress_total >= $deliveryOrder->total_order) bg-success
                            @elseif($deliveryOrder->progress_total > 0) bg-warning
                            @else bg-info @endif" 
                             role="progressbar" 
                             style="width: {{ ($deliveryOrder->progress_total / $deliveryOrder->total_order) * 100 }}%;" 
                             aria-valuenow="{{ $deliveryOrder->progress_total }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $deliveryOrder->total_order }}">
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <small class="text-muted">
                            @if($deliveryOrder->getRemainingQuantity() > 0)
                                <strong>{{ $deliveryOrder->getRemainingQuantity() }}</strong> remaining to deliver
                            @else
                                <strong>Fully Delivered</strong>
                            @endif
                        </small>
                    </div>
                </div>

                <!-- DO Details -->
                <div class="row">
                    <div class="col-md-6">
                        <!-- Date Field -->
                        <div class="form-group">
                            {!! Form::label('date', 'Date:') !!}
                            <p>{{ $deliveryOrder->date->format('d-m-Y') }}</p>
                        </div>

                        <!-- Created at Date Field -->
                        <div class="form-group">
                            {!! Form::label('date', 'Created At:') !!}
                            <p>{{ $deliveryOrder->created_at->format('d-m-Y g:i A') }}</p>
                        </div>

                        <!-- Dono Field -->
                        <div class="form-group">
                            {!! Form::label('dono', 'DO Number:') !!}
                            <p>{{ $deliveryOrder->dono }}</p>
                        </div>

                        <!-- Customer Id Field -->
                        <div class="form-group">
                            {!! Form::label('customer_id', 'Customer:') !!}
                            <p>{{ $deliveryOrder->customer->company }}</p>
                        </div>

                        <!-- Company Id Field -->
                        <div class="form-group">
                            {!! Form::label('company_id', 'Branch:') !!}
                            <p>{{ $deliveryOrder->company->name }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        
                        <!-- Product Id Field -->
                        <div class="form-group">
                            {!! Form::label('product_id', 'Product:') !!}
                            <p>{{ $deliveryOrder->product->code }}</p>
                        </div>

                        <!-- Total Order Field -->
                        <div class="form-group">
                            {!! Form::label('total_order', 'Total Order:') !!}
                            <p>{{ $deliveryOrder->total_order }}</p>
                        </div>

                        <!-- Progress Total Field -->
                    <div class="form-group">
                        {!! Form::label('progress_total', 'Progress Total:') !!}
                            <p>{{ $deliveryOrder->progress_total }}</p>
                        </div>

                        <!-- Status Field -->
                        <div class="form-group">
                            {!! Form::label('status', 'Status:') !!}
                            <p>
                                @php
                                    $statusOptions = \App\Models\DeliveryOrder::getStatusOptions();
                                    $statusText = $statusOptions[$deliveryOrder->status] ?? 'Unknown';
                                @endphp
                                <span class="badge 
                                    @switch($deliveryOrder->status)
                                        @case(0) badge-secondary @break
                                        @case(1) badge-info @break
                                        @case(2) badge-primary @break
                                        @case(3) badge-warning @break
                                        @case(4) badge-light @break
                                        @case(5) badge-success @break
                                        @case(6) badge-success @break
                                        @default badge-secondary
                                    @endswitch
                                ">{{ $statusText }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="row">
                    <div class="col-12">
                        <!-- Place Name Field -->
                        <div class="form-group">
                            {!! Form::label('place_name', 'Destination:') !!}
                            <p>{{ $deliveryOrder->place_name }}</p>
                        </div>

                        <!-- Place Address Field -->
                        <div class="form-group">
                            {!! Form::label('place_address', 'Address:') !!}
                            <p>{{ $deliveryOrder->place_address }}</p>
                        </div>

                        <!-- Strength At Field -->
                        <div class="form-group">
                            {!! Form::label('strength_at', 'Strength At 28 days:') !!}
                            <p>{{ $deliveryOrder->strength_at ?? 'N/A' }}</p>
                        </div>

                        <!-- Slump Field -->
                        <div class="form-group">
                            {!! Form::label('slump', 'Specific Slump:') !!}
                            <p>{{ $deliveryOrder->slump ?? 'N/A' }}</p>
                        </div>

                        <!-- Remark Field -->
                        <div class="form-group">
                            {!! Form::label('remark', 'Remark:') !!}
                            <p>{{ $deliveryOrder->remark ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Section -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">🚚 Associated Tasks ({{ $deliveryOrder->tasks->count() }})</h3>
                @if($deliveryOrder->tasks->count() > 3)
                    <small class="text-muted">
                        <i class="fas fa-arrows-alt-v"></i> Scroll to see more
                    </small>
                @endif
            </div>
            <div class="card-body p-0">
                @if($deliveryOrder->tasks->count() > 0)
                    <div class="tasks-scrollable p-3">
                        @foreach($deliveryOrder->tasks as $task)
                            <div class="card task-card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="card-title">
                                                Delivery Order #{{ $task->id }}
                                                @if($task->driver)
                                                    - {{ $task->driver->name }}
                                                @endif
                                            </h6>
                                            <p class="card-text mb-1">
                                                <strong>This Load:</strong> {{ $task->this_load }}
                                            </p>
                                            <p class="card-text mb-1">
                                                <strong>Status:</strong> 
                                                <span class="badge task-status-badge
                                                    @switch($task->getStatusValue())
                                                        @case(0) badge-secondary @break
                                                        @case(1) badge-primary @break
                                                        @case(2) badge-success @break
                                                        @case(3) badge-warning @break
                                                        @default badge-secondary
                                                    @endswitch">
                                                    @switch($task->getStatusValue())
                                                        @case(0) New @break
                                                        @case(1) Delivering @break
                                                        @case(2) Completed @break
                                                        @case(3) Returned @break
                                                        @default Unknown
                                                    @endswitch
                                                </span>
                                            </p>
                                            @if($task->return_reason)
                                                <p class="card-text mb-1">
                                                    <strong>Return Reason:</strong> {{ $task->return_reason }}
                                                </p>
                                            @endif
                                            <p class="card-text mb-1">
                                                <small class="text-muted">
                                                    Created: {{ $task->created_at->format('d-m-Y H:i') }}
                                                </small>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-right task-actions">
                                            <a href="{{ route('tasks.show', [Crypt::encrypt($task->id)]) }}" 
                                               class='btn btn-ghost-success btn-sm' 
                                               title="View Task Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tasks.edit', [Crypt::encrypt($task->id)]) }}" 
                                               class='btn btn-ghost-info btn-sm' 
                                               title="Edit Task">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            @if($task->deliveryImage && ($task->deliveryImage->delivery_order_image_path || $task->deliveryImage->proof_of_delivery_image_path))
                                                <button class="btn btn-ghost-warning btn-sm view-task-images" 
                                                        data-task-id="{{ $task->id }}"
                                                        title="View Delivery Images">
                                                    <i class="fa fa-picture-o"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No delivery order created for this project yet.</p>
                        @if($deliveryOrder->getRemainingQuantity() > 0)
                            <a href="{{ route('tasks.create', ['delivery_order_id' => Crypt::encrypt($deliveryOrder->id)]) }}" 
                               class="btn btn-success">
                               <i class="fas fa-plus"></i> Create First Delivery Order
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="taskImagesModal" tabindex="-1" role="dialog" aria-labelledby="taskImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskImagesModalLabel">Delivery Order Images</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="taskImagesContent">
                    <!-- Images will be loaded here via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            HideLoad();

            // View task images
            $('.view-task-images').click(function() {
                const taskId = $(this).data('task-id');
                
                // Show loading
                $('#taskImagesContent').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading images...</p>
                    </div>
                `);
                
                $('#taskImagesModal').modal('show');
                
                // Load task images via AJAX
                $.ajax({
                    url: '{{ route("deliveryOrders.getTaskImages") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        task_id: taskId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#taskImagesContent').html(response.html);
                        } else {
                            $('#taskImagesContent').html(`
                                <div class="alert alert-danger">
                                    Error loading images: ${response.message}
                                </div>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        $('#taskImagesContent').html(`
                            <div class="alert alert-danger">
                                Error loading images. Please try again.
                            </div>
                        `);
                    }
                });
            });

            // Keyboard shortcut
            $(document).keyup(function(e) {
                if (e.key === "Escape") {
                    $('.card .card-header a')[0].click();
                }
            });
        });
    </script>
@endpush