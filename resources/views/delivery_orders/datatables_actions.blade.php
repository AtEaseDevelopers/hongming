{!! Form::open(['route' => ['deliveryOrders.destroy', Crypt::encrypt($id)], 'method' => 'delete']) !!}
<div class='btn-group'>

 <!-- Add Create Task Button -->

   @php
    $deliveryOrder = \App\Models\DeliveryOrder::find($id);
   @endphp

   @if($deliveryOrder && $deliveryOrder->getRemainingQuantity() > 0)
      @if($deliveryOrder->status != 0)
      <a href="{{ route('tasks.create', ['delivery_order_id' => Crypt::encrypt($id)]) }}" 
         class='btn btn-ghost-warning'>
         <i class="fa fa-plus-square"></i>
      </a>
      @endif
   @endif

    <a href="{{ route('deliveryOrders.print', ['id' => encrypt($id), 'function' => 'view'] ) }}" class='btn btn-ghost-primary' target="_blank">
       <i class="fa fa-print"></i>
    </a>
    
    <a href="{{ route('deliveryOrders.show', Crypt::encrypt($id)) }}" class='btn btn-ghost-success'>
       <i class="fa fa-eye"></i>
    </a>
    <a href="{{ route('deliveryOrders.edit', Crypt::encrypt($id)) }}" class='btn btn-ghost-info'>
       <i class="fa fa-edit"></i>
    </a>

</div>
{!! Form::close() !!}
