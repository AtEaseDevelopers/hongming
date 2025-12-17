<!-- Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date', __('tasks.date')) !!}<span class="asterisk"> *</span>
    {!! Form::date('date', isset($task) ? $task->getRawOriginal('date') : \Carbon\Carbon::now()->format('Y-m-d'), [
        'class' => 'form-control', 
        'id' => 'date', 
        'autofocus',
    ]) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('company', 'Branch:') !!}<span class="asterisk"> *</span>
    {!! Form::select('company_id', $company, null, ['class' => 'form-control', 'placeholder' => 'Pick a Branch...', 'id' => 'company_id']) !!}
</div>

<!-- Task Number Field -->
<div class="form-group col-sm-6" id="task_number_field_group" style="display: none;">
    {!! Form::label('task_number', 'Task Number:') !!}<span class="asterisk"> *</span>
    {!! Form::text('task_number', null, [
        'class' => 'form-control', 
        'id' => 'task_number',
        'placeholder' => 'Auto-generated when company is selected'
    ]) !!}
</div>

<!-- Delivery Order Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('delivery_order_id', __('Project Number')) !!}<span class="asterisk"> *</span>
    {!! Form::select('delivery_order_id', $delivery_order, $delivery_order_id ?? null, [
        'class' => 'form-control select2-delivery', 
        'placeholder' => 'Pick a Delivery Order...', 
        'id' => 'delivery_order_id'
    ]) !!}
</div>

<!-- Delivery Order Details (Read-only) -->
<div id="delivery-order-details" class="col-sm-12 mt-3" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">📦Project Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Customer Field -->
                <div class="form-group col-sm-6">
                    {!! Form::label('customer_display', 'Customer:') !!}
                    {!! Form::text('customer_display', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'customer_display']) !!}
                </div>
                <!-- Product Field -->
                <div class="form-group col-sm-6">
                    {!! Form::label('product_display', 'Product:') !!}
                    {!! Form::text('product_display', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'product_display']) !!}
                </div>
                <!-- Destination Field -->
                <div class="form-group col-sm-6">
                    {!! Form::label('destination_display', 'Destination:') !!}
                    {!! Form::text('destination_display', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'destination_display']) !!}
                </div>
                <!-- Progress Load Field -->
                <div class="form-group col-sm-6">
                    {!! Form::label('progress_load_display', 'Progress Load:') !!}
                    {!! Form::text('progress_load_display', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'progress_load_display']) !!}
                </div>

                <!-- Total Order Field -->
                <div class="form-group col-sm-6">
                    {!! Form::label('total_order_display', 'Total Order:') !!}
                    {!! Form::text('total_order_display', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'total_order_display']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- This Load Field -->
<div class="form-group col-sm-6">
    {!! Form::label('this_load', 'This Load:') !!}
    {!! Form::number('this_load', null, ['class' => 'form-control','maxlength' => 255]) !!}
</div>

<!-- Lorry Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('lorry_id', __('Lorry')) !!}<span class="asterisk"> *</span>
    {!! Form::select('lorry_id', $lorryItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Lorry...']) !!}
</div>

<!-- Countdown Minutes Field -->
<div class="form-group col-sm-6">
    {!! Form::label('countdown', ' Countdown (minutes):') !!}
    {!! Form::number('countdown', null, [
        'class' => 'form-control',
        'min' => 1,
        'max' => 1440,
        'placeholder' => 'Enter default countdown (1-1440)'
    ]) !!}
    <small class="form-text text-muted">
        Default countdown time in minutes for tasks using this product. Admins can override this when creating tasks.
    </small>
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('tasks.status'))!!}:
    {{ Form::select('status', [0 => 'New', 1 => 'Delivering', 2 => 'Completed', 3 => 'Returned'], isset($task) ? $task->getStatusValue() : null, ['class' => 'form-control', 'id' => 'status']) }}
</div>

<!-- Return Reason Field (Initially Hidden) -->
<div class="form-group col-sm-6" id="return_reason_field" style="display: none;">
    {!! Form::label('return_reason', 'Return Reason:') !!}<span class="asterisk"> *</span>
    {!! Form::text('return_reason', null, ['class' => 'form-control','maxlength' => 255, 'id' => 'return_reason']) !!}
</div>

