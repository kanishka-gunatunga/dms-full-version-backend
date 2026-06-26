<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class ZohoService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get Access Token from cache or refresh it if expired
     */
    public function getAccessToken()
    {
        if (Cache::has('zoho_access_token')) {
            return Cache::get('zoho_access_token');
        }

        $response = $this->client->post('https://accounts.zoho.com/oauth/v2/token', [
            'form_params' => [
                'refresh_token' => config('zoho.refresh_token'),
                'client_id' => config('zoho.client_id'),
                'client_secret' => config('zoho.client_secret'),
                'grant_type' => 'refresh_token',
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $accessToken = $data['access_token'];

        // store in cache for 1 hour (Zoho token expires in 1 hour)
        Cache::put('zoho_access_token', $accessToken, 3600);

        return $accessToken;
    }

    /**
     * Create a ServiceDesk request with attachments
     */
    public function createRequest(array $requestData, array $attachments = [])
    {
        $accessToken = $this->getAccessToken();

        $multipart = [
            [
                'name' => 'input_data',
                'contents' => json_encode(['request' => $requestData]),
            ]
        ];

        // foreach ($attachments as $filePath) {
        //     $multipart[] = [
        //         'name' => 'attachments[]',
        //         'contents' => fopen($filePath, 'r'),
        //         'filename' => basename($filePath),
        //     ];
        // }

        $response = $this->client->post(config('zoho.api_base_url') . '/requests', [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Accept' => 'application/vnd.manageengine.sdp.v3+json',
            ],
            'multipart' => $multipart,
        ]);

        return json_decode($response->getBody(), true);
    }
}
