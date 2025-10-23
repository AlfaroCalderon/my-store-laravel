<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class WebHookService{
    private const WELCOME_EMAIL_WEBHOOK = 'https://javierrjca.app.n8n.cloud/webhook/laravel-mailing';

    public function sendWelcomeEmail(array $userData):bool{
        try {
            $payload = [
                'type' => $userData['type'],
                'user' => [
                    'name' => $userData['name'],
                    'lastname' => $userData['lastname'],
                    'email' => $userData['email']
                ]
            ];

            $response = Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'API-KEY' => 'C976E26DB7FC3D2E92395B174C6FB'
            ])->post(self::WELCOME_EMAIL_WEBHOOK, $payload);

            if($response->successful()){
                return true;
            }

            return false;

        } catch (\Exception $error) {
            return false;
        }
    }

    public function sendLoginSession(array $userData):bool{
        try {

            $payload = [
                'type' => $userData['type'],
                'user' => [
                    'name' => $userData['name'],
                    'lastname' => $userData['lastname'],
                    'email' => $userData['email']
                ],
                'geolocation' => [
                    'country' => $userData['country'] ?? 'Unknown',
                    'city' =>  $userData['city'] ?? 'Unknown'
                ]
            ];

            $response = Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'API-KEY' => 'C976E26DB7FC3D2E92395B174C6FB'
            ])->post(self::WELCOME_EMAIL_WEBHOOK, $payload );

            if($response->successful()){
                return true;
            }

            return false;
        } catch (\Exception $error) {
            return false;
        }
    }

}



?>
