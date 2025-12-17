@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">Project</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
             @include('flash::message')
             <div class="row">
                 <div class="col-lg-12">
                     <div class="card">
                         <div class="card-header">
                             <i class="fa fa-align-justify"></i>
                             Project
                             @if(auth()->user()->roles()->pluck('name')->first() === 'normal admin')
                                <a class="pull-right" href="{{ route('deliveryOrders.create') }}"><i class="fa fa-plus-square fa-lg"></i></a>
                             @endif
                         </div>
                         <div class="card-body">
                             @include('delivery_orders.table')
                              <div class="pull-right mr-3">
                                     
                              </div>
                         </div>
                     </div>
                  </div>
             </div>
         </div>
    </div>
@endsection

@push('styles')
<style>
    .progress-popover {
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
    }
    .progress-popover:hover {
        background-color: #f8f9fa !important;
    }
    .progress-popover.active-popover {
        background-color: #6c757d !important;
        color: white !important;
    }
    .popover {
        max-width: 600px !important;
    }
    .popover-header {
        background-color: #007bff;
        color: white;
        border-bottom: 1px solid #0056b3;
    }
</style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
        // Re-initialize popovers when DataTable is redrawn
            $('#dataTableBuilder').on('draw.dt', function() {
                $('.progress-popover').popover('dispose');
                initializePopovers();
            });

            function initializePopovers() {
                $('.progress-popover').popover({
                    placement: "top",
                    trigger: "click",
                    container: "body",
                    html: true
                });

                // Handle popover show event to add active class
                $('.progress-popover').on('show.bs.popover', function() {
                    // Remove active class from all other popovers
                    $('.progress-popover').removeClass('active-popover');
                    // Add active class to current popover
                    $(this).addClass('active-popover');
                });

                // Handle popover hide event to remove active class
                $('.progress-popover').on('hide.bs.popover', function() {
                    $(this).removeClass('active-popover');
                });
            }

            // Initialize on page load
            initializePopovers();

            // Close popover when clicking elsewhere
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.progress-popover').length && 
                    !$(e.target).closest('.popover').length) {
                    $('.progress-popover').popover('hide');
                    $('.progress-popover').removeClass('active-popover');
                }
            });
        });

        $(document).keyup(function(e) {
            if(e.altKey && e.keyCode == 78){
                $('.card .card-header a')[0].click();
            } 
        });
        
        $(document).on("click", "#masssave", function(e){
            var m = "";
            if(window.checkboxid.length == 0){
                noti('i','Info','Please select at least one row');
                return;
            }else if(window.checkboxid.length == 1){
                m = "Confirm to save 1 row"
            }else{
                m = "Confirm to save " + window.checkboxid.length + " rows!"
            }
            $.confirm({
                title: 'Save View',
                content: m,
                buttons: {
                    Yes: function() {
                        masssave(window.checkboxid);
                    },
                    No: function() {
                        return;
                    }
                }
            });
            
        });

        function masssave(ids){
            ShowLoad();
            $.ajax({
                url: "{{config('app.url')}}/deliveryOrders/masssave",
                type:"POST",
                data:{
                ids: ids
                ,_token: "{{ csrf_token() }}"
                },
                success:function(response){
                    window.checkboxid = [];
                    $('.buttons-reload').click();
                    toastr.success('Please find Save View ID: '+response, 'Save Successfully', {showEasing: "swing", hideEasing: "linear", showMethod: "fadeIn", hideMethod: "fadeOut", positionClass: "toast-bottom-right", timeOut: 0, allowHtml: true });
                },
                error: function(error) {
                    noti('e','Please contact your administrator',error.responseJSON.message)
                    HideLoad();
                }
            });
        }
        
        $(document).on("click", "#massdelete", function(e){
            var m = "";
            if(window.checkboxid.length == 0){
                noti('i','Info','Please select at least one row');
                return;
            }else if(window.checkboxid.length == 1){
                m = "Confirm to delete 1 row!"
            }else{
                m = "Confirm to delete " + window.checkboxid.length + " rows!"
            }
            $.confirm({
                title: 'Mass Delete',
                content: m,
                buttons: {
                    Yes: function() {
                        massdelete(window.checkboxid);
                    },
                    No: function() {
                        return;
                    }
                }
            });
        });
        
        $(document).on("click", "#massactive", function(e){
            var m = "";
            if(window.checkboxid.length == 0){
                noti('i','Info','Please select at least one row');
                return;
            }else if(window.checkboxid.length == 1){
                m = "Confirm to update 1 row"
            }else{
                m = "Confirm to update " + window.checkboxid.length + " rows!"
            }
            $.confirm({
                title: 'Mass Update',
                content: m,
                buttons: {
                    Active: function() {
                        massupdatestatus(window.checkboxid,1);
                    },
                    Unactive: function() {
                        massupdatestatus(window.checkboxid,0);
                    },
                    somethingElse: {
                        text: 'Cancel',
                        btnClass: 'btn-gray',
                        keys: ['enter', 'shift']
                    }
                }
            });
            
        });

        function massdelete(ids){
            ShowLoad();
            $.ajax({
                url: "{{config('app.url')}}/deliveryOrders/massdestroy",
                type:"POST",
                data:{
                ids: ids
                ,_token: "{{ csrf_token() }}"
                },
                success:function(response){
                    window.checkboxid = [];
                    $('.buttons-reload').click();
                    noti('s','Delete Successfully',response+' row(s) had been deleted.')
                },
                error: function(error) {
                    noti('e','Please contact your administrator',error.responseJSON.message)
                    HideLoad();
                }
            });
        }
        function massupdatestatus(ids,status){
            ShowLoad();
            $.ajax({
                url: "{{config('app.url')}}/deliveryOrders/massupdatestatus",
                type:"POST",
                data:{
                ids: ids,
                status: status
                ,_token: "{{ csrf_token() }}"
                },
                success:function(response){
                    window.checkboxid = [];
                    $('.buttons-reload').click();
                    noti('s','Update Successfully',response+' row(s) had been updated.')
                },
                error: function(error) {
                    noti('e','Please contact your administrator',error.responseJSON.message)
                    HideLoad();
                }
            });
        }
    </script>
@endpush