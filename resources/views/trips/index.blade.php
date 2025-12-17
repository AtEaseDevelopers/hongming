@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">{{ __('trips.trips') }}</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
             @include('flash::message')
             <div class="row">
                 <div class="col-lg-12">
                     <div class="card">
                         <div class="card-header">
                             <i class="fa fa-align-justify"></i>
                             {{ __('trips.trips') }}
                         </div>
                         <div class="card-body">
                             @include('trips.table')
                              <div class="pull-right mr-3">
                                     
                              </div>
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
            if(e.altKey && e.keyCode == 78){
                $('.card .card-header a')[0].click();
            } 
        });

        // Add this to your trips index view
        $(document).on('click', '.btn-end-trip', function() {
            const tripId = $(this).data('trip-id');
            const driverName = $(this).data('driver-name');
            const lorryNo = $(this).data('lorry-no');
            const $button = $(this);
            
            if (confirm(`Are you sure you want to end this trip?\nDriver: ${driverName}\nLorry: ${lorryNo}`)) {
                // Show loading on the button
                $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Ending...');
                
                $.ajax({
                    url: '{{ route("trips.endTrip") }}', // Remove the /tripId from URL
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        trip_id: tripId  // Send trip_id in request body
                    },
                    success: function(response) {
                        // Reload the table or update the row
                        $('.dataTableBuilder').DataTable().ajax.reload(null, false);
                        // Show success message using toastr or Flash
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Trip ended successfully');
                            location.reload();

                        } else {
                            // Fallback to alert if toastr is not available
                            alert('Trip ended successfully');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error ending trip:', xhr);
                        let errorMessage = 'Error ending trip';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.statusText) {
                            errorMessage = xhr.statusText;
                        }
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                        $button.prop('disabled', false).html('<i class="fa fa-flag-checkered"></i> End Trip');
                    }
                });
            }
        });

    </script>
@endpush

