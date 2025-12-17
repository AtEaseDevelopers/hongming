@push('styles')
<style>
    .form-group label {
        font-weight: bold;
        text-decoration: underline;
    }
    .product-table {
        font-size: 0.9em;
    }
    .total-section {
        background-color: #f8f9fa;
        border-top: 2px solid #dee2e6;
    }
</style>
@endpush

<!-- Date Field -->
<div class="form-group">
    {!! Form::label('date', 'Date:') !!}
    <p>{{ $machineRental->date->format('d-m-Y') }}</p>
</div>

<!-- Delivery Order Number Field -->
<div class="form-group">
    {!! Form::label('delivery_order_number', 'DO Number:') !!}
    <p>{{ $machineRental->delivery_order_number }}</p>
</div>

<!-- Customer Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer:') !!}
    <p>{{ $machineRental->customer->company }}</p>
</div>

<!-- Company Field -->
<div class="form-group">
    {!! Form::label('company_id', 'Branch:') !!}
    <p>{{ $machineRental->company->name }}</p>
</div>

<!-- Rental Products Section -->
<div class="form-group">
    {!! Form::label('products', 'Rental Products:') !!}
    <div class="card mt-2">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0 product-table">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="40%">Product Description</th>
                            <th width="10%" class="text-center">UOM</th>
                            <th width="10%" class="text-center">Quantity</th>
                            <th width="15%" class="text-right">Unit Price (RM)</th>
                            <th width="15%" class="text-right">Amount (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($machineRental->items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                @if($item->description)
                                <br><small class="text-muted">{{ $item->description }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->uom ?? '-' }}</td>
                            <td class="text-center">{{ number_format($item->quantity) }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="total-section">
                        <tr>
                            <td colspan="5" class="text-right"><strong>Total Amount:</strong></td>
                            <td class="text-right"><strong>RM {{ number_format($machineRental->total_amount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark:') !!}
    <p>{{ $machineRental->remark ?? 'N/A' }}</p>
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