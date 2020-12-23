<?php

namespace App\Helper;

class Helper
{
    public function sendMobileNotification($target,$data)
    {
        $token = env('NOTIF_API_KEY');
        $to = $token.'-'.$token;
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST','https://fcm.googleapis.com/fcm/send?',
        [
            'form_params' => [
                'to' => $to,
                'data' => $data
            ]
        ]);
        $response = json_decode($response->getBody(),TRUE);
        return $response;
    }
}
