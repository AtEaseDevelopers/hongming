<div class="row">
    @if($task->deliveryImage && $task->deliveryImage->delivery_order_image_path)
        <div class="col-md-6 mb-3">
            <h6>📝 Signed Delivery Order</h6>
            <img src="{{ asset($task->deliveryImage->delivery_order_image_path) }}" 
                 alt="Signed Delivery Order" 
                 class="img-fluid img-thumbnail"
                 style="max-height: 300px; cursor: pointer;"
                 onclick="window.open('{{ asset($task->deliveryImage->delivery_order_image_path) }}', '_blank')">
            <div class="mt-2">
                <a href="{{ asset($task->deliveryImage->delivery_order_image_path) }}" 
                   download="signed-do-task-{{ $task->id }}.jpg" 
                   class="btn btn-sm btn-success">
                   <i class="fas fa-download"></i> Download
                </a>
                <small class="text-muted ml-2">Click image to view full size</small>
            </div>
        </div>
    @endif
    
    @if($task->deliveryImage && $task->deliveryImage->proof_of_delivery_image_path)
        <div class="col-md-6 mb-3">
            <h6>📸 Proof of Delivery</h6>
            <img src="{{ asset($task->deliveryImage->proof_of_delivery_image_path) }}" 
                 alt="Proof of Delivery" 
                 class="img-fluid img-thumbnail"
                 style="max-height: 300px; cursor: pointer;"
                 onclick="window.open('{{ asset($task->deliveryImage->proof_of_delivery_image_path) }}', '_blank')">
            <div class="mt-2">
                <a href="{{ asset($task->deliveryImage->proof_of_delivery_image_path) }}" 
                   download="proof-of-delivery-task-{{ $task->id }}.jpg" 
                   class="btn btn-sm btn-success">
                   <i class="fas fa-download"></i> Download
                </a>
                <small class="text-muted ml-2">Click image to view full size</small>
            </div>
        </div>
    @endif
    
    @if(!$task->deliveryImage || (!$task->deliveryImage->delivery_order_image_path && !$task->deliveryImage->proof_of_delivery_image_path))
        <div class="col-12 text-center py-4">
            <i class="fas fa-images fa-3x text-muted mb-3"></i>
            <p class="text-muted">No images available for this Delivery Order.</p>
            <small class="text-info">
                Images will appear here when the task is marked as completed and images are uploaded.
            </small>
        </div>
    @endif
</div>

@if($task->deliveryImage && ($task->deliveryImage->delivery_order_image_path || $task->deliveryImage->proof_of_delivery_image_path))
<div class="row mt-3">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6>Delivery Order Information</h6>
                <p class="mb-1"><strong>Delivery Order ID:</strong> #{{ $task->id }}</p>
                <p class="mb-1"><strong>Driver:</strong> {{ $task->driver->name ?? 'N/A' }}</p>
                <p class="mb-1"><strong>This Load:</strong> {{ number_format($task->this_load, 2) }}</p>
                <p class="mb-0"><strong>Status:</strong> 
                    <span class="badge 
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
            </div>
        </div>
    </div>
</div>
@endif