<!-- Driver Code Field -->
<div class="form-group">
    {!! Form::label('driver_id', __('assign.driver_code')) !!}:
    <p>{{ $assign->driver->employeeid }}</p>
</div>

<!-- Driver Name Field -->
<div class="form-group">
    {!! Form::label('driver_id', __('assign.driver_name')) !!}:
    <p>{{ $assign->driver->name }}</p>
</div>

<!-- Delivery Order Field -->
<div class="form-group">
    {!! Form::label('delivery_order_id', __('Delivery Order')) !!}:
    <p>{{ $assign->deliveryOrder->dono }}</p>
</div>

<!-- Sequence Field -->
<div class="form-group">
    {!! Form::label('sequence', __('assign.sequence')) !!}:
    <p>{{ $assign->sequence }}</p>
</div>

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('.card .card-header a')[0].click();
            }
        });
        $(document).ready(function () {
            HideLoad();
        });
    </script>
@endpush