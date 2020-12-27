<?php

namespace App\Helper;
use Xendit\Xendit;

class Helper
{
    public function __construct(){
        Xendit::setApiKey(env('SECRET_API_KEY_DEV'));
    }

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

    public function createFVA($params){
        return \Xendit\VirtualAccounts::create($params);
    }

    public function createInvoice($params){
        return \Xendit\Invoice::create($params);
    }
}
