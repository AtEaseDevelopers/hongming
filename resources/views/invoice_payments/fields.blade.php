<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', __('invoices.customer')) !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control select2-customer', 'placeholder' => 'Pick a Customer...']) !!}
</div>

<!-- Invoice Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('invoice_id', __('invoice_payments.invoice')) !!}
    <select name="invoice_id" id="invoice_id" class="form-control select2-invoice" placeholder="Pick an Invoice...">
        <option value="">Pick an Invoice...</option>
        @if (isset($selectedInvoice))
            <option value="{{ $selectedInvoice->id }}" selected>
                {{ $selectedInvoice->invoiceno }} - RM {{ number_format($selectedInvoice->total_amount ?? 0, 2) }} - {{ $selectedInvoice->date }}
            </option>
        @endif
    </select>
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', __('invoice_payments.type')) !!}<span class="asterisk"> *</span>
    {{ Form::select('type', array(1 => 'Cash', 3 => 'Online BankIn', 4 => 'E-wallet', 5 => 'Cheque'), null, ['class' => 'form-control']) }}
</div>

<!-- ChequeNo Field -->
<div class="form-group col-sm-6" id='cheque-container' style='display:none;'>
    {!! Form::label('chequeno', __('invoice_payments.cheque_no')) !!}
    {!! Form::text('chequeno', null, ['class' => 'form-control', 'maxlength' => 20]) !!}
</div>

<!-- Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('amount', __('invoice_payments.amount')) !!}<span class="asterisk"> *</span>
    {!! Form::text('amount', null, ['class' => 'form-control', 'min' => 0, 'step' => 0.01]) !!}
</div>

@can('paymentapprove')
<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('invoice_payments.status')) !!}
    {{ Form::select('status', array(0 => 'New', 1 => 'Completed', 2 => 'Canceled'), null, ['class' => 'form-control']) }}
</div>
@endcan

<!-- Attachment Field -->
<div class="form-group col-sm-6">
    {!! Form::label('attachment', __('invoice_payments.attachment')) !!}
    <div class="custom-file">
        <input type="file" class="custom-file-input" name="attachment" id="attachment" enctype="multipart/form-data" accept=".jpg, .jpeg, .png, .pdf">
        <label id="attachment-label" class="custom-file-label" for="attachment" accept=".jpg, .jpeg, .png, .pdf">Choose file</label>
    </div>
</div>

<!-- Remark Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remark', __('invoice_payments.remark')) !!}
    {!! Form::text('remark', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('invoice_payments.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('invoicePayments.index') }}" class="btn btn-secondary">{{ __('invoice_payments.cancel') }}</a>
</div>

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        
        $(document).ready(function () {
            // Initialize select2 for customer
            $('.select2-customer').select2({
                placeholder: "Search for a customer...",
                allowClear: true,
                width: '100%'
            });
            
            // Initialize select2 for invoice
            $('.select2-invoice').select2({
                placeholder: "Search for an invoice...",
                allowClear: true,
                width: '100%'
            });
            
            HideLoad();
            
            // If editing, ensure the invoice is properly selected
            @if (isset($selectedInvoice))
                $('#invoice_id').val("{{ $selectedInvoice->id }}").trigger('change');
            @endif
        });
        
        $("#attachment").on("change", function(){
            if(this.value != ''){
                $('#attachment-label').html(this.value);
            }else{
                $('#attachment-label').html('Choose file');
            }
        });
        
        $("#customer_id").change(function(){
            ShowLoad();
            let customerId = $('#customer_id').val();

            // Always clear the invoice dropdown first
            $('#invoice_id').empty().append('<option value="">Pick an Invoice...</option>');
            $('#invoice_id').val(null).trigger('change');
            
            if (customerId === '') {
                HideLoad();
            } else {
                var url = '{{ config("app.url") }}/invoicePayments/customer-invoices/' + customerId;

                $.get(url, function(data, status){
                    if (status === 'success') {
                        if (data.status) {
                            var options = '<option value="">Pick an Invoice...</option>';
                            if (data.data.length > 0) {
                                $.each(data.data, function(key, invoice) {
                                    options += `<option value="${invoice.id}">
                                        ${invoice.invoiceno} - RM ${invoice.total_amount.toFixed(2)} - ${invoice.date}
                                    </option>`;
                                });
                            }
                            // Update the invoice select dropdown
                            $('#invoice_id').empty().append(options);
                        } else {
                            noti('e', 'Please contact your administrator', data.message);
                        }
                    } else {
                        noti('e', 'Please contact your administrator', '');
                    }
                    HideLoad();
                });
            }
        });
        
        $("#invoice_id").on("change", function(){
            getinvoice();
        });
        
        function getinvoice(){
            var invoice_id = $('#invoice_id').val();
            if(invoice_id != ''){
                ShowLoad();
                var url = '{{ config("app.url") }}/invoicePayments/getinvoice/'+invoice_id;
                $.get(url, function(data, status){
                    if(status == 'success'){
                        if(data.status){
                            var customer_id = data.data.customer_id;
                            var amount = 0;
                            data.data.invoicedetail.forEach((element, index, array) => {
                                amount = amount + element.totalprice;
                            });
                            $('#customer_id').val(customer_id).trigger('change');
                            $('#amount').val(amount);
                        }else{
                            noti('e','Please contact your administrator',data.message);
                        }
                        HideLoad();
                    }else{
                        noti('e','Please contact your administrator','')
                        HideLoad();
                    }
                }); 
            }
        }
        
        $('#type').change(function(){
            if($(this).val() == "5") {
                $('#cheque-container').show();
            } else {
                $('#cheque-container').hide();
            }
        });
    </script>
    <style>
        /* Select2 search field styling */
        .select2-container--default .select2-search--dropdown .select2-search__field {
            padding: 6px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            box-shadow: none;
        }

        /* Dropdown menu styling */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
            color: white;
        }

        /* Selected item styling */
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
            color: #495057;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        /* Focus/hover states */
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Dropdown width adjustment */
        .select2-container {
            width: 100% !important;
        }

    </style>
@endpush