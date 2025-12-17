<!-- Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date', 'Date:') !!}<span class="asterisk"> *</span>
    {!! Form::date('date', isset($machineRental) ? $machineRental->getRawOriginal('date') : \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control','id'=>'date','autofocus']) !!}
</div>

<!-- Company Id Field (Branch) -->
<div class="form-group col-sm-6" style = "margin-top: 20px;">
    {!! Form::label('company', 'Branch:') !!}<span class="asterisk"> *</span>
    {!! Form::select('company_id', $company, isset($machineRental) ? $machineRental->company_id : null, ['class' => 'form-control', 'placeholder' => 'Pick a Branch...']) !!}
</div>

<!-- Dono Field -->
<div class="form-group col-sm-6" style = "margin-top: 20px;">
    {!! Form::label('delivery_order_number', 'DO Number:') !!}<span class="asterisk"> *</span>
    {!! Form::text('delivery_order_number', isset($machineRental) ? $machineRental->delivery_order_number : null, ['class' => 'form-control','maxlength' => 255]) !!}
</div>

<!-- Customer Id Field -->
<div class="form-group col-sm-6" style = "margin-top: 20px;">
    {!! Form::label('customer_id', 'Customer:') !!}&nbsp;<a href="#" id="info_customer_id" class="pe-auto"><i class="nav-icon icon-info"></i></a>&nbsp;<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customers, isset($machineRental) ? $machineRental->customer_id : null, ['class' => 'form-control selectpicker', 'placeholder' => 'Pick a Customer...','data-live-search'=>'true']) !!}
</div>

