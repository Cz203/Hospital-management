<?php

/**
 * Zoom Service
 * Tạo meeting links sử dụng Zoom API
 */

require_once 'config/zoom.php';
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ZoomService
{
    private $client;
    private $accountId;
    private $clientId;
    private $clientSecret;

    public function __construct()
    {
        $this->client = new Client();
        $this->accountId = ZoomConfig::getAccountId();
        $this->clientId = ZoomConfig::getClientId();
        $this->clientSecret = ZoomConfig::getClientSecret();
    }

    /**
     * Kiểm tra service có sẵn sàng không
     */
    public function isReady()
    {
        return ZoomConfig::isConfigured();
    }

    /**
     * Tạo OAuth token cho Zoom API
     */
    private function generateOAuthToken()
    {
        try {
            $response = $this->client->post('https://zoom.us/oauth/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'grant_type' => 'account_credentials',
                    'account_id' => $this->accountId
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['access_token'] ?? null;
        } catch (Exception $e) {
            error_log("Error generating OAuth token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo Zoom meeting link
     */
    public function createMeetingLink($appointmentData)
    {
        try {
            error_log("ZoomService - Creating meeting for appointment: " . $appointmentData['appointment_id']);

            if (!$this->isReady()) {
                return [
                    'success' => false,
                    'error' => 'Zoom API chưa được cấu hình. Vui lòng cập nhật ZOOM_ACCOUNT_ID, ZOOM_CLIENT_ID và ZOOM_CLIENT_SECRET trong file .env'
                ];
            }

            // Tạo OAuth token
            $token = $this->generateOAuthToken();

            if (!$token) {
                return [
                    'success' => false,
                    'error' => 'Không thể tạo OAuth token. Kiểm tra Account ID, Client ID và Client Secret.'
                ];
            }

            // Chuẩn bị thời gian meeting
            $startTime = $this->formatDateTime($appointmentData['ngay_hen'], $appointmentData['gio_hen']);
            $endTime = $this->calculateEndTime($startTime, ZoomConfig::MEETING_CONFIG['duration']);

            // Chuẩn bị dữ liệu meeting
            $meetingData = [
                'topic' => 'Tư vấn trực tuyến - ' . $appointmentData['doctor_name'],
                'type' => ZoomConfig::MEETING_CONFIG['type'],
                'start_time' => $startTime,
                'duration' => ZoomConfig::MEETING_CONFIG['duration'],
                'timezone' => ZoomConfig::MEETING_CONFIG['timezone'],
                'agenda' => 'Cuộc hẹn tư vấn trực tuyến với bác sĩ ' . $appointmentData['doctor_name'] .
                    "\nLý do: " . ($appointmentData['ly_do'] ?? 'Không có') .
                    "\nBác sĩ: " . $appointmentData['doctor_email'] .
                    "\nBệnh nhân: " . $appointmentData['patient_email'],
                'settings' => ZoomConfig::MEETING_CONFIG['settings']
            ];

            // Meeting data prepared successfully

            // Gọi Zoom API
            $response = $this->client->post(ZoomConfig::CREATE_MEETING_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'json' => $meetingData
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['join_url'])) {
                error_log("ZoomService - Meeting created successfully");

                return [
                    'success' => true,
                    'meet_link' => $responseData['join_url'],
                    'meeting_id' => $responseData['id'],
                    'password' => $responseData['password'] ?? null
                ];
            } else {
                error_log("ZoomService - Failed to create meeting");
                return [
                    'success' => false,
                    'error' => 'Không thể tạo meeting. Vui lòng thử lại sau.'
                ];
            }
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $errorMessage .= " - Response: " . $responseBody;
            }
            error_log("ZoomService - Request error: " . $errorMessage);
            return [
                'success' => false,
                'error' => 'Lỗi kết nối Zoom API. Vui lòng thử lại sau.'
            ];
        } catch (Exception $e) {
            error_log("ZoomService - General error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi tạo meeting. Vui lòng thử lại sau.'
            ];
        }
    }

    /**
     * Format datetime cho Zoom API
     */
    private function formatDateTime($date, $time)
    {
        // Đảm bảo time có format HH:MM:SS
        if (strlen($time) == 5) { // HH:MM
            $time .= ':00';
        }

        $datetime = $date . 'T' . $time;
        return date('c', strtotime($datetime)); // ISO 8601 format
    }

    /**
     * Tính thời gian kết thúc
     */
    private function calculateEndTime($startTime, $durationMinutes)
    {
        $startTimestamp = strtotime($startTime);
        $endTimestamp = $startTimestamp + ($durationMinutes * 60);
        return date('c', $endTimestamp);
    }
}