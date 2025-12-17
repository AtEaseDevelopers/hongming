<?php

namespace App\DataTables;

use App\Models\DeliveryOrder;
use App\Models\Company;
use App\Models\Task;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryOrderDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable
            ->addColumn('action', 'delivery_orders.datatables_actions')
            ->addColumn('balance', function ($model) {
                $balance = $model->getRemainingQuantity();
                $balanceClass = $balance > 0 ? 'text-warning' : 'text-success';
                return '<span class="' . $balanceClass . '"><strong>' . $balance . '</strong></span>';
            })
            ->editColumn('status', function ($model) {
                $statusOptions = DeliveryOrder::getStatusOptions();
                $statusClass = [
                    0 => 'badge badge-danger',
                    1 => 'badge badge-dark',
                    2 => 'badge badge-primary',
                    3 => 'badge badge-warning',
                    4 => 'badge badge-info',
                    5 => 'badge badge-secondary',
                    6 => 'badge badge-success',
                    7 => 'badge badge-danger',
                ];
                
                $status = $model->status;
                $statusText = $statusOptions[$status] ?? 'Unknown';
                
                return '<span class="' . ($statusClass[$status] ?? 'badge badge-secondary') . '">' . $statusText . '</span>';
            })
            ->editColumn('date', function ($model) {
                $date = Carbon::parse($model->date);
                return $date->format('d-m-Y');
            })
            ->editColumn('total_order', function ($model) {
                return '<span class="text-primary"><strong>' . $model->total_order . '</strong></span>';
            })
            ->editColumn('progress_total', function ($model) {
                $progressPercent = $model->total_order > 0 ? ($model->progress_total / $model->total_order) * 100 : 0;
                
                if ($progressPercent >= 100) {
                    $progressClass = 'text-success';
                } else {
                    $progressClass = 'text-warning';
                }
                
                // Get task details for popover
                $taskDetails = $this->getTaskDetails($model);
                if ($taskDetails === 'No tasks assigned') {
                    return '<span class="' . $progressClass . ' progress-popover" 
                            data-toggle="popover" 
                            data-html="true" 
                            data-content="' . htmlspecialchars('<div style="min-width: 300px; padding: 10px; text-align: center; color: #666;">No tasks assigned</div>') . '" 
                            data-delivery-order-id="' . $model->id . '"
                            style="cursor: pointer; padding: 4px 8px; border-radius: 4px;">
                            <strong>' . $model->progress_total . ' / ' . $model->total_order . '</strong>
                            </span>';
                }
                
                return '<span class="' . $progressClass . ' progress-popover" 
                          data-toggle="popover" 
                          data-html="true" 
                          data-content="' . $taskDetails . '" 
                          data-delivery-order-id="' . $model->id . '"
                          style="cursor: pointer; padding: 4px 8px; border-radius: 4px;">
                          <strong>' . $model->progress_total . ' / ' . $model->total_order . '</strong>
                        </span>';
            })
            ->rawColumns(['status', 'action', 'progress_total', 'balance', 'total_order']); 
    }

    /**
     * Get task details for popover
     */
 
    private function getTaskDetails($deliveryOrder)
    {
        $tasks = $deliveryOrder->tasks()->with('driver', 'lorry')->get();
        
        if ($tasks->isEmpty()) {
            return 'No tasks assigned';
        }
        
        $html = '<div style="min-width: 350px; max-width: 500px;">';
        $html .= '<h6 style="margin-bottom: 15px; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 8px;">Task Details - ' . $deliveryOrder->dono . '</h6>';
        $html .= '<div style="max-height: 300px; overflow-y: auto;">';
        
        // Initialize counters
        $completedTasks = 0;
        $inProgressTasks = 0;
        $newTasks = 0;
        $returnedTasks = 0;
        
        foreach ($tasks as $task) {
            $statusClass = [
                Task::STATUS_NEW => 'badge-secondary',
                Task::STATUS_DELIVERING => 'badge-warning',
                Task::STATUS_COMPLETED => 'badge-success',
                Task::STATUS_RETURNED => 'badge-danger',
            ];
            
            // Get the raw status value from attributes
            $status = $task->getStatusValue();
            $statusText = Task::getStatusOptions()[$status] ?? 'Unknown';
            
            // Count tasks by status
            switch ($status) {
                case Task::STATUS_COMPLETED:
                    $completedTasks++;
                    break;
                case Task::STATUS_DELIVERING:
                    $inProgressTasks++;
                    break;
                case Task::STATUS_NEW:
                    $newTasks++;
                    break;
                case Task::STATUS_RETURNED:
                    $returnedTasks++;
                    break;
            }
            
            $html .= '<div style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; margin-bottom: 10px; background: #fafafa;">';
            $html .= '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">';
            $html .= '<div style="flex: 1;">';
            $html .= '<div style="font-weight: bold; color: #333; margin-bottom: 4px;">' . ($task->driver->name ?? 'N/A') . '</div>';
            $html .= '<div style="font-size: 12px; color: #666;">';
            $html .= '<strong>Lorry:</strong> ' . ($task->lorry->lorryno ?? 'N/A') . '<br>';
            $html .= '<strong>Quantity:</strong> <span style="color: #007bff; font-weight: bold;">' . $task->this_load . '</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<span class="badge ' . ($statusClass[$status] ?? 'badge-secondary') . '" style="font-size: 11px; padding: 4px 8px;">' . $statusText . '</span>';
            $html .= '</div>';
            
            // Show additional info for completed/delivering tasks
            if ($status === Task::STATUS_COMPLETED && $task->end_time) {
                $html .= '<div style="font-size: 11px; color: #28a745; background: #e8f5e8; padding: 4px 8px; border-radius: 3px; margin-top: 5px;">';
                $html .= '✅ Completed: ' . Carbon::parse($task->end_time)->format('d-m-Y H:i');
                $html .= '</div>';
            } elseif ($status === Task::STATUS_DELIVERING && $task->start_time) {
                $html .= '<div style="font-size: 11px; color: #ffc107; background: #fff9e6; padding: 4px 8px; border-radius: 3px; margin-top: 5px;">';
                $html .= '🚚 Started: ' . Carbon::parse($task->start_time)->format('d-m-Y H:i');
                $html .= '</div>';
            } elseif ($status === Task::STATUS_NEW) {
                $html .= '<div style="font-size: 11px; color: #6c757d; background: #f8f9fa; padding: 4px 8px; border-radius: 3px; margin-top: 5px;">';
                $html .= '⏳ Waiting to start';
                $html .= '</div>';
            } elseif ($status === Task::STATUS_RETURNED) {
                $html .= '<div style="font-size: 11px; color: #dc3545; background: #fde8e8; padding: 4px 8px; border-radius: 3px; margin-top: 5px;">';
                $html .= '↩️ Returned';
                if ($task->return_reason) {
                    $html .= ' - ' . $task->return_reason;
                }
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Add summary
        $totalAssigned = $tasks->sum('this_load');
        
        $html .= '<div style="margin-top: 15px; padding: 12px; background: #e9ecef; border-radius: 6px; border-left: 4px solid #007bff;">';
        $html .= '<strong style="color: #495057; display: block; margin-bottom: 8px;">📊 Summary</strong>';
        $html .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-size: 12px;">';
        $html .= '<div>Total Tasks:</div><div style="text-align: right; font-weight: bold;">' . $tasks->count() . '</div>';
        $html .= '<div>Completed:</div><div style="text-align: right; color: #28a745; font-weight: bold;">' . $completedTasks . '</div>';
        $html .= '<div>In Progress:</div><div style="text-align: right; color: #ffc107; font-weight: bold;">' . $inProgressTasks . '</div>';
        $html .= '<div>New:</div><div style="text-align: right; color: #6c757d; font-weight: bold;">' . $newTasks . '</div>';
        if ($returnedTasks > 0) {
            $html .= '<div>Returned:</div><div style="text-align: right; color: #dc3545; font-weight: bold;">' . $returnedTasks . '</div>';
        }
        $html .= '<div style="grid-column: 1 / -1; border-top: 1px solid #ccc; margin-top: 5px; padding-top: 5px;">';
        $html .= '<div>Total Assigned:</div><div style="text-align: right; color: #007bff; font-weight: bold;">' . $totalAssigned . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return htmlspecialchars($html);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\DeliveryOrder $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DeliveryOrder $model)
    {
        $query = $model->newQuery()
            ->with('customer:id,company')
            ->with('product:id,code,name')
            ->with('company:id,code,name')
            ->with(['tasks' => function($query) {
                $query->with('driver:id,name')
                      ->with('lorry:id,lorryno')
                      ->select('tasks.*');
            }])
            ->select(
                'delivery_orders.id',
                'delivery_orders.dono',
                'delivery_orders.date',
                'delivery_orders.customer_id',
                'delivery_orders.company_id',
                'delivery_orders.place_name',
                'delivery_orders.place_address',
                'delivery_orders.product_id',
                'delivery_orders.total_order',
                'delivery_orders.progress_total',
                'delivery_orders.strength_at',
                'delivery_orders.slump',
                'delivery_orders.status',
            );

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        // Get companies for dropdown
        $companies = Company::orderBy('name')->pluck('name', 'id');
        $companySelectOptions = $companies->map(function($name, $id) {
            return "<option value=\"$name\">$name</option>";
        })->implode('');

        $companySelect = '<select class="border-0" style="width: 100%;"><option value="">All</option>' . $companySelectOptions . '</select>';

        $statusOptions = DeliveryOrder::getStatusOptions();
        $statusSelectOptions = array_map(function($value, $key) {
            return "<option value=\"$key\">$value</option>";
        }, $statusOptions, array_keys($statusOptions));

        $statusSelect = '<select class="border-0" style="width: 100%;"><option value="">All</option>' . implode('', $statusSelectOptions) . '</select>';
        
        $userRole = auth()->user()->roles()->pluck('name')->first();

        $buttons = [
            ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner',],
            ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner',],
            ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner',],
            ['extend' => 'excelHtml5','text'=>'<i class="fa fa-file-excel-o"></i> Excel','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'DO'.date('dmYHis')],
            ['extend' => 'pdfHtml5', 'orientation' => 'landscape', 'pageSize' => 'LEGAL','text'=>'<i class="fa fa-file-pdf-o"></i> PDF','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'DO'.date('dmYHis')],
            ['extend' => 'colvis', 'className' => 'btn btn-default btn-sm no-corner','text'=>'<i class="fa fa-columns"></i> Column',],
            ['extend' => 'pageLength','className' => 'btn btn-default btn-sm no-corner',],
        ];

        // Add create button only for admin users
        if ($userRole === 'normal admin') {
            array_unshift($buttons, ['extend' => 'create', 'className' => 'btn btn-default btn-sm no-corner',]);
        }

        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['width' => '120px', 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[1, 'desc']],
                'lengthMenu' => [[10, 50, 100, 300], ['10 rows', '50 rows', '100 rows', '300 rows']],
                'buttons'   => $buttons,
                'columnDefs' => [
                    [
                        'targets' => 0, // Checkbox column
                        'orderable' => false,
                        'searchable' => false,
                        'render' => 'function(data, type){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
                    ],
                    [
                        'targets' => 11, // Status column (updated from 12 to 11 since we removed one column)
                        'orderable' => true,
                        'searchable' => true
                    ],
                ],
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            if(columns[index].title == \'Status\'){
                                var input = \'' . $statusSelect . '\';
                            }else if(columns[index].title == \'Branch\'){
                                var input = \'' . $companySelect . '\';
                            }else if(columns[index].title == \'Date\'){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search ">\';
                            }else{
                                var input = \'<input type="text" placeholder="Search ">\';
                            }
                            $(input).appendTo($(column.footer()).empty()).on(\'change\', function(){
                                column.search($(this).val(),true,false).draw();
                                ShowLoad();
                            })
                        }
                    });
                    
                    // Initialize popovers
                    $(\'.progress-popover\').popover({
                        placement: "top",
                        trigger: "click",
                        container: "body",
                        html: true
                    });
                    
                    // Close popover when clicking elsewhere
                    $(document).on("click", function(e) {
                        if (!$(e.target).closest(\'.progress-popover\').length && 
                            !$(e.target).closest(\'.popover\').length) {
                            $(\'.progress-popover\').popover("hide");
                            $(\'.progress-popover\').removeClass("active-popover");
                        }
                    });
                }'
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {   
        return [
            'checkbox' => new \Yajra\DataTables\Html\Column([
                'title' => '<input type="checkbox" id="selectallcheckbox">',
                'data' => 'id',
                'name' => 'id', 
                'orderable' => false,
                'searchable' => false
            ]),
            'date' => new \Yajra\DataTables\Html\Column([
                'title' => 'Date',
                'data' => 'date',
                'name' => 'delivery_orders.date',
                'searchable' => true
            ]),
            'dono' => new \Yajra\DataTables\Html\Column([
                'title' => 'Project Number',
                'data' => 'dono',
                'name' => 'dono'
            ]),
            'customer_id' => new \Yajra\DataTables\Html\Column([
                'title' => 'Customer',
                'data' => 'customer.company',
                'name' => 'customer.company'
            ]),
            'company_id' => new \Yajra\DataTables\Html\Column([
                'title' => 'Branch',
                'data' => 'company.name',
                'name' => 'company.name',
                'searchable' => true
            ]),
            'place_name' => new \Yajra\DataTables\Html\Column([
                'title' => 'Destination',
                'data' => 'place_name',
                'name' => 'place_name'
            ]),
            'product_id' => new \Yajra\DataTables\Html\Column([
                'title' => 'Product',
                'data' => 'product.name',
                'name' => 'product.name'
            ]),
            'total_order' => new \Yajra\DataTables\Html\Column([
                'title' => 'Total Order',
                'data' => 'total_order',
                'name' => 'total_order'
            ]),
            'progress_total' => new \Yajra\DataTables\Html\Column([
                'title' => 'Progress Total',
                'data' => 'progress_total',
                'name' => 'progress_total'
            ]),
            'balance' => new \Yajra\DataTables\Html\Column([
                'title' => 'Balance',
                'data' => 'balance',
                'name' => 'balance',
                'orderable' => false,
                'searchable' => false
            ]),
            'status' => new \Yajra\DataTables\Html\Column([
                'title' => 'Status',
                'data' => 'status',
                'name' => 'status',
                'searchable' => true,
                'orderable' => true
            ]),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'delivery_orders_datatable_' . time();
    }
}