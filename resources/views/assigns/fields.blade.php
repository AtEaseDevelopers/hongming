<!-- Driver Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('driver_id', __('assign.driver')) !!}<span class="asterisk"> *</span>
    {!! Form::select('driver_id', $driverItems, null, ['class' => 'form-control select2-driver', 'placeholder' => 'Pick a Driver...','autofocus']) !!}
</div>

<!-- Delivery Order Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('delivery_order_id', __('Delivery Order')) !!}<span class="asterisk"> *</span>
    {!! Form::select('delivery_order_id', $delivery_order, null, ['class' => 'form-control select2-delivery', 'placeholder' => 'Pick a Delivery Order...']) !!}
</div>

<!-- Sequence Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sequence', __('assign.sequence')) !!}<span class="asterisk"> *</span>
    {!! Form::number('sequence', null, ['class' => 'form-control', 'min' => 0]) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('assign.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('assigns.index') }}" class="btn btn-secondary">{{ __('assign.cancel') }}</a>
</div>

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        $(document).ready(function () {
            // Initialize Select2 for delivery field
            $('.select2-delivery').select2({
                placeholder: "Search for a Delivery Order...",
                allowClear: true,
                width: '100%'
            });
            
            // Initialize Select2 for driver field
            $('.select2-driver').select2({
                placeholder: "Search for a driver...",
                allowClear: true,
                width: '100%'
            });
            
            HideLoad();
        });
    </script>

    <style>
        /* Optional: Style the Select2 dropdowns to match your theme */
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            border-radius: .25rem;
            height: 38px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endpush