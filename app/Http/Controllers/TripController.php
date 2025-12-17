<?php

namespace App\Http\Controllers;

use App\DataTables\TripDataTable;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Repositories\TripRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request; 
use App\Models\Trip; 

class TripController extends AppBaseController
{
    /** @var TripRepository $tripRepository*/
    private $tripRepository;

    public function __construct(TripRepository $tripRepo)
    {
        $this->tripRepository = $tripRepo;
    }

    /**
     * Display a listing of the Trip.
     *
     * @param TripDataTable $tripDataTable
     *
     * @return Response
     */
    public function index(TripDataTable $tripDataTable)
    {
        return $tripDataTable->render('trips.index');
    }

    public function endTrip(Request $request)
    {
        try {
            $tripId = $request->input('trip_id');
            
            if (!$tripId) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Trip ID is required'], 400);
                }
                Flash::error('Trip ID is required');
                return redirect(route('trips.index'));
            }

            $trip = Trip::find($tripId);

            if (empty($trip)) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Trip not found'], 404);
                }
                Flash::error('Trip not found');
                return redirect(route('trips.index'));
            }

            // Check if trip is already ended
            if ($trip->isEnded()) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'This trip has already been ended'], 422);
                }
                Flash::warning('This trip has already been ended');
                return redirect(route('trips.index'));
            }

            // Check if trip is a start trip
            if ($trip->type != 1) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Only start trips can be ended'], 422);
                }
                Flash::error('Only start trips can be ended');
                return redirect(route('trips.index'));
            }

            // Create end trip record
            Trip::create([
                'date' => now(),
                'driver_id' => $trip->driver_id,
                'lorry_id' => $trip->lorry_id,
                'task_id' => $trip->task_id,
                'type' => 0, // End trip
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Trip ended successfully']);
            }

            Flash::success('Trip ended successfully');
            return redirect(route('trips.index'));

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error ending trip: ' . $e->getMessage()], 500);
            }
            Flash::error('Error ending trip: ' . $e->getMessage());
            return redirect(route('trips.index'));
        }
    }

}
