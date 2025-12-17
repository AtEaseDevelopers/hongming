<?php

namespace App\Http\Controllers;

use App\DataTables\CheckInDataTable;
use App\Http\Requests;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DriverCheckIn;

class CheckInController extends AppBaseController
{
    /**
     *
     * @param CheckInDataTable $checkInDataTable
     *
     * @return Response
     */
    public function index(CheckInDataTable $checkInDataTable)
    {
        return $checkInDataTable->render('checkins.index');
    }

    /**
     * Get check-in details for modal
     */
    public function getDetails($id)
    {
        try {
            $checkin = DriverCheckIn::with('driver')->findOrFail($id);
            
            $fullAddress = 'No location data';
            if ($checkin->latitude && $checkin->longitude) {
                // You can call the getFullAddress method here or reuse the logic
                $fullAddress = $this->getFullAddress($checkin->latitude, $checkin->longitude);
            }

            return view('checkins.details-modal', compact('checkin', 'fullAddress'));
            
        } catch (\Exception $e) {
            return '<div class="alert alert-danger">Check-in not found</div>';
        }
    }

    /**
     * Get full address from coordinates
     */
    private function getFullAddress($latitude, $longitude)
    {
        // Same implementation as in DataTable
        try {
            $apiKey = config('app.google_api');
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";
            
            $context = stream_context_create([
                'http' => ['timeout' => 5, 'ignore_errors' => true]
            ]);
            
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) return 'Failed to get address';
            
            $data = json_decode($response, true);
            
            if ($data['status'] === 'OK' && !empty($data['results'][0])) {
                return $data['results'][0]['formatted_address'];
            }
            
            return 'Address not found';
            
        } catch (\Exception $e) {
            return 'Error getting address';
        }
    }


}
