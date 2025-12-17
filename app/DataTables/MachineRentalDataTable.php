<?php

namespace App\DataTables;

use App\Models\MachineRental;
use App\Models\Company; // ADD THIS
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MachineRentalDataTable extends DataTable
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
            ->addColumn('action', 'machine_rental.datatables_actions')
            ->editColumn('date', function ($model) {
                // Format date as dd-mm-yy
                return $model->date->format('d-m-Y');
            })
            ->editColumn('total_amount', function ($model) {
                return 'RM ' . number_format($model->total_amount, 2);
            })
            ->editColumn('issued_by', function ($model) {
                return $model->user->name ?? 'N/A';
            })
            ->rawColumns(['action']); 
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\MachineRental $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MachineRental $model)
    {
        $query = $model->newQuery()
            ->with('customer:id,company')
            ->with('company:id,code,name')
            ->with('user:id,name')
            ->select(
                'machine_rental.id',
                'machine_rental.delivery_order_number',
                'machine_rental.date',
                'machine_rental.customer_id',
                'machine_rental.company_id',
                'machine_rental.issued_by',
                'machine_rental.total_amount',
                'machine_rental.remark',
                'machine_rental.created_at',
                'machine_rental.updated_at'
            )
            ->groupBy(
                'machine_rental.id',
                'machine_rental.delivery_order_number',
                'machine_rental.date',
                'machine_rental.customer_id',
                'machine_rental.company_id',
                'machine_rental.issued_by',
                'machine_rental.total_amount',
                'machine_rental.remark',
                'machine_rental.created_at',
                'machine_rental.updated_at'
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
                'buttons'   => [
                    ['extend' => 'create', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'excelHtml5','text'=>'<i class="fa fa-file-excel-o"></i> Excel','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'MachineRental'.date('dmYHis')],
                    ['extend' => 'pdfHtml5', 'orientation' => 'landscape', 'pageSize' => 'LEGAL','text'=>'<i class="fa fa-file-pdf-o"></i> PDF','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'MachineRental'.date('dmYHis')],
                    ['extend' => 'colvis', 'className' => 'btn btn-default btn-sm no-corner','text'=>'<i class="fa fa-columns"></i> Column',],
                    ['extend' => 'pageLength','className' => 'btn btn-default btn-sm no-corner',],
                ],
                'columnDefs' => [
                    [
                        'targets' => 0, // Checkbox column
                        'orderable' => false,
                        'searchable' => false,
                        'render' => 'function(data, type){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
                    ],
                    [
                        'targets' => 1, // Date column
                        'render' => 'function(data, type){if(type === "display" || type === "filter"){return data ? moment(data).format("DD-MM-YY") : "N/A";}return data;}'
                    ],
                    [
                        'targets' => 6, // Total Amount column
                        'className' => 'dt-body-right'
                    ],
                ],
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            if(columns[index].title == \'Date\'){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search ">\';
                            }
                            else if(columns[index].title == \'Branch\'){
                                var input = \'' . $companySelect . '\';
                            }
                            else{
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
            'date' => new \Yajra\DataTables\Html\Column([
                'title' => 'Date',
                'data' => 'date',
                'name' => 'machine_rental.date',
                'searchable' => true
            ]),
            'delivery_order_number' => new \Yajra\DataTables\Html\Column([
                'title' => 'DO Number',
                'data' => 'delivery_order_number',
                'name' => 'delivery_order_number'
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
            'issued_by' => new \Yajra\DataTables\Html\Column([
                'title' => 'Issued By',
                'data' => 'issued_by',
                'name' => 'user.name'
            ]),
            'total_amount' => new \Yajra\DataTables\Html\Column([
                'title' => 'Total Amount',
                'data' => 'total_amount',
                'name' => 'total_amount'
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
        return 'machine_rental_datatable_' . time();
    }
}