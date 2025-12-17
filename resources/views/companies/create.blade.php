@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
         <a href="{!! route('companies.index') !!}">{{ __('Branch')}}</a>
      </li>
      <li class="breadcrumb-item active">{{ __('Create Branch')}}</li>
    </ol>
     <div class="container-fluid">
          <div class="animated fadeIn">
                @include('coreui-templates::common.errors')
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-plus-square-o fa-lg"></i>
                                <strong>{{ __('Create Branch')}}</strong>
                            </div>
                            <div class="card-body">
                                {!! Form::open(['route' => 'companies.store']) !!}

                                   @include('companies.fields')

                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
           </div>
    </div>
@endsection
