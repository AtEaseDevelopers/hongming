<?php

namespace App\DataTables;

use App\Models\DriverCheckIn;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Illuminate\Support\Facades\Crypt;

class CheckInDataTable extends DataTable
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
            return '<div class="btn-group">
                        <button type="button" class="btn btn-ghost-success view-checkin" 
                                data-id="' . $row->id . '" 
                                data-toggle="modal" data-target="#checkinModal">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>';
        })
        ->addColumn('location_name', function ($row) {
            if ($row->latitude && $row->longitude) {
                // Get short location name
                $locationName = $this->getShortLocationName($row->latitude, $row->longitude);
                return $locationName ?: 'Location not available';
            }
            return '<span class="text-muted">No location</span>';
        })
        ->editColumn('type', function ($row) {
            return $row->type == DriverCheckIn::TYPE_CHECK_IN ? 
                '<span class="badge badge-success">Check In</span>' : 
                '<span class="badge badge-warning">Check Out</span>';
        })
        ->editColumn('check_time', function ($row) {
            return $row->check_time ? $row->check_time->format('d-m-Y H:i:s') : '-';
        })
        ->filterColumn('check_time', function ($query, $keyword) {
            // Custom filter for date search
            if (!empty($keyword)) {
                // Remove any spaces and convert search term
                $searchTerm = trim($keyword);
                
                // If search term contains date only (without time)
                if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $searchTerm)) {
                    // Search for records on that specific date
                    $query->whereDate('check_time', '=', date('Y-m-d', strtotime($searchTerm)));
                } 
                // If search term contains date and time
                elseif (preg_match('/^\d{1,2}-\d{1,2}-\d{4} \d{1,2}:\d{1,2}:\d{1,2}$/', $searchTerm)) {
                    // Exact datetime match
                    $query->where('check_time', '=', date('Y-m-d H:i:s', strtotime($searchTerm)));
                }
                // If search term contains partial date/time
                else {
                    // Use LIKE for partial matching
                    $query->where('check_time', 'LIKE', "%{$searchTerm}%");
                }
            }
        })
        ->rawColumns(['action', 'type', 'location_name']);
    }

    /**
     * Get short location name from coordinates
     */
    private function getShortLocationName($latitude, $longitude)
    {
        try {
            $apiKey = config('app.google_api');
            
            if (!$apiKey) {
                return 'API key not configured';
            }
            
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                return 'Failed to get location';
            }
            
            $data = json_decode($response, true);
            
            if ($data['status'] === 'OK' && !empty($data['results'][0])) {
                $result = $data['results'][0];
                
                // Try to get establishment name first (like "吉隆坡塔")
                foreach ($result['address_components'] as $component) {
                    if (in_array('establishment', $component['types']) || 
                        in_array('point_of_interest', $component['types']) ||
                        in_array('tourist_attraction', $component['types'])) {
                        return $component['long_name']; // or short_name
                    }
                }
                
                // If no establishment, get route name
                foreach ($result['address_components'] as $component) {
                    if (in_array('route', $component['types'])) {
                        return $component['long_name'];
                    }
                }
                
                // If no route, get locality
                foreach ($result['address_components'] as $component) {
                    if (in_array('locality', $component['types'])) {
                        return $component['long_name'];
                    }
                }
                
                // Fallback: get the first part of formatted address
                $formattedAddress = $result['formatted_address'];
                $parts = explode(',', $formattedAddress);
                return trim($parts[0]);
            }
            
            return 'Location not found';
            
        } catch (\Exception $e) {
            \Log::error('Geocoding error: ' . $e->getMessage());
            return 'Error getting location';
        }
    }

    /**
     * Get full address from coordinates (for modal)
     */
    private function getFullAddress($latitude, $longitude)
    {
        try {
            $apiKey = config('app.google_api');
            
            if (!$apiKey) {
                return 'API key not configured';
            }
            
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                return 'Failed to get address';
            }
            
            $data = json_decode($response, true);
            
            if ($data['status'] === 'OK' && !empty($data['results'][0])) {
                return $data['results'][0]['formatted_address'];
            }
            
            return 'Address not found';
            
        } catch (\Exception $e) {
            \Log::error('Geocoding error: ' . $e->getMessage());
            return 'Error getting address';
        }
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\DriverCheckIn $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DriverCheckIn $model)
    {
        return $model->newQuery()
            ->with('driver:id,name')
            ->select('driver_check_ins.*')
            ->orderBy('check_time', 'desc');
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
            ->addAction(['title' => 'Action', 'printable' => false, 'width' => '80px'])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[1, 'desc']],
                'lengthMenu' => [[10, 50, 100, 300], ['10 rows', '50 rows', '100 rows', '300 rows']],
                'buttons' => [
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-print"></i> Print',
                    ],
                    [
                        'extend' => 'reset',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> Reset',
                    ],
                    [
                        'extend' => 'reload',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> Reload',
                    ],
                    [
                        'extend' => 'excelHtml5',
                        'text' => '<i class="fa fa-file-excel-o"></i> Excel',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'driver_checkins_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> PDF',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'driver_checkins_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'colvis',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-columns"></i> Column'
                    ],
                    [
                        'extend' => 'pageLength',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => 'Show 10 rows'
                    ],
                ],
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            if(columns[index].title == \'Type\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="check_in">Check In</option><option value="check_out">Check Out</option></select>\';
                            }else if(columns[index].title == \'Check Time\'){
                                // Date search input with better placeholder
                                var input = \'<input type="text" placeholder="DD-MM-YYYY or DD-MM-YYYY HH:MM:SS" style="width: 100%; padding: 3px; border: 1px solid #ccc; font-size: 12px;">\';
                            }else{
                                var input = \'<input type="text" placeholder="Search" style="width: 100%; padding: 3px; border: 1px solid #ccc;">\';
                            }
                            $(input).appendTo($(column.footer()).empty()).on(\'change keyup\', function(){
                                column.search($(this).val()).draw();
                                if(typeof ShowLoad !== "undefined") ShowLoad();
                            })
                        }
                    });
                }',
                'drawCallback' => 'function(){
                    if(typeof HideLoad !== "undefined") HideLoad();
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
                'title' => 'ID',
                'data' => 'id',
                'name' => 'id',
                'width' => '50px' 
            ]),

            'check_time' => new \Yajra\DataTables\Html\Column([
                'title' => 'Check Time',
                'data' => 'check_time',
                'name' => 'check_time',
                'searchable' => true,
                'width' => '180px'
            ]),

            'driver_id' => new \Yajra\DataTables\Html\Column([
                'title' => 'Driver',
                'data' => 'driver.name',
                'name' => 'driver.name',
                'width' => '120px'
            ]),

            'type' => new \Yajra\DataTables\Html\Column([
                'title' => 'Type',
                'data' => 'type',
                'name' => 'type',
                'searchable' => true,
                'width' => '60px'
            ]),

            'location_name' => new \Yajra\DataTables\Html\Column([
                'title' => 'Location',
                'data' => 'location_name',
                'name' => 'location_name',
                'orderable' => false,
                'searchable' => false,
                'width' => '150px'
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
        return 'driver_checkins_datatable_' . time();
    }
}