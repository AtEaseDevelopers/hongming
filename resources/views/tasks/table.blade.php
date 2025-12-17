@section('css')
    @include('layouts.datatables_css')
@endsection

{!! $dataTable->table(['width' => '100%', 'class' => 'table table-striped table-bordered'], true) !!}

@push('scripts')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}
    
    <script>
        $(document).ready(function () {
            $(".buttons-reset").click(function(e){
                $('#dataTableBuilder tfoot th input').val('');
                $('#dataTableBuilder tfoot th select').val(1);
            });
            
            var table = $('#dataTableBuilder').DataTable();
            
            table.on( 'draw', function () {
                setcheckbox(window.checkboxid);
                checkcheckbox();
                HideLoad();
            });
            
            table.on( 'preDraw', function () {
                ShowLoad();
            });
            
            if(resize == 1){
                $('#dataTableBuilder').resizableColumns();
            }
        });

        function getTableCode(data) {
            var tbl  = document.createElement("table");
            tbl.className = "table table-sm table-striped";
            var tr = tbl.insertRow(-1);
            $.each( data[0], function( key, value ) {
                var td = tr.insertCell();
                td.appendChild(document.createTextNode(key.charAt(0).toUpperCase() + key.slice(1)));
            });
            for (var i = 0; i < data.length; ++i) {
                var tr = tbl.insertRow();
                $.each( data[i], function( key, value ) {
                    var td = tr.insertCell();
                    td.appendChild(document.createTextNode(value.toString()));
                });
            }
            return tbl;
        }
        
        function searchDateColumn(i){
            $('#columnid').val(i.id);
            $('#dateModel').modal('show');
        }

        function dateRange(steps = 1) {
            if($('#datefrommodel').val() == ''){
                noti('i','Date From cannot be empty','Please select the Date From');
                return;
            }
            if($('#datetomodel').val() == ''){
                noti('i','Date To cannot be empty','Please select the Date To');
                return;
            }
            if($('#datetomodel').val() < $('#datefrommodel').val()){
                noti('i','Date From cannot greater than Date To','Please select the Date again');
                return;
            }
            var dateArray = '';
            var currentDateParts = $('#datefrommodel').val().split("-");
            var endDateParts = $('#datetomodel').val().split("-");
            var currentDate = new Date(+currentDateParts[2], currentDateParts[1] - 1, +currentDateParts[0]);
            var endDate = new Date(+endDateParts[2], endDateParts[1] - 1, +endDateParts[0]);
                
            while (currentDate <= endDate) {
                dateArray=dateArray+moment(currentDate).format("YYYY-MM-DD")+'|';
                currentDate.setUTCDate(currentDate.getUTCDate() + steps);
            }

            $('#'+$('#columnid').val()).val(dateArray.substring(0, dateArray.length-1)).change();
            $('#dateModel').modal('hide');
        }
        
        var start = moment();
        var end = moment();

        function cb(start, end) {
            $('#reportrange span').html(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
            $('#datefrommodel').val(start.format('DD-MM-YYYY'));
            $('#datetomodel').val(end.format('DD-MM-YYYY'));
        }
        
        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        window.checkboxid = [];
        $(document).on("change", ".checkboxselect", function(e){
            if(this.checked){
                addcheckboxid($(this).attr('checkboxid'));
            }
            else{
                removecheckboxid($(this).attr('checkboxid'));
            }
            checkcheckbox();
        });
        
        $(document).on("change", "#selectallcheckbox", function(e){
            var checkall = this.checked;
            $('.checkboxselect').each(function(i, obj) {
                if(checkall){
                    if(!obj.checked){
                        addcheckboxid($(obj).attr('checkboxid'));
                        $(obj).prop( "checked", checkall );
                    }
                }else{
                    if(obj.checked){
                        removecheckboxid($(obj).attr('checkboxid'));
                        $(obj).prop( "checked", checkall );
                    }
                }
            });
        });
        
        function addcheckboxid(checkboxid){
            window.checkboxid.push(checkboxid);
        }
        
        function removecheckboxid(checkboxid){
            window.checkboxid = jQuery.grep(window.checkboxid, function(value) {
                return value != checkboxid;
            });
        }
        
        function setcheckbox(checkboxids){
            for (i = 0; i < checkboxids.length; ++i) {
                $('input[class="checkboxselect"][checkboxid="'+checkboxids[i]+'"]').prop( "checked", true );
            }
        }
        
        function checkcheckbox(){
            var checked = 0;
            var checkbox = $('.checkboxselect');
            checkbox.each(function(i, obj) {
                if(obj.checked){
                    checked ++;
                }
            });
            if(checked == checkbox.length){
                $('#selectallcheckbox').prop( "checked", true );
            }else{
                $('#selectallcheckbox').prop( "checked", false );
            }
        }

        // ========== TASK STATUS MODAL FUNCTIONALITY ==========
        $(document).ready(function() {
            // Initialize modal functionality after DataTable is ready
            setTimeout(function() {
                initializeTaskModal();
            }, 1000);
            
            // Re-initialize when DataTable is redrawn
            $('#dataTableBuilder').on('draw.dt', function() {
                setTimeout(function() {
                    initializeTaskModal();
                }, 500);
            });
        });

        function initializeTaskModal() {
            // Handle Complete button click
            $(document).off('click', '.complete-task-btn').on('click', '.complete-task-btn', function() {
                const taskId = $(this).data('task-id');
                const taskNumber = $(this).data('task-number');
                const currentStatus = $(this).data('current-status');
                
                // Set modal data
                $('#modal_task_id').val(taskId);
                $('#modal_task_number').text(taskNumber);
                $('#modal_current_status').text(currentStatus);
                
                // Clear previous form data
                $('#modal_status').val('2'); // Default to Completed
                $('#modal_return_reason').val('');
                $('#modal_return_remarks').val('');
                $('#modal_signed_do_image').val('');
                $('#modal_proof_of_delivery_image').val('');
                
                // Show/hide fields based on default status
                toggleModalFields('2');
                
                // Show modal
                $('#taskStatusModal').modal('show');
            });
            
            // Handle status change in modal
            $('#modal_status').off('change').on('change', function() {
                const status = $(this).val();
                toggleModalFields(status);
            });
            
            // Toggle modal fields based on status
            function toggleModalFields(status) {
                const isCompleted = status === '2';
                const isReturned = status === '3';
                
                // Toggle return reason field
                if (isReturned) {
                    $('#modal_return_reason_field').slideDown();
                    $('#modal_return_reason').prop('required', true);
                } else {
                    $('#modal_return_reason_field').slideUp();
                    $('#modal_return_reason').prop('required', false);
                }
                
                // Toggle return remarks field
                if (isReturned) {
                    $('#modal_return_remarks_field').slideDown();
                } else {
                    $('#modal_return_remarks_field').slideUp();
                }
                
                // Toggle image upload fields
                if (isCompleted) {
                    $('#modal_image_upload_fields').slideDown();
                    $('#modal_signed_do_image').prop('required', true);
                    $('#modal_proof_of_delivery_image').prop('required', true);
                } else {
                    $('#modal_image_upload_fields').slideUp();
                    $('#modal_signed_do_image').prop('required', false);
                    $('#modal_proof_of_delivery_image').prop('required', false);
                }
            }
            
            // Handle form submission
            $('#taskStatusForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const taskId = $('#modal_task_id').val();
                const status = $('#modal_status').val();
                
                // Validation
                if (status === '3' && !$('#modal_return_reason').val()) {
                    alert('Return reason is required when marking task as returned.');
                    return;
                }
                
                if (status === '2') {
                    const signedDoImage = $('#modal_signed_do_image').prop('files')[0];
                    const proofImage = $('#modal_proof_of_delivery_image').prop('files')[0];
                    
                    if (!signedDoImage) {
                        alert('Signed Delivery Order Image is required for Completed status.');
                        return;
                    }
                    
                    if (!proofImage) {
                        alert('Proof of Delivery Image is required for Completed status.');
                        return;
                    }
                }

                // Show loading
                ShowLoad();
                
                // Add CSRF token to form data
                formData.append('_token', '{{ csrf_token() }}');
                
                // Submit form
                $.ajax({
                    url: "{{ route('tasks.updateStatusViaModal') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        HideLoad();
                        if (response.success) {
                            noti('s', 'Success', response.message);
                            $('#taskStatusModal').modal('hide');
                            // Clear form
                            $('#taskStatusForm')[0].reset();
                            // Reload DataTable
                            $('.buttons-reload').click();
                        } else {
                            noti('e', 'Error', response.message);
                        }
                    },
                    error: function(xhr) {
                        HideLoad();
                        let errorMessage = 'An error occurred while updating task status.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        noti('e', 'Error', errorMessage);
                    }
                });
            });
        }
    </script>