<!-- Return Remarks Field (Initially Hidden) -->
<div class="form-group col-sm-6" id="return_remarks_field" style="display: none;">
    {!! Form::label('return_remarks', 'Return Remarks:') !!}
    {!! Form::text('return_remarks', null, ['class' => 'form-control','maxlength' => 255, 'id' => 'return_remarks']) !!}
    <small class="form-text text-muted">Additional notes about the return (optional)</small>
</div>

<!-- Image Upload Fields (Initially Hidden) -->
<div id="image_upload_fields" style="display: none;">
    <div class="col-sm-12">
        <div class="card image-card">
            <div class="card-header">
                <h6 class="card-title mb-0">📷 Upload Delivery Proof Images</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Signed DO Image Field -->
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            {!! Form::label('signed_do_image', 'Signed Delivery Order Image:') !!}
                            {!! Form::file('signed_do_image', [
                                'class' => 'form-control-file',
                                'accept' => 'image/*',
                                'id' => 'signed_do_image'
                            ]) !!}
                            <small class="form-text text-muted">Upload signed delivery order document</small>
                            
                            @if(isset($task) && $task->deliveryImage && $task->deliveryImage->delivery_order_image_path)
                                <div class="mt-3">
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
                                    <small class="text-muted d-block text-center mt-1">Current uploaded image</small>
                                </div>
                            @else
                                <div class="text-center text-muted mt-3 py-3 border rounded">
                                    <i class="fa fa-camera fa-2x mb-2"></i>
                                    <p class="mb-0">No signed DO image uploaded yet</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Proof of Delivery Image Field -->
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            {!! Form::label('proof_of_delivery_image', 'Proof of Delivery Image:') !!}
                            {!! Form::file('proof_of_delivery_image', [
                                'class' => 'form-control-file',
                                'accept' => 'image/*',
                                'id' => 'proof_of_delivery_image'
                            ]) !!}
                            <small class="form-text text-muted">Upload location/proof of delivery photo</small>
                            
                            @if(isset($task) && $task->deliveryImage && $task->deliveryImage->proof_of_delivery_image_path)
                                <div class="mt-3">
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
                                    <small class="text-muted d-block text-center mt-1">Current uploaded image</small>
                                </div>
                            @else
                                <div class="text-center text-muted mt-3 py-3 border rounded">
                                    <i class="fa fa-camera fa-2x mb-2"></i>
                                    <p class="mb-0">No proof of delivery image uploaded yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Upload Instructions -->
                <div class="alert alert-info mt-3">
                    <h6 class="alert-heading"><i class="fa fa-info-circle"></i> Upload Instructions</h6>
                    <ul class="mb-0 pl-3">
                        <li>Both images are required when marking task as completed</li>
                        <li>Accepted formats: JPG, PNG, GIF</li>
                        <li>Maximum file size: 5MB per image</li>
                        <li>Images will replace existing ones if re-uploaded</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('tasks.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">{{ __('tasks.cancel') }}</a>
</div>

@push('styles')
<style>
    #delivery-order-details .card {
        border-left: 4px solid #007bff;
        background-color: #f8f9fa;
    }
    
    #delivery-order-details .form-control[readonly] {
        background-color: #fff;
        border: 1px solid #e9ecef;
        color: #495057;
    }
    
    .asterisk {
        color: red;
    }
    
    #return_reason_field .asterisk {
        display: inline;
    }
    
    .preview-image {
        margin-right: 5px;
    }
    
    .img-thumbnail {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    .image-card {
        border-left: 4px solid #28a745;
    }

    .image-card .img-thumbnail {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        transition: transform 0.2s ease;
    }

    .image-card .img-thumbnail:hover {
        transform: scale(1.02);
    }

</style>
@endpush

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        
        $(document).ready(function () {
            HideLoad();

            // ========== TASK NUMBER AUTO-GENERATION ==========
            const companyTaskPrefixes = {};
            @foreach($companies as $company)
                companyTaskPrefixes[{{ $company->id }}] = '{{ $company->task_prefix }}';
            @endforeach

            // Function to get next task number via AJAX
            function getNextTaskNumber(companyId) {
                
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: '{{ route("tasks.getNextTaskNumber") }}',
                        method: 'POST',
                        data: {
                            company_id: companyId,
                             _token: '{{ csrf_token() }}'
                        },
                        
                        success: function(response) {
                            if (response.success) {
                                resolve(response.task_number);
                            } else {
                                reject(response.message);
                            }
                        },
                        error: function(xhr) {
                            console.error('Error getting task number:', xhr.responseText);
                            reject('Error generating task number');
                        }
                    });
                });
            }

            // Auto-generate task number when company is selected
            $(document).on('change', 'select[name="company_id"]', function() {
                const companyId = $(this).val();
                const taskNumberInput = $('#task_number');
                const taskNumberFieldGroup = $('#task_number_field_group');
                
                if (companyId && companyTaskPrefixes[companyId]) {
                    // Show the task number field
                    taskNumberFieldGroup.show();
                    
                    // Show loading state
                    taskNumberInput.val('Generating...');
                    
                    console.log('🔄 Generating task number for company:', companyId);
                    
                    // Get sequential task number from server
                    getNextTaskNumber(companyId)
                        .then(taskNumber => {
                            taskNumberInput.val(taskNumber);
                            console.log('✅ Generated task number:', taskNumber);
                        })
                        .catch(error => {
                            // If AJAX fails, use fallback
                            const currentPrefix = companyTaskPrefixes[companyId];
                            taskNumberInput.val(currentPrefix + '-');
                            console.error('AJAX failed, using fallback:', error);
                        });
                } else {
                    // Hide task number field if no company selected
                    taskNumberFieldGroup.hide();
                    taskNumberInput.val('');
                }
            });

            // Check if we're in edit mode and company is already selected
            function checkEditModeTaskNumber() {
                const companyId = $('select[name="company_id"]').val();
                const taskNumberInput = $('#task_number');
                const taskNumberFieldGroup = $('#task_number_field_group');
                
                if (companyId) {
                    taskNumberFieldGroup.show();
                    
                    // If task number is empty, generate one
                    if (!taskNumberInput.val() || taskNumberInput.val() === '') {
                        taskNumberInput.val('Generating...');
                        
                        getNextTaskNumber(companyId)
                            .then(taskNumber => {
                                taskNumberInput.val(taskNumber);
                            })
                            .catch(error => {
                                const currentPrefix = companyTaskPrefixes[companyId];
                                const fallbackNumber = currentPrefix + '-001';
                                taskNumberInput.val(fallbackNumber);
                            });
                    }
                }
            }
            
            // Run check on page load for edit mode
            checkEditModeTaskNumber();

            // ========== EXISTING DELIVERY ORDER FUNCTIONALITY ==========
            
            // Function to fetch delivery order details
            function fetchDeliveryOrderDetails(deliveryOrderId) {
                if (deliveryOrderId) {
                    // Show loading
                    ShowLoad();
                    
                    // Fetch delivery order details via AJAX
                    $.ajax({
                        url: '{{ route("tasks.getDeliveryOrderDetails") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            delivery_order_id: deliveryOrderId
                        },
                        success: function(response) {
                            console.log('Response:', response);
                            if (response.success) {
                                // Populate the read-only fields
                                $('#customer_display').val(response.data.customer || 'N/A');
                                $('#product_display').val(response.data.product || 'N/A');
                                $('#progress_load_display').val(response.data.progress_total || '0');
                                $('#total_order_display').val(response.data.total_order || '0');
                                $('#destination_display').val(response.data.destination || 'N/A');
                                
                                // Auto-fill countdown with product's default countdown
                                if (response.data.product_countdown && (!$('#countdown').val() || $('#countdown').val() === '0')) {
                                    $('#countdown').val(response.data.product_countdown);
                                    console.log('Auto-filled countdown:', response.data.product_countdown);
                                }
                                
                                // FIXED: Always update "This Load" when DO changes, regardless of current value
                                // Only skip if we're in edit mode and the task already has a this_load value
                                const isEditMode = $('input[name="_method"]').val() === 'PUT' || $('input[name="_method"]').val() === 'PATCH';
                                const hasExistingThisLoad = {{ isset($task) && $task->this_load ? 'true' : 'false' }};
                                
                                if (!isEditMode || !hasExistingThisLoad) {
                                    $('#this_load').val(response.data.this_load || '');
                                    console.log('Updated This Load to:', response.data.this_load);
                                }
                                
                                // Show the delivery order details section
                                $('#delivery-order-details').slideDown();
                            } else {
                                alert('Error loading delivery order details');
                                $('#delivery-order-details').slideUp();
                            }
                            HideLoad();
                        },
                        error: function(xhr) {
                            console.log('Error:', xhr.responseText);
                            alert('Error loading delivery order details');
                            $('#delivery-order-details').slideUp();
                            HideLoad();
                        }
                    });
                } else {
                    // Hide the details section if no delivery order selected
                    $('#delivery-order-details').slideUp();
                    
                    // Clear the read-only fields
                    $('#customer_display').val('');
                    $('#product_display').val('');
                    $('#progress_load_display').val('');
                    $('#total_order_display').val('');
                    $('#destination_display').val('');
                    
                    // FIXED: Also clear "This Load" when no DO is selected
                    const isEditMode = $('input[name="_method"]').val() === 'PUT' || $('input[name="_method"]').val() === 'PATCH';
                    const hasExistingThisLoad = {{ isset($task) && $task->this_load ? 'true' : 'false' }};
                    
                    if (!isEditMode || !hasExistingThisLoad) {
                        $('#this_load').val('');
                    }
                }
            }
            
            // Initialize Select2 for delivery order
            if ($('.select2-delivery').length) {
                $('.select2-delivery').select2({
                    placeholder: 'Pick a Delivery Order...',
                    allowClear: true
                });
            }
            
            // Handle status change to show/hide return reason, return remarks and image upload fields
            $('#status').change(function() {
                toggleReturnReasonField();
                toggleReturnRemarksField();
                toggleImageUploadFields();
            });
            
            // Function to toggle return reason field visibility
            function toggleReturnReasonField() {
                const status = $('#status').val();
                const returnReasonField = $('#return_reason_field');
                
                if (status == '3') { // Returned status
                    returnReasonField.slideDown();
                    $('#return_reason').prop('required', true);
                } else {
                    returnReasonField.slideUp();
                    $('#return_reason').prop('required', false);
                }
            }
            
            // Function to toggle return remarks field visibility
            function toggleReturnRemarksField() {
                const status = $('#status').val();
                const returnRemarksField = $('#return_remarks_field');
                
                if (status == '3') { // Returned status
                    returnRemarksField.slideDown();
                } else {
                    returnRemarksField.slideUp();
                }
            }
            
            // Function to toggle image upload fields visibility
            function toggleImageUploadFields() {
                const status = $('#status').val();
                const imageUploadFields = $('#image_upload_fields');
                
                if (status == '2') { // Completed status
                    imageUploadFields.slideDown();
                } else {
                    imageUploadFields.slideUp();
                    $('#signed_do_image').prop('required', false);
                    $('#proof_of_delivery_image').prop('required', false);
                }
            }
            
            // Handle delivery order selection change
            $('#delivery_order_id').change(function() {
                const deliveryOrderId = $(this).val();
                fetchDeliveryOrderDetails(deliveryOrderId);
            });
                
            function previewImage(imageUrl, imageName) {
                $('#previewImage').attr('src', imageUrl);
                $('#imageName').text(imageName);
                $('#downloadImageBtn').attr('href', imageUrl);
                $('#imagePreviewModal').modal('show');
            }

            // Image preview functionality
            $(document).on('click', '.preview-image', function() {
                const imageUrl = $(this).data('image-url');
                const imageName = $(this).data('image-name');
                previewImage(imageUrl, imageName);
            });
            
            // Initialize delivery order details on page load
            function initDeliveryOrderDetails() {
                const deliveryOrderId = $('#delivery_order_id').val();                
                if (deliveryOrderId) {
                    // Wait for Select2 to be fully initialized
                    setTimeout(function() {
                        fetchDeliveryOrderDetails(deliveryOrderId);
                    }, 300);
                }
            }
            
            // Initialize when page loads
            setTimeout(function() {
                initDeliveryOrderDetails();
                toggleReturnReasonField();
                toggleReturnRemarksField();
                toggleImageUploadFields();
            }, 500);
            
            // Additional safety check
            setTimeout(function() {
                const deliveryOrderId = $('#delivery_order_id').val();
                if (deliveryOrderId && $('#delivery-order-details').is(':hidden')) {
                    fetchDeliveryOrderDetails(deliveryOrderId);
                }
            }, 1000);
        });
    </script>
@endpush