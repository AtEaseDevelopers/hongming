@extends('layouts.app')

@section('content')
     <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('trips.index') }}">Trip</a>
            </li>
            <li class="breadcrumb-item active">End Trip Summary</li>
     </ol>
     <div class="container-fluid">
          <div class="animated fadeIn">
                 @include('coreui-templates::common.errors')
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>End Trip Summary</strong>
                                  <a href="{{ route('trips.index') }}" class="btn btn-light">Back</a>
                             </div>
                             <div class="card-body">
                                 @include('trips.show_fields')
                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>Product Sold</strong>
                               
                             </div>
                             <div class="card-body">
                                <table class="table table-striped table-bordered dataTable" width="100%" role="grid" style="width: 100%;">
                                    <thead>
                                        <tr role="row">
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($trip->productsold['details']) == 0)
                                            <tr class="odd">
                                                <td valign="top" colspan="10" class="dataTables_empty">No Product Sold</td>
                                            </tr>
                                        @endif
                                        @foreach($trip->productsold['details'] as $i=>$invoicedetail)
                                            @if( ($i+1) % 2 == 0 )

                                                <tr class="even">
                                                    <td>{{ $invoicedetail->name }}</td>
                                                    <td>{{ $invoicedetail->quantity }}</td>
                                                    <td>{{ $invoicedetail->price }}</td>
                                                   
                                                </tr>
                                            @else
                                                <tr class="odd">
                                                    <td>{{ $invoicedetail->name }}</td>
                                                    <td>{{ $invoicedetail->quantity }}</td>
                                                    <td>{{ $invoicedetail->price }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>

                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>Product FOC</strong>
                               
                             </div>
                             <div class="card-body">
                                <table class="table table-striped table-bordered dataTable" width="100%" role="grid" style="width: 100%;">
                                    <thead>
                                        <tr role="row">
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($trip->productfoc['details']) == 0)
                                            <tr class="odd">
                                                <td valign="top" colspan="10" class="dataTables_empty">No Product FOC</td>
                                            </tr>
                                        @endif
                                        @foreach($trip->productfoc['details'] as $i=>$invoicedetail)
                                            @if( ($i+1) % 2 == 0 )

                                                <tr class="even">
                                                    <td>{{ $invoicedetail->name }}</td>
                                                    <td>{{ $invoicedetail->quantity }}</td>
                                                   
                                                   
                                                </tr>
                                            @else
                                                <tr class="odd">
                                                    <td>{{ $invoicedetail->name }}</td>
                                                    <td>{{ $invoicedetail->quantity }}</td>
                                                   
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>

                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>Wastage</strong>
                               
                             </div>
                             <div class="card-body">
                                <table class="table table-striped table-bordered dataTable" width="100%" role="grid" style="width: 100%;">
                                    <thead>
                                        <tr role="row">
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($trip->wastage) == 0)
                                            <tr class="odd">
                                                <td valign="top" colspan="10" class="dataTables_empty">No Wastage</td>
                                            </tr>
                                        @endif
                                        @foreach($trip->wastage as $i=>$invoicedetail)
                                            @if( ($i+1) % 2 == 0 )

                                                <tr class="even">
                                                    <td>{{ $invoicedetail->name }}</td>
                                                    <td>{{ $invoicedetail->quantity }}</td>
                                                   
                                                </tr>
                                            @else
                                                <tr class="odd">
                                                    <td>{{ $invoicedetail->name }}</td>
                                                    <td>{{ $invoicedetail->quantity }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>

                             </div>
                         </div>
                     </div>
                 </div>
          </div>
    </div>
@endsection


@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        $(document).ready(function () {
            HideLoad();
        });
    </script>
@endpush
