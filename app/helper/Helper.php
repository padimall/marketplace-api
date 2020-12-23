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

        $json_data = [
            'to' => $target,
            'data'=> $data
        ];

        $response = $client->request('POST','https://fcm.googleapis.com/fcm/send?',
        [
            'json'=>$json_data
        ]);

        $response = json_decode($response->getBody(),TRUE);
        return $response;
    }
}
