<?php

use Google\Auth\Credentials\ServiceAccountJwtAccessCredentials;

class FCMService
{
    protected $projectId = 'selforder-c7da0';
    protected $credentialPath;

    public function __construct()
    {
        $this->credentialPath = BASE_PATH . '/selforder-c7da0-firebase-adminsdk-fbsvc-acbc1b9591.json';
    }

    protected function getAccessToken()
    {
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new ServiceAccountJwtAccessCredentials($this->credentialPath, $scopes);
        $authToken = $credentials->fetchAuthToken();
        return $authToken['access_token'] ?? null;
    }

    public function sendToDevices(array $tokens, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken)
            return false;

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        foreach ($tokens as $token) {
            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'data' => array_map('strval', $data)
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer {$accessToken}",
                "Content-Type: application/json; charset=UTF-8"
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);
            if ($httpCode !== 200) {
                $status = $responseData['error']['status'] ?? '';
                error_log("FCM send failed for {$token}: {$status}");
            }
        }

        return true;
    }
}