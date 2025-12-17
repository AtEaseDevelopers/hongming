<?php

namespace App\DataTables;

use App\Models\Task;
use App\Models\Company; // ADD THIS
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Carbon\Carbon;

class TaskDataTable extends DataTable
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
            ->addColumn('action', 'tasks.datatables_actions')
            ->addColumn('task_number', function ($model) {                
                return !empty($model->task_number) && $model->task_number != 0 ? $model->task_number : '-';
            })
            ->editColumn('status', function ($model) {
                $statusOptions = Task::getStatusOptions();
                $statusClass = [
                    Task::STATUS_NEW => 'badge badge-secondary',
                    Task::STATUS_DELIVERING => 'badge badge-warning',
                    Task::STATUS_COMPLETED => 'badge badge-success',
                    Task::STATUS_RETURNED => 'badge badge-danger',
                ];

                // Get the raw status value (integer) from attributes
                $status = $model->getStatusValue();
                $statusText = $statusOptions[$status] ?? 'Unknown';

                // Special icon for delivering status
                if ($status === Task::STATUS_DELIVERING) {
                    return '<span class="badge badge-warning">
                        <i class="fa fa-truck"></i> Ongoing
                    </span>';
                }

                return '<span class="' . ($statusClass[$status] ?? 'badge badge-secondary') . '">' . $statusText . '</span>';
            })
            ->editColumn('date', function ($model) {
                return $model->date; // This will use the accessor that formats the date
            })
            ->addColumn('this_load', function ($model) {
                return $model->this_load;
            })
            ->addColumn('countdown_formatted', function ($model) {
                return $model->getCountdownFormatted();
            })
            ->addColumn('start_time_formatted', function ($model) {
                // Only show start time for delivering and completed tasks
                if (in_array($model->getStatusValue(), [Task::STATUS_DELIVERING, Task::STATUS_COMPLETED]) && $model->start_time) {
                    return Carbon::parse($model->start_time)->format('d-m-Y H:i');
                }
                return '-';
            })
            ->addColumn('end_time_formatted', function ($model) {
                // Only show end time for completed tasks
                if ($model->end_time) {
                    return Carbon::parse($model->end_time)->format('d-m-Y H:i');
                }
                return '-';
            })
            ->addColumn('time_taken_formatted', function ($model) {
                // Only show time taken for completed tasks
                if ($model->time_taken){
                    return $model->getTimeTakenFormatted();
                }
                return '-';
            })
            ->addColumn('company_name', function ($model) {
                return $model->company ? $model->company->name : '-';
            })
            ->rawColumns(['status', 'action', 'formatted_id']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Task $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Task $model)
    {
        return $model->newQuery()
            ->with('driver:id,name')
            ->with('lorry:id,lorryno')
            ->with('deliveryOrder:id,dono,customer_id') 
            ->with('deliveryOrder.customer:id,company') 
            ->with('company:id,name,code') 
            ->select('tasks.*');
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

        $statusOptions = Task::getStatusOptions();
        $statusSelectOptions = array_map(function($key, $value) {
            return "<option value=\"$key\">$value</option>";
        }, array_keys($statusOptions), $statusOptions);

        $statusSelect = '<select class="border-0" style="width: 100%;"><option value="">All</option>' . implode('', $statusSelectOptions) . '</select>';

        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['title' => trans('tasks.action'), 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[1, 'desc']],
                'lengthMenu' => [[ 10, 50, 100, 300 ],[ '10 rows', '50 rows', '100 rows', '300 rows' ]],
                'buttons' => [
                    [
                        'extend' => 'create',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-plus"></i> ' . trans('table_buttons.create'),
                    ],
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-print"></i> ' . trans('table_buttons.print'),
                    ],
                    [
                        'extend' => 'reset',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> ' . trans('table_buttons.reset'),
                    ],
                    [
                        'extend' => 'reload',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> ' . trans('table_buttons.reload'),
                    ],
                    [
                        'extend' => 'excelHtml5',
                        'text' => '<i class="fa fa-file-excel-o"></i> ' . trans('table_buttons.excel'),
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'invoice' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> ' . trans('table_buttons.pdf'),
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'invoice' . date('dmYHis')
                    ],
                    [
                        'extend' => 'colvis',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-columns"></i> ' . trans('table_buttons.column')
                    ],
                    [
                        'extend' => 'pageLength',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => trans('table_buttons.show_10_rows')
                    ],
                ],
                'columnDefs' => [
                    [
                        'targets' => 0,
                        'visible' => true,
                        'render' => 'function(data, type){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
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

            'task_number' => new \Yajra\DataTables\Html\Column([
                'title' => 'DO ID',
                'data' => 'task_number',
                'name' => 'id',
                'orderable' => true,
                'searchable' => true
            ]),

            'delivery_order_id' => new \Yajra\DataTables\Html\Column([
                'title' => 'Project Number',
                'data' => 'delivery_order.dono', 
                'name' => 'deliveryOrder.dono',
            ]),
                    
            'date' => new \Yajra\DataTables\Html\Column([
                'title' => trans('tasks.date'),
                'data' => 'date',
                'name' => 'date'
            ]),

            'company_name' => new \Yajra\DataTables\Html\Column([
                'title' => 'Branch',
                'data' => 'company_name',
                'name' => 'company.name',
                'orderable' => true,
                'searchable' => true
            ]),

            'driver_id' => new \Yajra\DataTables\Html\Column([
                'title' => trans('tasks.driver'),
                'data' => 'driver.name',
                'name' => 'driver.name'
            ]),

            'customer_id' => new \Yajra\DataTables\Html\Column([
                'title' => trans('tasks.customer'),
                'data' => 'delivery_order.customer.company', 
                'name' => 'deliveryOrder.customer.company'
            ]),

            'this_load' => new \Yajra\DataTables\Html\Column([
                'title' => 'This Load',
                'data' => 'this_load',
                'name' => 'this_load',
                'orderable' => true,
                'searchable' => false
            ]),

            'countdown_formatted' => new \Yajra\DataTables\Html\Column([
                'title' => 'Countdown',
                'data' => 'countdown_formatted',
                'name' => 'countdown',
                'orderable' => false,
                'searchable' => false
            ]),

            'start_time_formatted' => new \Yajra\DataTables\Html\Column([
                'title' => 'Start Time',
                'data' => 'start_time_formatted',
                'name' => 'start_time',
                'orderable' => true,
                'searchable' => false
            ]),

            'end_time_formatted' => new \Yajra\DataTables\Html\Column([
                'title' => 'End Time',
                'data' => 'end_time_formatted',
                'name' => 'end_time',
                'orderable' => true,
                'searchable' => false
            ]),

            'time_taken_formatted' => new \Yajra\DataTables\Html\Column([
                'title' => 'Time Taken',
                'data' => 'time_taken_formatted',
                'name' => 'time_taken',
                'orderable' => false,
                'searchable' => false
            ]),

            'status' => new \Yajra\DataTables\Html\Column([
                'title' => trans('tasks.status'),
                'data' => 'status',
                'name' => 'tasks.status',
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
        return 'tasks_datatable_' . time();
    }
}