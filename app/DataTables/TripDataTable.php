<?php

namespace App\DataTables;

use App\Models\Trip;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class TripDataTable extends DataTable
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

        return $dataTable->addColumn('action', function ($row) {
            if ($row->type == 2) {
                return '<div class="btn-group">
                            <a href="' . route('trips.show', Crypt::encrypt($row->id)) . '" class="btn btn-ghost-success">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>';
            } else {
                return '';
            }
        })
        ->addColumn('end_trip_action', function ($row) {
            // Show end trip button only for Start Trip (type 1) and if not already ended
            if ($row->type == 1 && !$row->isEnded()) {
                return '<div class="btn-group">
                            <button type="button" class="btn btn-ghost-danger btn-end-trip" 
                                    data-trip-id="' . $row->id . '" 
                                    data-driver-name="' . ($row->driver->name ?? 'N/A') . '"
                                    data-lorry-no="' . ($row->lorry->lorryno ?? 'N/A') . '">
                                <i class="fa fa-flag-checkered"></i> End Trip
                            </button>
                        </div>';
            } else {
                return '<span class="text-muted">Completed</span>';
            }
        })
        ->editColumn('type', function ($row) {
            if ($row->type == 1) {
                return '<span class="badge badge-primary">Start Trip</span>';
            } else {
                return '<span class="badge badge-success">End Trip</span>';
            }
        })
        ->editColumn('date', function ($row) {
            return Carbon::parse($row->date)->format('d-m-Y H:i');
        })
        ->rawColumns(['action', 'end_trip_action', 'type']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Trip $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Trip $model)
    {
        return $model->newQuery()
            ->with('driver:id,name')
            ->with('lorry:id,lorryno')
            ->select('trips.*');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => true,
                'order'     => [[1, 'desc']],
                'lengthMenu' => [[10, 50, 100, 300], ['10 rows', '50 rows', '100 rows', '300 rows']],
                'buttons' => [
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
                        'filename' => 'trips_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> ' . trans('table_buttons.pdf'),
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'trips_' . date('dmYHis')
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
                        'targets' => 4, // Type column (index 4)
                        'render' => 'function(data, type, row, meta){ 
                            if(type === "display") {
                                return data; // Return the HTML badge
                            }
                            // For sorting and filtering, return numeric value
                            return row.type; 
                        }'
                    ],
                    [
                        'targets' => 5, // End Trip Action column
                        'searchable' => false,
                        'orderable' => false,
                    ],
                ],
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            if(columns[index].title == "Type"){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="1">Start Trip</option><option value="2">End Trip</option></select>\';
                                $(input).appendTo($(column.footer()).empty())
                                .on(\'change\', function(){
                                    column.search($(this).val()).draw();
                                    ShowLoad();
                                });
                            }else if(columns[index].title == "Date"){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search Date">\';
                                $(input).appendTo($(column.footer()).empty())
                                .on(\'keyup change\', function(){
                                    column.search($(this).val()).draw();
                                    ShowLoad();
                                });
                            }else{
                                var input = \'<input type="text" placeholder="Search">\';
                                $(input).appendTo($(column.footer()).empty())
                                .on(\'keyup change\', function(){
                                    column.search($(this).val()).draw();
                                    ShowLoad();
                                });
                            }
                        }
                    });
                    HideLoad();
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
            'id' => new \Yajra\DataTables\Html\Column([
                'title' => trans('trips.trip_id'),
                'data' => 'id',
                'name' => 'id',
                'width' => '50px',
                'searchable' => false
            ]),

            'date' => new \Yajra\DataTables\Html\Column([
                'title' => 'Date',
                'data' => 'date',
                'name' => 'date',
                'searchable' => true,
                'width' => '100px'
            ]),

            'driver_id' => new \Yajra\DataTables\Html\Column([
                'title' => trans('trips.driver'),
                'data' => 'driver.name',
                'name' => 'driver.name',
                'searchable' => true,
                'width' => '120px'
            ]),

            'lorry_id' => new \Yajra\DataTables\Html\Column([
                'title' => trans('trips.lorry'),
                'data' => 'lorry.lorryno',
                'name' => 'lorry.lorryno',
                'searchable' => true,
                'width' => '100px'
            ]),

            'type' => new \Yajra\DataTables\Html\Column([
                'title' => trans('trips.type'),
                'data' => 'type',
                'name' => 'type',
                'searchable' => true,
                'orderable' => true,
                'width' => '100px'
            ]),

            'end_trip_action' => new \Yajra\DataTables\Html\Column([
                'title' => 'Action',
                'data' => 'end_trip_action',
                'name' => 'end_trip_action',
                'searchable' => false,
                'orderable' => false,
                'width' => '100px',
                'exportable' => false,
                'printable' => false,
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
        return 'trips_datatable_' . time();
    }
}