@endpush

<!-- Modal for Complete/Return Action -->
<div class="modal fade" id="taskStatusModal" tabindex="-1" role="dialog" aria-labelledby="taskStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskStatusModalLabel">Update Delivery Order Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="taskStatusForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="task_id" id="modal_task_id" value="">
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>#DO:</strong> <span id="modal_task_number"></span>
                        <br>
                        <strong>Current Status:</strong> <span id="modal_current_status"></span>
                    </div>
                    
                    <!-- Status Selection -->
                    <div class="form-group">
                        <label for="modal_status">Update Status to :</label>
                        <select name="status" id="modal_status" class="form-control" required>
                            <option value="2">Completed</option>
                            <option value="3">Returned</option>
                        </select>
                    </div>
                    <small class="form-text text-muted">
                        System will send notification to inform driver about the DO status has been updated
                    </small>
                    
                    <!-- Return Reason Field (Initially Hidden) -->
                    <div class="form-group" id="modal_return_reason_field" style="display: none;">
                        <label for="modal_return_reason">Return Reason <span class="text-danger">*</span></label>
                        <input type="text" name="return_reason" id="modal_return_reason" class="form-control" maxlength="255">
                        <small class="form-text text-muted">Required when marking DO as returned</small>
                    </div>
                    
                    <!-- Return Remarks Field (Initially Hidden) -->
                    <div class="form-group" id="modal_return_remarks_field" style="display: none;">
                        <label for="modal_return_remarks">Return Remarks</label>
                        <input type="text" name="return_remarks" id="modal_return_remarks" class="form-control" maxlength="255">
                        <small class="form-text text-muted">Additional notes about the return (optional)</small>
                    </div>
                    
                    <!-- Image Upload Fields -->
                    <div id="modal_image_upload_fields">
                        <div class="card mt-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">📷 Upload Required Images</h6>
                            </div>
                            <div class="card-body">
                                <!-- Signed DO Image Field -->
                                <div class="form-group">
                                    <label for="modal_signed_do_image">Signed Delivery Order Image <span class="text-danger">*</span></label>
                                    <input type="file" name="signed_do_image" id="modal_signed_do_image" class="form-control-file" accept="image/*">
                                    <small class="form-text text-muted">
                                        Upload signed delivery order document<br>
                                    </small>
                                </div>
                                
                                <!-- Proof of Delivery Image Field -->
                                <div class="form-group">
                                    <label for="modal_proof_of_delivery_image">Proof of Delivery Image <span class="text-danger">*</span></label>
                                    <input type="file" name="proof_of_delivery_image" id="modal_proof_of_delivery_image" class="form-control-file" accept="image/*">
                                    <small class="form-text text-muted">
                                        Upload location/proof of delivery photo<br>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>