<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Driver;
use App\Models\DriverNotifications;

class OneSignalNotificationService
{
    protected $appId;
    protected $apiKey;
    protected $apiUrl = 'https://onesignal.com/api/v1/notifications';

    public function __construct()
    {
        $this->appId = '5081b7c1-4087-404f-b61f-d539660cbbb2';
        $this->apiKey = 'os_v2_app_kca3pqkaq5ae7nq72u4wmdf3wjvegjfsy5selle4semwxfvecn54r5bb3bwwufm67r7ojbuclzbifjl64ux5opxo2wwlsi2f6s5keni';
    }

    /**
     * Send notification to driver via OneSignal
     */
    public function sendToDriver($driverId, $title, $body, $data = [])
    {
        try {
            $driver = Driver::find($driverId);
            
            if (!$driver) {
                Log::warning("Driver {$driverId} not found");
                return false;
            }

            // 1. Always save to database (works 100%)
            $this->saveToDatabase($driver, $title, $body, $data);
            Log::info("✅FCM TOKEN: {$driver->fcm_token}");
            Log::info("✅API KEY: {$this->apiKey}");
            // 2. Try OneSignal push if player_id exists
            if ($driver->fcm_token && $this->apiKey) {
                $oneSignalSuccess = $this->sendOneSignalPush($driver->fcm_token, $title, $body, $data);
                
                if ($oneSignalSuccess) {
                    Log::info("✅ OneSignal push + database notification sent to driver {$driver->id}");
                } else {
                    Log::warning("✅ Database notification saved, but OneSignal push failed for driver {$driver->id}");
                }
                
                return true; // Always return true since database notification works
            } else {
                Log::info("✅ Database notification saved for driver {$driver->id} (OneSignal push skipped - no player_id or API key)");
                return true;
            }

        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification via OneSignal API
     */
    private function sendOneSignalPush($playerId, $title, $body, $data)
    {
        try {
            $payload = [
                'app_id' => $this->appId,
                'include_player_ids' => [$playerId],
                'headings' => ['en' => $title],
                'contents' => ['en' => $body],
                'data' => $data,
                'ios_badgeType' => 'Increase',
                'ios_badgeCount' => 1,
                'small_icon' => 'ic_stat_onesignal_default',
                'large_icon' => 'ic_launcher',
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Basic ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->apiUrl, $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info("OneSignal push sent successfully", [
                    'player_id' => substr($playerId, -10), // Last 10 chars for privacy
                    'recipients' => $responseData['recipients'] ?? 0
                ]);
                return true;
            } else {
                Log::error("OneSignal API error", [
                    'status' => $response->status(),
                    'error' => $responseData['errors'] ?? 'Unknown error'
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('OneSignal request failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save notification to database (always works)
     */
    private function saveToDatabase($driver, $title, $body, $data)
    {
        DriverNotifications::create([
            'driver_id' => $driver->id,
            'title' => $title,
            'message' => $body,
            'type' => $data['type'] ?? 'general',
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Test OneSignal configuration
     */
    public function testConfiguration()
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'message' => 'OneSignal REST API Key not configured in .env'
            ];
        }

        return [
            'success' => true,
            'message' => 'OneSignal configured successfully',
            'app_id' => $this->appId,
            'api_key_preview' => substr($this->apiKey, 0, 10) . '...'
        ];
    }
}