@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
          <li class="breadcrumb-item">
             <a href="{!! route('deliveryOrders.index') !!}">Project</a>
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
                              <strong>Edit Project</strong>
                          </div>
                          <div class="card-body">
                              {!! Form::model($deliveryOrder, ['route' => ['deliveryOrders.update', Crypt::encrypt($deliveryOrder->id)], 'method' => 'patch']) !!}

                              @include('delivery_orders.fields')

                              {!! Form::close() !!}
                              {{-- Move the approve form OUTSIDE the main form --}}
                                @if(isset($deliveryOrder) && $deliveryOrder->status == 0 && auth()->user()->roles()->pluck('name')->first() != 'normal admin')
                                    <form action="{{ route('deliveryOrders.approve', Crypt::encrypt($deliveryOrder->id)) }}" method="POST" style="display: inline; margin-left: 10px;">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this Project?')">
                                            <i class="fa fa-check"></i> Approve Project
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
         </div>
    </div>
@endsection