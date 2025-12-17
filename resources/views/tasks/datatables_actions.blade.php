{!! Form::open(['route' => ['tasks.destroy', encrypt($id)], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('deliveryOrders.print', ['id' => encrypt($delivery_order_id),'task_id' => encrypt($id), 'function' => 'view'] ) }}" class='btn btn-ghost-primary' target="_blank">
       <i class="fa fa-print"></i>
    </a>
    <a href="{{ route('tasks.show', encrypt($id)) }}" class='btn btn-ghost-success'>
       <i class="fa fa-eye"></i>
    </a>
    <a href="{{ route('tasks.edit', encrypt($id)) }}" class='btn btn-ghost-info'>
       <i class="fa fa-edit"></i>
    </a>
      @if($status == 'Delivering')
    <!-- New Complete Action Button -->
    <button type="button" class="btn btn-ghost-warning complete-task-btn" 
            data-task-id="{{ $id }}"
            data-task-number="{{ $task_number }}"
            data-current-status="{{ $status }}">
        <i class="fa fa-check-circle"></i>
    </button>
      @endif
</div>
{!! Form::close() !!}