<!-- Products Section -->
<div class="col-sm-12" style="margin-top: 20px;">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Rental Products</h5>
        </div>
        <div class="card-body">
            <div id="products-container">
                <!-- Product items will be populated here dynamically -->
                @if(isset($machineRental) && $machineRental->items->count() > 0)
                    @foreach($machineRental->items as $index => $item)
                    <div class="product-item row mb-3 align-items-end">
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('product_id[]', 'Product:') !!}<span class="asterisk"> *</span>
                                {!! Form::select('product_id[]', $productItems, $item->product_id, ['class' => 'form-control product-select', 'placeholder' => 'Pick a Product...']) !!}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {!! Form::label('uom[]', 'UOM:') !!}<span class="asterisk"> *</span>
                                <select name="uom[]" class="form-control uom-select" placeholder="Select UOM">
                                    <option value="">Select UOM</option>
                                    @if($item->product && $item->product->uoms)
                                        @foreach($item->product->uoms as $uom)
                                            <option value="{{ $uom['name'] }}" data-price="{{ $uom['price'] }}" {{ $item->uom == $uom['name'] ? 'selected' : '' }}>
                                                {{ $uom['name'] }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {!! Form::label('quantity[]', 'Quantity:') !!}<span class="asterisk"> *</span>
                                {!! Form::number('quantity[]', $item->quantity, ['class' => 'form-control quantity', 'min' => '1', 'step' => '1', 'placeholder' => 'Qty']) !!}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {!! Form::label('unit_price[]', 'Unit Price (RM):') !!}<span class="asterisk"> *</span>
                                {!! Form::number('unit_price[]', $item->unit_price, ['class' => 'form-control unit-price', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00']) !!}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {!! Form::label('amount[]', 'Amount (RM):') !!}
                                {!! Form::number('amount[]', $item->amount, ['class' => 'form-control amount', 'readonly' => true, 'placeholder' => '0.00']) !!}
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label class="d-block">&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-sm remove-product" {{ $loop->first && $machineRental->items->count() == 1 ? 'disabled' : '' }}>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <!-- First Product Item (for create) -->
                    <div class="product-item row mb-3 align-items-end">
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('product_id[]', 'Product:') !!}<span class="asterisk"> *</span>
                                {!! Form::select('product_id[]', $productItems, null, ['class' => 'form-control product-select', 'placeholder' => 'Pick a Product...']) !!}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {!! Form::label('uom[]', 'UOM:') !!}<span class="asterisk"> *</span>
                                <select name="uom[]" class="form-control uom-select" placeholder="Select UOM" disabled>
                                    <option value="">Select UOM</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {!! Form::label('quantity[]', 'Quantity:') !!}<span class="asterisk"> *</span>
                                {!! Form::number('quantity[]', null, ['class' => 'form-control quantity', 'min' => '1', 'step' => '1', 'placeholder' => 'Qty']) !!}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {!! Form::label('unit_price[]', 'Unit Price (RM):') !!}<span class="asterisk"> *</span>
                                {!! Form::number('unit_price[]', null, ['class' => 'form-control unit-price', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00']) !!}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {!! Form::label('amount[]', 'Amount (RM):') !!}
                                {!! Form::number('amount[]', null, ['class' => 'form-control amount', 'readonly' => true, 'placeholder' => '0.00']) !!}
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label class="d-block">&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-sm remove-product" disabled>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Add Product Button -->
            <div class="row mt-3">
                <div class="col-sm-12">
                    <button type="button" id="add-product" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Add Another Product
                    </button>
                </div>
            </div>
            
            <!-- Total Amount -->
            <div class="row mt-4 pt-3 border-top">
                <div class="col-sm-8 text-right">
                    <div class="form-group">
                        <strong>{!! Form::label('total_amount', 'Total Amount (RM):') !!}</strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::number('total_amount', isset($machineRental) ? $machineRental->total_amount : 0, ['class' => 'form-control total-amount font-weight-bold', 'readonly' => true, 'style' => 'font-size: 1.1em;']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remark Field -->
<div class="form-group col-sm-12" style = "margin-top: 20px;">
    {!! Form::label('remark', 'Remark:') !!}
    {!! Form::textarea('remark', isset($machineRental) ? $machineRental->remark : null, ['class' => 'form-control','maxlength' => 500, 'rows' => 3]) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12" style = "margin-top: 20px;">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('machineRentals.index') }}" class="btn btn-secondary">Cancel</a>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        HideLoad();
            
        // ========== MACHINE RENTAL NUMBER AUTO-GENERATION ==========
        const companyMachinePrefixes = {};
        @foreach($companies as $company)
            companyMachinePrefixes[{{ $company->id }}] = '{{ $company->machine_prefix ?? 'MR' }}';
        @endforeach

        console.log('🔍 Machine Rental - Company Prefixes:', companyMachinePrefixes);

        // Function to get next rental number via AJAX
        function getNextRentalNumber(companyId) {
            console.log('🔍 Machine Rental - getNextRentalNumber called with companyId:', companyId);
            
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '{{ route("machineRentals.getNextRentalNumber") }}',
                    method: 'POST',
                    data: {
                        company_id: companyId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('✅ Machine Rental - AJAX Success:', response);
                        if (response.success) {
                            resolve(response.rental_number);
                        } else {
                            reject(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Machine Rental - AJAX Error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        reject('Error generating rental number: ' + error);
                    }
                });
            });
        }

        // Auto-generate rental number when company is selected
        $(document).on('change', 'select[name="company_id"]', function() {
            const companyId = $(this).val();
            const rentalNumberInput = $('input[name="delivery_order_number"]');
            
            console.log('🔄 Machine Rental - Company changed:', companyId);
            
            if (companyId && companyMachinePrefixes[companyId]) {
                // Only auto-fill if the field is empty or contains the current prefix
                const currentValue = rentalNumberInput.val();
                const currentPrefix = companyMachinePrefixes[companyId];
                
                if (!currentValue || currentValue.startsWith(currentPrefix)) {
                    // Show loading
                    rentalNumberInput.val('Generating...');
                    
                    console.log('🔄 Machine Rental - Generating rental number for company:', companyId);
                    
                    // Get sequential rental number from server
                    getNextRentalNumber(companyId)
                        .then(rentalNumber => {
                            rentalNumberInput.val(rentalNumber);
                            console.log('✅ Machine Rental - Generated rental number:', rentalNumber);
                        })
                        .catch(error => {
                            // If AJAX fails, use fallback
                            const currentPrefix = companyMachinePrefixes[companyId];
                            const fallbackNumber = currentPrefix + '-000001';
                            rentalNumberInput.val(fallbackNumber);
                            console.error('❌ Machine Rental - AJAX failed, using fallback:', error);
                        });
                }
            }
        });

        // Check if we're in edit mode and company is already selected
        function checkEditModeRentalNumber() {
            const companyId = $('select[name="company_id"]').val();
            const rentalNumberInput = $('input[name="delivery_order_number"]');
            
            if (companyId && (!rentalNumberInput.val() || rentalNumberInput.val() === '')) {
                console.log('🔄 Machine Rental - Edit mode detected, generating number');
                rentalNumberInput.val('Generating...');
                
                getNextRentalNumber(companyId)
                    .then(rentalNumber => {
                        rentalNumberInput.val(rentalNumber);
                    })
                    .catch(error => {
                        const currentPrefix = companyMachinePrefixes[companyId];
                        const fallbackNumber = currentPrefix + '-000001';
                        rentalNumberInput.val(fallbackNumber);
                    });
            }
        }
        
        // Run check on page load for edit mode
        setTimeout(function() {
            checkEditModeRentalNumber();
        }, 500);

        // ========== PRODUCT PRICING WITH UOM ==========
        // Pre-load product UOMs from JSON data
        const productUomData = {};
        @foreach($productsWithUoms as $product)
            productUomData[{{ $product['id'] }}] = {!! json_encode($product['uoms']) !!};
        @endforeach
        
        console.log('Product UOM Data:', productUomData);

        // Template for new product row
        function getProductRowTemplate() {
            return `
                <div class="product-item row mb-3 align-items-end">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Product:<span class="asterisk"> *</span></label>
                            <select name="product_id[]" class="form-control product-select" placeholder="Pick a Product...">
                                <option value="">Pick a Product...</option>
                                @foreach($productItems as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>UOM:<span class="asterisk"> *</span></label>
                            <select name="uom[]" class="form-control uom-select" placeholder="Select UOM" disabled>
                                <option value="">Select UOM</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Quantity:<span class="asterisk"> *</span></label>
                            <input type="number" name="quantity[]" class="form-control quantity" min="1" step="1" placeholder="Qty">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Unit Price (RM):<span class="asterisk"> *</span></label>
                            <input type="number" name="unit_price[]" class="form-control unit-price" min="0" step="0.01" placeholder="0.00" readonly>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Amount (RM):</label>
                            <input type="number" name="amount[]" class="form-control amount" readonly placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <div class="form-group">
                            <label class="d-block">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-product">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Add new product row
        $('#add-product').click(function() {
            $('#products-container').append(getProductRowTemplate());
            updateRemoveButtons();
        });

        // Remove product row
        $(document).on('click', '.remove-product', function() {
            if ($('.product-item').length > 1) {
                $(this).closest('.product-item').remove();
                calculateTotal();
                updateRemoveButtons();
            }
        });

        // Update remove buttons state (disable for first item)
        function updateRemoveButtons() {
            $('.remove-product').prop('disabled', false);
            if ($('.product-item').length === 1) {
                $('.remove-product').first().prop('disabled', true);
            }
        }

        // Populate UOM dropdown when product is selected
        function populateUomOptions(uomSelect, uomArray) {
            uomSelect.empty().append('<option value="">Select UOM</option>');
            
            if (Array.isArray(uomArray) && uomArray.length > 0) {
                $.each(uomArray, function(index, uom) {
                    if (uom && uom.name && uom.price !== undefined) {
                        uomSelect.append($('<option>', {
                            value: uom.name,
                            text: uom.name,
                            'data-price': uom.price
                        }));
                    }
                });
            }
            
            uomSelect.prop('disabled', false);
        }

        // Auto-fill UOM options when product is selected
        $(document).on('change', '.product-select', function() {
            var productId = $(this).val();
            var row = $(this).closest('.product-item');
            var uomSelect = row.find('.uom-select');
            var unitPriceInput = row.find('.unit-price');
            
            console.log('Product selected:', productId, 'UOM Data:', productUomData[productId]);
            
            // Clear previous values
            uomSelect.empty().append('<option value="">Select UOM</option>').prop('disabled', true);
            unitPriceInput.val('').prop('readonly', true);
            row.find('.quantity').val('');
            row.find('.amount').val('');
            
            if (productId && productUomData[productId]) {
                // Populate UOM dropdown with options from product's JSON uoms
                populateUomOptions(uomSelect, productUomData[productId]);
                console.log('UOM dropdown populated for product:', productId);
            } else {
                uomSelect.prop('disabled', true);
                console.log('No UOM data found for product:', productId);
            }
            
            calculateTotal();
        });

        // When UOM is selected, auto-fill the price
        $(document).on('change', '.uom-select', function() {
            var row = $(this).closest('.product-item');
            var selectedOption = $(this).find('option:selected');
            var unitPriceInput = row.find('.unit-price');
            
            console.log('UOM selected:', selectedOption.val(), 'Price data:', selectedOption.data('price'));
            
            if (selectedOption.val()) {
                // Get price from data-price attribute
                var price = selectedOption.data('price');
                if (price !== undefined) {
                    unitPriceInput.val(price).prop('readonly', false);
                    console.log('Price filled:', price);
                    
                    // Auto-calculate amount if quantity is already filled
                    var quantity = row.find('.quantity').val();
                    if (quantity && quantity > 0) {
                        calculateRowAmount(row);
                        calculateTotal();
                    }
                }
            } else {
                unitPriceInput.val('').prop('readonly', true);
                row.find('.amount').val('');
                calculateTotal();
            }
        });

        // Calculate amount when quantity changes
        $(document).on('input', '.quantity', function() {
            var row = $(this).closest('.product-item');
            var unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            
            if (unitPrice > 0) {
                calculateRowAmount(row);
                calculateTotal();
            }
        });

        // Calculate amount when unit price changes manually (if editable)
        $(document).on('input', '.unit-price', function() {
            var row = $(this).closest('.product-item');
            calculateRowAmount(row);
            calculateTotal();
        });

        // Calculate amount for a single row
        function calculateRowAmount(row) {
            var quantity = parseFloat(row.find('.quantity').val()) || 0;
            var unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            var amount = quantity * unitPrice;
            
            row.find('.amount').val(amount.toFixed(2));
        }

        // Calculate total amount
        function calculateTotal() {
            var total = 0;
            $('.amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('.total-amount').val(total.toFixed(2));
        }

        // Initialize calculations for existing items on edit page
        function initializeExistingItems() {
            $('.product-item').each(function() {
                var productId = $(this).find('.product-select').val();
                var uomSelect = $(this).find('.uom-select');
                
                if (productId && productUomData[productId]) {
                    // For edit mode, we need to populate the UOM dropdown
                    // but keep the saved value selected
                    var savedUom = uomSelect.val();
                    populateUomOptions(uomSelect, productUomData[productId]);
                    uomSelect.val(savedUom);
                    
                    // Enable unit price field for editing
                    var unitPriceInput = $(this).find('.unit-price');
                    if (savedUom && unitPriceInput.val()) {
                        unitPriceInput.prop('readonly', false);
                    }
                }
                
                calculateRowAmount($(this));
            });
            calculateTotal();
            updateRemoveButtons();
        }

        // Initialize
        initializeExistingItems();

        // Form validation before submit
        $('form').on('submit', function(e) {
            var hasProducts = false;
            var hasErrors = false;
            
            // Check if at least one product is selected
            $('.product-select').each(function() {
                if ($(this).val()) {
                    hasProducts = true;
                }
            });
            
            if (!hasProducts) {
                e.preventDefault();
                alert('Please add at least one product.');
                hasErrors = true;
                return false;
            }
            
            // Validate that all selected products have required fields
            $('.product-item').each(function() {
                var productId = $(this).find('.product-select').val();
                var uom = $(this).find('.uom-select').val();
                var quantity = $(this).find('.quantity').val();
                var unitPrice = $(this).find('.unit-price').val();
                
                if (productId) {
                    if (!uom) {
                        if (!hasErrors) {
                            e.preventDefault();
                            alert('Please select UOM for all products.');
                            hasErrors = true;
                        }
                    }
                    
                    if (!quantity || quantity <= 0 || !unitPrice || unitPrice <= 0) {
                        if (!hasErrors) {
                            e.preventDefault();
                            alert('Please fill in both quantity and unit price for all selected products.');
                            hasErrors = true;
                        }
                    }
                }
            });
        });
        
        // Debug: Log all product data on page load
        console.log('All product UOM data:', productUomData);
    });

    // Escape key handler
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            $('form a.btn-secondary')[0].click();
        }
    });
</script>

<style>
.asterisk {
    color: #dc3545;
}
.product-item {
    padding-bottom: 15px;
}
.product-item:last-child {
    border-bottom: none;
}
.form-group {
    margin-bottom: 0;
}
.align-items-end {
    align-items: flex-end;
}
.border-top {
    border-top: 2px solid #dee2e6 !important;
}
</style>
@endpush