<!-- resources/views/products/fields.blade.php -->
<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', __('products.code')) !!}<span class="asterisk"> *</span>
    {!! Form::text('code', null, ['class' => 'form-control', 'maxlength' => 255, 'autofocus']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', __('products.name')) !!}<span class="asterisk"> *</span>
    {!! Form::text('name', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', __('products.type')) !!}
    {{ Form::select('type', [
        0 => __('Material'),
        1 => __('Machine'),
    ], null, ['class' => 'form-control', 'id' => 'product-type']) }}
</div>

<!-- Countdown Field -->
<div class="form-group col-sm-6">
    {!! Form::label('countdown', __('Countdown')) !!}
    {!! Form::number('countdown', null, ['class' => 'form-control', 'min' => '1', 'max' => '1440']) !!}
    <small class="form-text text-muted">Minutes (1-1440)</small>
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('products.status')) !!}
    {{ Form::select('status', \App\Models\Product::$status, null, ['class' => 'form-control']) }}
</div>

<!-- UOMs Field -->
<div class="form-group col-sm-12">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">UOMs & Prices</h5>
            <small class="text-muted" id="uom-instruction">Add at least one UOM with price.</small>
        </div>
        <div class="card-body">
            <div id="uoms-container">
                @php
                    $productType = isset($product) ? $product->type : 1; // Default to Machine if not set
                    $productUoms = isset($product) && $product->uoms ? $product->uoms : [['name' => '', 'price' => ($productType == 1 ? '' : '0')]];
                @endphp
                
                @foreach($productUoms as $index => $uom)
                <div class="form-group uom-group mb-3 p-3 border rounded" data-index="{{ $index }}">
                    <div class="row">
                        <div class="col-sm-5">
                            <label>UOM Name (e.g., KG, Hour, Unit)</label>
                            <input type="text" name="uoms[{{ $index }}][name]" 
                                   class="form-control uom-name" value="{{ $uom['name'] }}" 
                                   placeholder="e.g., KG, Hour, Unit" required>
                        </div>
                        
                        <div class="col-sm-5 price-field-wrapper" style="{{ $productType == 0 ? 'display: none;' : '' }}">
                            <label>Price (RM)</label>
                            <input type="number" name="uoms[{{ $index }}][price]" 
                                   class="form-control uom-price" step="0.01" min="0" 
                                   value="{{ $productType == 1 ? $uom['price'] : '0' }}" 
                                   {{ $productType == 1 ? 'required' : '' }}>
                        </div>
                        
                        <!-- Hidden price field for Materials (always value 0) -->
                        @if($productType == 0)
                        <input type="hidden" name="uoms[{{ $index }}][price]" value="0">
                        @endif
                        
                        <div class="col-sm-2">
                            @if($index > 0)
                                <button type="button" class="btn btn-danger btn-sm mt-4 remove-uom">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <button type="button" id="add-uom" class="btn btn-success btn-sm mt-3">
                <i class="fa fa-plus"></i> Add Another UOM
            </button>
        </div>
    </div>
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('products.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('products.index') }}" class="btn btn-secondary">{{ __('products.cancel') }}</a>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            HideLoad();
            
            // Initialize based on current type
            togglePriceFields($('#product-type').val());
        });
        
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
    </script>
    
    <script>
        $(document).ready(function() {
            let uomIndex = {{ count($productUoms) }};
            
            // Handle product type change
            $('#product-type').change(function() {
                const type = $(this).val();
                togglePriceFields(type);
            });
            
            function togglePriceFields(type) {
                const isMachine = type == 1;
                
                if (isMachine) {
                    // Show price fields for Machine
                    $('.price-field-wrapper').show();
                    $('.uom-price').prop('required', true);
                    $('#uom-instruction').text('Add at least one UOM with price.');
                } else {
                    // Hide price fields for Material, set all to 0
                    $('.price-field-wrapper').hide();
                    $('.uom-price').prop('required', false);
                    $('.uom-price').val('0');
                    $('#uom-instruction').text('Add at least one UOM (price will be set to 0 for Materials).');
                    
                    // Update hidden price fields to 0
                    $('input[name*="[price]"]').not('.uom-price').val('0');
                }
            }
            
            // Add UOM row
            $('#add-uom').click(function() {
                const productType = $('#product-type').val();
                const isMachine = productType == 1;
                const priceRequired = isMachine ? 'required' : '';
                const priceValue = isMachine ? '' : '0';
                const priceFieldStyle = isMachine ? '' : 'style="display: none;"';
                const hiddenPriceField = isMachine ? '' : `<input type="hidden" name="uoms[${uomIndex}][price]" value="0">`;
                
                const template = `
                    <div class="form-group uom-group mb-3 p-3 border rounded" data-index="${uomIndex}">
                        <div class="row">
                            <div class="col-sm-5">
                                <label>UOM Name (e.g., KG, Hour, Unit)</label>
                                <input type="text" name="uoms[${uomIndex}][name]" 
                                       class="form-control uom-name" 
                                       placeholder="e.g., KG, Hour, Unit" required>
                            </div>
                            
                            <div class="col-sm-5 price-field-wrapper" ${priceFieldStyle}>
                                <label>Price (RM)</label>
                                <input type="number" name="uoms[${uomIndex}][price]" 
                                       class="form-control uom-price" step="0.01" min="0" 
                                       value="${priceValue}" ${priceRequired}>
                            </div>
                            
                            ${hiddenPriceField}
                            
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger btn-sm mt-4 remove-uom">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#uoms-container').append(template);
                uomIndex++;
            });
            
            // Remove UOM row
            $(document).on('click', '.remove-uom', function() {
                if ($('.uom-group').length > 1) {
                    $(this).closest('.uom-group').remove();
                    reindexUoms();
                }
            });
            
            // Reindex UOMs after removal
            function reindexUoms() {
                uomIndex = 0;
                $('.uom-group').each(function(index) {
                    $(this).attr('data-index', index);
                    $(this).find('[name*="[name]"]').attr('name', `uoms[${index}][name]`);
                    
                    // Handle visible price fields
                    const priceInput = $(this).find('.uom-price');
                    if (priceInput.length) {
                        priceInput.attr('name', `uoms[${index}][price]`);
                    }
                    
                    // Handle hidden price fields
                    const hiddenPriceInput = $(this).find('input[name*="[price]"]').not('.uom-price');
                    if (hiddenPriceInput.length) {
                        hiddenPriceInput.attr('name', `uoms[${index}][price]`);
                    }
                    
                    uomIndex++;
                });
            }
            
            // Form validation
            $('form').submit(function(e) {
                ShowLoad();
                
                // Check for duplicate UOM names
                let uomNames = [];
                let hasDuplicates = false;
                
                $('.uom-name').each(function() {
                    const value = $(this).val().trim();
                    if (value) {
                        if (uomNames.includes(value.toLowerCase())) {
                            hasDuplicates = true;
                        }
                        uomNames.push(value.toLowerCase());
                    }
                });
                
                if (hasDuplicates) {
                    e.preventDefault();
                    HideLoad();
                    alert('Each UOM name must be unique for this product.');
                    return false;
                }
            });
        });
    </script>
@endpush