<?php

namespace App\Helper;

class Helper
{
    public function sendMobileNotification($target,$data)
    {
        $token = env('NOTIF_API_KEY');
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization'=>'key='.$token
            ]
        ]);

        $response = $client->request('POST','https://fcm.googleapis.com/fcm/send?',
        [
            'form_params' => [
                'to' => $target,
                'data' => $data
            ]
        ]);
        $response = json_decode($response->getBody(),TRUE);
        return $response;
    }
}
