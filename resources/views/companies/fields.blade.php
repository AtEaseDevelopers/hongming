<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', __('companies.code') . ':') !!}<span class="asterisk"> *</span>
    {!! Form::text('code', null, ['class' => 'form-control', 'maxlength' => 255, 'autofocus']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', __('Branch Name') . ':') !!}<span class="asterisk"> *</span>
    {!! Form::text('name', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('do_prefix', 'Project Prefix:') !!}<span class="asterisk"> *</span>
    {!! Form::text('do_prefix', null, [
        'class' => 'form-control', 
        'maxlength' => 10,
    ]) !!}
    <small class="form-text text-muted">This prefix will be used for Project numbers</small>
</div>

<div class="form-group col-sm-6">
    {!! Form::label('do_prefix', 'Machine Prefix:') !!}<span class="asterisk"> *</span>
    {!! Form::text('machine_prefix', null, [
        'class' => 'form-control', 
        'maxlength' => 10,
    ]) !!}
    <small class="form-text text-muted">This prefix will be used for Machine Rental numbers</small>
</div>

<div class="form-group col-sm-6">
    {!! Form::label('task_prefix', 'DO Prefix:') !!}<span class="asterisk"> *</span>
    {!! Form::text('task_prefix', null, [
        'class' => 'form-control', 
        'maxlength' => 10,
    ]) !!}
    <small class="form-text text-muted">This prefix will be used for Delivery Order numbers</small>
</div>

<!-- Ssm Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ssm', __('SSM') . ':') !!}<span class="asterisk"> *</span>
    {!! Form::text('ssm', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('phone', __('Contact Number') . ':') !!}<span class="asterisk"> *</span>
    {!! Form::text('phone', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>
<div class="form-group col-sm-6">
    {!! Form::label('email', __('Email') . ':') !!}<span class="asterisk"> *</span>
    {!! Form::text('email', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Address1 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address1', __('companies.address1') . ':') !!}
    {!! Form::text('address1', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Address2 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address2', __('companies.address2') . ':') !!}
    {!! Form::text('address2', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Address3 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address3', __('companies.address3') . ':') !!}
    {!! Form::text('address3', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Address4 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address4', __('companies.address4') . ':') !!}
    {!! Form::text('address4', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Group Id Field
<div class="form-group col-sm-6">
    {!! Form::label('group_id', __('companies.group') . ':') !!}<span class="asterisk"> *</span>
    {!! Form::select('group_id', $groups, $company->group_id ?? null, ['class' => 'selectpicker form-control', 'placeholder' =>'Select Group']) !!}
</div> -->

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('companies.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('companies.index') }}" class="btn btn-secondary">{{ __('companies.cancel') }}</a>
</div>

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