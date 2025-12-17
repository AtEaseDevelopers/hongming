@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
          <li class="breadcrumb-item">
             <a href="{!! route('machineRentals.index') !!}">Machine Rental</a>
          </li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
    <div class="container-fluid">
         <div class="animated fadeIn">
             @include('coreui-templates::common.errors')
             <div class="row">
                 <div class="col-lg-12">
                      <div class="card">
                          <div class="card-header">
                              <i class="fa fa-edit fa-lg"></i>
                              <strong>Edit Machine Rental</strong>
                          </div>
                          <div class="card-body">
                              {!! Form::model($machineRental, ['route' => ['machineRentals.update', Crypt::encrypt($machineRental->id)], 'method' => 'patch']) !!}

                              @include('machine_rental.fields')

                              {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
         </div>
    </div>
@endsection