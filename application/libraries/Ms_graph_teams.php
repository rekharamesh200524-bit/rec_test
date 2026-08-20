<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ms_graph_teams
{
    protected $CI;
    protected $tenant_id;
    protected $client_id;
    protected $client_secret;
    protected $organizer_user_id;

    public function __construct($params = array())
    {
        $this->CI =& get_instance();
        
        $this->tenant_id         = !empty($params['tenant_id'])         ? $params['tenant_id']         : $this->CI->config->item('ms_tenant_id');
        $this->client_id         = !empty($params['client_id'])         ? $params['client_id']         : $this->CI->config->item('ms_client_id');
        $this->client_secret     = !empty($params['client_secret'])     ? $params['client_secret']     : $this->CI->config->item('ms_client_secret');
        $this->organizer_user_id = !empty($params['organizer_user_id']) ? $params['organizer_user_id'] : $this->CI->config->item('ms_organizer_user_id');

        if (empty($this->tenant_id))         $this->tenant_id         = getenv('MS_TENANT_ID') ?: '';
        if (empty($this->client_id))         $this->client_id         = getenv('MS_CLIENT_ID') ?: '';
        if (empty($this->client_secret))     $this->client_secret     = getenv('MS_CLIENT_SECRET') ?: '';
        if (empty($this->organizer_user_id)) $this->organizer_user_id = getenv('MS_ORGANIZER_USER_ID') ?: '';
    }

    /**
     * Get OAuth 2.0 Access Token from Microsoft Entra ID / Azure AD
     */
    public function getAccessToken()
    {
        if (empty($this->tenant_id) || empty($this->client_id) || empty($this->client_secret)) {
            $this->logError("Missing Microsoft Graph credentials in configuration.");
            return null;
        }

        $tokenUrl = "https://login.microsoftonline.com/" . trim($this->tenant_id) . "/oauth2/v2.0/token";
        
        $postData = http_build_query([
            'client_id'     => trim($this->client_id),
            'client_secret' => trim($this->client_secret),
            'scope'         => 'https://graph.microsoft.com/.default',
            'grant_type'    => 'client_credentials'
        ]);

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $this->logError("cURL token error: " . $curlErr);
            return null;
        }

        $json = json_decode($response, true);
        if ($httpCode === 200 && !empty($json['access_token'])) {
            return $json['access_token'];
        }

        $this->logError("Failed to fetch access token. HTTP $httpCode: " . ($json['error_description'] ?? $response));
        return null;
    }

    /**
     * Create a Microsoft Teams Meeting via Microsoft Graph API
     *
     * @param string $subject Subject line for the meeting (e.g. "Interview - Candidate Name")
     * @param string $startDateTime Local date/time string (e.g. "2026-08-25 10:00:00")
     * @param int $durationMinutes Duration in minutes (default 45)
     * @param string $timezone Timezone identifier (default Asia/Kolkata)
     * @return array ['status' => bool, 'joinWebUrl' => string|null, 'message' => string]
     */
    public function createTeamsMeeting($subject, $startDateTime, $durationMinutes = 45, $timezone = 'Asia/Kolkata')
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'status'  => false,
                'message' => 'Unable to create Microsoft Teams meeting. Please check Microsoft credentials.'
            ];
        }

        try {
            $tz = new DateTimeZone($timezone ? $timezone : date_default_timezone_get());
            $dtStart = new DateTime($startDateTime, $tz);
            $dtStart->setTimezone(new DateTimeZone('UTC'));
            $startUtcIso = $dtStart->format('Y-m-d\TH:i:s\Z');

            $dtEnd = clone $dtStart;
            $dtEnd->modify("+{$durationMinutes} minutes");
            $endUtcIso = $dtEnd->format('Y-m-d\TH:i:s\Z');
        } catch (Exception $e) {
            $this->logError("DateTime parsing exception: " . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'Invalid scheduled interview date/time format.'
            ];
        }

        $organizerId = !empty($this->organizer_user_id) ? trim($this->organizer_user_id) : 'me';
        $endpointUrl = "https://graph.microsoft.com/v1.0/users/" . rawurlencode($organizerId) . "/onlineMeetings";

        $payload = [
            'startDateTime' => $startUtcIso,
            'endDateTime'   => $endUtcIso,
            'subject'       => $subject
        ];

        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $this->logError("cURL onlineMeetings error: " . $curlErr);
            return [
                'status'  => false,
                'message' => 'Unable to create Microsoft Teams meeting. Please try again.'
            ];
        }

        $resData = json_decode($response, true);

        if (($httpCode === 200 || $httpCode === 201) && !empty($resData['joinWebUrl'])) {
            return [
                'status'     => true,
                'joinWebUrl' => $resData['joinWebUrl'],
                'id'         => $resData['id'] ?? null
            ];
        }

        $errorMsg = $resData['error']['message'] ?? $response;
        $this->logError("Graph API onlineMeetings error (HTTP $httpCode): " . $errorMsg);

        return [
            'status'  => false,
            'message' => 'Unable to create Microsoft Teams meeting. Please try again.'
        ];
    }

    protected function logError($message)
    {
        $logFile = FCPATH . 'interview_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $formatted = "[$timestamp] [Ms_graph_teams ERROR] " . $message . "\n";
        @file_put_contents($logFile, $formatted, FILE_APPEND);
        log_message('error', '[Ms_graph_teams] ' . $message);
    }
}
