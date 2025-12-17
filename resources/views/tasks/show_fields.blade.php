@push('styles')
<style>
    .form-group label {
        font-weight: bold;
        text-decoration: underline;
    }
    
    .task-card {
        border-left: 4px solid #007bff;
    }
    
    .status-badge {
        font-size: 0.9em;
        padding: 0.5em 0.8em;
    }
    
    .image-card {
        border-left: 4px solid #28a745;
    }
    
    .return-card {
        border-left: 4px solid #dc3545;
    }
    
    .info-card {
        border-left: 4px solid #17a2b8;
    }
</style>
@endpush

<div class="row">
    <!-- Task Basic Information -->
    <div class="col-md-6">
        <div class="card task-card">
            <div class="card-header">
                <h3 class="card-title">📋 Delivery Order Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Task ID Field -->
                        <div class="form-group">
                            {!! Form::label('task_id', 'Delivery Order Number:') !!}
                            <p class="form-control-static">
                                @php
                                    $maxId = \App\Models\Task::max('id');
                                    $paddingLength = max(4, strlen((string)$maxId));
                                    $formattedId = 'T' . str_pad($task->id, $paddingLength, '0', STR_PAD_LEFT);
                                @endphp
                                {{ $formattedId }}
                            </p>
                        </div>

                        <!-- Date Field -->
                        <div class="form-group">
                            {!! Form::label('date', 'Date:') !!}
                            <p class="form-control-static">{{ $task->date }}</p>
                        </div>
                        <!-- Create at Date Field -->
                        <div class="form-group">
                            {!! Form::label('date', 'Created At:') !!}
                            <p class="form-control-static">{{$task->created_at->format('d-m-Y H:i') }}</p>
                        </div>
                        <!-- Lorry Field -->
                        <div class="form-group">
                            {!! Form::label('lorry_id', 'Lorry:') !!}
                            <p class="form-control-static">{{ $task->lorry->lorryno ?? 'N/A' }}</p>
                        </div>

                        <!-- Driver Field -->
                        <div class="form-group">
                            {!! Form::label('driver_id', 'Driver:') !!}
                            <p class="form-control-static">{{ $task->driver->name ?? 'N/A' }}</p>
                        </div>

                        <!-- This Load Field -->
                        <div class="form-group">
                            {!! Form::label('this_load', 'This Load:') !!}
                            <p class="form-control-static">{{ $task->this_load }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Status Field -->
                        <div class="form-group">
                            {!! Form::label('status', 'Status:') !!}
                            <p class="form-control-static">
                                @php
                                    $statusOptions = [0 => 'New', 1 => 'In-Progress', 2 => 'Completed', 3 => 'Returned'];
                                    $status = $task->getStatusValue(); // Use getStatusValue() to get integer
                                    $statusText = $statusOptions[$status] ?? 'Unknown';
                                    
                                    $statusClass = [
                                        0 => 'badge badge-secondary status-badge',
                                        1 => 'badge badge-warning status-badge',
                                        2 => 'badge badge-success status-badge', 
                                        3 => 'badge badge-danger status-badge',
                                    ];
                                @endphp
                                <span class="{{ $statusClass[$status] ?? 'badge badge-secondary status-badge' }}">
                                    @if($status === 1)
                                        <i class="fa fa-truck-moving"></i> 
                                    @endif
                                    {{ $statusText }}
                                </span>
                            </p>
                        </div>

                        <!-- Countdown Field -->
                        <div class="form-group">
                            {!! Form::label('countdown', 'Countdown:') !!}
                            <p class="form-control-static">{{ $task->getCountdownFormatted() }}</p>
                        </div>

                        <!-- Start Time Field -->
                        <div class="form-group">
                            {!! Form::label('start_time', 'Start Time:') !!}
                            <p class="form-control-static">
                                @if($task->start_time)
                                    {{ \Carbon\Carbon::parse($task->start_time)->format('d-m-Y H:i') }}
                                @else
                                    <span class="text-muted">Not started</span>
                                @endif
                            </p>
                        </div>

                        <!-- End Time Field -->
                        <div class="form-group">
                            {!! Form::label('end_time', 'End Time:') !!}
                            <p class="form-control-static">
                                @if($task->end_time)
                                    {{ \Carbon\Carbon::parse($task->end_time)->format('d-m-Y H:i') }}
                                @else
                                    <span class="text-muted">Not completed</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Time Taken Field -->
                <div class="form-group">
                    {!! Form::label('time_taken', 'Time Taken:') !!}
                    <p class="form-control-static">
                        @if($task->time_taken)
                            {{ $task->getTimeTakenFormatted() }}
                            @if($task->is_late)
                                <span class="badge badge-danger ml-2">Late</span>
                            @endif
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Order Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📦 Project Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- DO Number Field -->
                        <div class="form-group">
                            {!! Form::label('delivery_order_id', 'DO Number:') !!}
                            <p class="form-control-static">{{ $task->deliveryOrder->dono ?? 'N/A' }}</p>
                        </div>

                        <!-- Customer Field -->
                        <div class="form-group">
                            {!! Form::label('customer_display', 'Customer:') !!}
                            <p class="form-control-static">{{ $task->deliveryOrder->customer->company ?? 'N/A' }}</p>
                        </div>

                        <!-- Product Field -->
                        <div class="form-group">
                            {!! Form::label('product_display', 'Product:') !!}
                            <p class="form-control-static">{{ $task->deliveryOrder->product->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Destination Field -->
                        <div class="form-group">
                            {!! Form::label('destination_display', 'Destination:') !!}
                            <p class="form-control-static">{{ $task->deliveryOrder->place_name ?? 'N/A' }}</p>
                        </div>

                        <!-- Address Field -->
                        <div class="form-group">
                            {!! Form::label('address_display', 'Address:') !!}
                            <p class="form-control-static">{{ $task->deliveryOrder->place_address ?? 'N/A' }}</p>
                        </div>

                        <!-- Total Order Field -->
                        <div class="form-group">
                            {!! Form::label('total_order_display', 'Total Order:') !!}
                            <p class="form-control-static">{{ $task->deliveryOrder->total_order}}</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Information -->
                <div class="progress-container bg-light p-3 rounded mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Project Progress:</strong></span>
                        <span>
                            {{ $task->deliveryOrder->progress_total ?? 0 }} / {{ $task->deliveryOrder->total_order ?? 0 }} 
                            ({{ $task->deliveryOrder->total_order > 0 ? number_format(($task->deliveryOrder->progress_total / $task->deliveryOrder->total_order) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="progress" style="height: 15px;">
                        @php
                            $progressPercent = $task->deliveryOrder->total_order > 0 ? ($task->deliveryOrder->progress_total / $task->deliveryOrder->total_order) * 100 : 0;
                        @endphp
                        <div class="progress-bar 
                            @if($progressPercent >= 100) bg-success
                            @elseif($progressPercent > 0) bg-warning
                            @else bg-info @endif" 
                             role="progressbar" 
                             style="width: {{ $progressPercent }}%;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Return Information (Only show if status is Returned) -->
@if($task->getStatusValue() == 3)
<div class="row mt-3">
    <div class="col-12">
        <div class="card return-card">
            <div class="card-header">
                <h3 class="card-title">🔄 Return Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($task->return_reason)
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('return_reason', 'Return Reason:') !!}
                            <p class="form-control-static">{{ $task->return_reason }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($task->return_remarks)
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('return_remarks', 'Return Remarks:') !!}
                            <p class="form-control-static">{{ $task->return_remarks }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                
                @if(!$task->return_reason && !$task->return_remarks)
                <div class="text-center text-muted py-3">
                    <i class="fa fa-info-circle fa-2x mb-2"></i>
                    <p>No return details provided</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Delivery Images (Only show if status is Completed and images exist) -->
@if($task->getStatusValue() == 2 && $task->deliveryImage && ($task->deliveryImage->delivery_order_image_path || $task->deliveryImage->proof_of_delivery_image_path))
<div class="row mt-3">
    <div class="col-12">
        <div class="card image-card">
            <div class="card-header">
                <h3 class="card-title">📷 Delivery Proof Images</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Signed DO Image Field -->
                    @if($task->deliveryImage->delivery_order_image_path)
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            {!! Form::label('signed_do_image_preview', 'Signed Delivery Order:') !!}
                            <div class="mt-2">
                                <div class="text-center mb-2">
                                    <img src="{{ asset($task->deliveryImage->delivery_order_image_path) }}" 
                                         alt="Signed DO" 
                                         class="img-thumbnail" 
                                         style="max-height: 200px; cursor: pointer;"
                                         onclick="previewImage('{{ asset($task->deliveryImage->delivery_order_image_path) }}', 'Signed Delivery Order')">
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-sm btn-info preview-image" 
                                            data-image-url="{{ asset($task->deliveryImage->delivery_order_image_path) }}"
                                            data-image-name="Signed Delivery Order">
                                        <i class="fa fa-eye"></i> Preview
                                    </button>
                                    <a href="{{ route('tasks.downloadImage', ['type' => 'signed_do', 'taskId' => Crypt::encrypt($task->id)]) }}" 
                                       class="btn btn-sm btn-success">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Proof of Delivery Image Field -->
                    @if($task->deliveryImage->proof_of_delivery_image_path)
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            {!! Form::label('proof_of_delivery_image_preview', 'Proof of Delivery:') !!}
                            <div class="mt-2">
                                <div class="text-center mb-2">
                                    <img src="{{ asset($task->deliveryImage->proof_of_delivery_image_path) }}" 
                                         alt="Proof of Delivery" 
                                         class="img-thumbnail" 
                                         style="max-height: 200px; cursor: pointer;"
                                         onclick="previewImage('{{ asset($task->deliveryImage->proof_of_delivery_image_path) }}', 'Proof of Delivery')">
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-sm btn-info preview-image" 
                                            data-image-url="{{ asset($task->deliveryImage->proof_of_delivery_image_path) }}"
                                            data-image-name="Proof of Delivery">
                                        <i class="fa fa-eye"></i> Preview
                                    </button>
                                    <a href="{{ route('tasks.downloadImage', ['type' => 'proof_of_delivery', 'taskId' => Crypt::encrypt($task->id)]) }}" 
                                       class="btn btn-sm btn-success">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@elseif($task->getStatusValue() == 2)
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center text-muted py-4">
                <i class="fa fa-images fa-3x mb-3"></i>
                <h5>No Delivery Images</h5>
                <p>No proof images were uploaded for this completed task.</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Image Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Preview" class="img-fluid" style="max-height: 70vh;">
                <p class="mt-2" id="imageName"></p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-success" id="downloadImageBtn" download>
                    <i class="fa fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            HideLoad();
            
            // Image preview functionality
            $(document).on('click', '.preview-image', function() {
                const imageUrl = $(this).data('image-url');
                const imageName = $(this).data('image-name');
                previewImage(imageUrl, imageName);
            });
            
            // Keyboard shortcut
            $(document).keyup(function(e) {
                if (e.key === "Escape") {
                    $('a.btn-secondary')[0].click();
                }
            });
        });
        
        function previewImage(imageUrl, imageName) {
            $('#previewImage').attr('src', imageUrl);
            $('#imageName').text(imageName);
            $('#downloadImageBtn').attr('href', imageUrl);
            $('#imagePreviewModal').modal('show');
        }
    </script>
@endpush