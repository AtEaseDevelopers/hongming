@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">{{ __('Check In') }}</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
             @include('flash::message')
             <div class="row">
                 <div class="col-lg-12">
                     <div class="card">
                         <div class="card-header">
                             <i class="fa fa-align-justify"></i>
                             {{ __('Check In') }}
                         </div>
                         <div class="card-body">
                             @include('checkins.table')
                              <div class="pull-right mr-3">
                                     
                              </div>
                         </div>
                     </div>
                  </div>
             </div>
         </div>
    </div>
    <!-- Check-in Details Modal -->
    <div class="modal fade" id="checkinModal" tabindex="-1" role="dialog" aria-labelledby="checkinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkinModalLabel">Check-in Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="checkinDetails">
                        <!-- Details will be loaded here via AJAX -->
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
        $(document).ready(function() {
            
        // Handle view check-in button click
        $(document).on('click', '.view-checkin', function() {
            var checkinId = $(this).data('id');
            
            // Show loading spinner
            $('#checkinDetails').html(`
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            `);
            
            // Load check-in details via AJAX
            $.ajax({
                url: '/checkins/' + checkinId + '/details',
                type: 'GET',
                success: function(response) {
                    $('#checkinDetails').html(response);
                },
                error: function(xhr) {
                    $('#checkinDetails').html(`
                        <div class="alert alert-danger">
                            Failed to load check-in details. Please try again.
                        </div>
                    `);
                }
            });
        });
    });

    </script>
@endpush

