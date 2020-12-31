<?php

namespace App\Helper;
use Xendit\Xendit;

class Helper
{
    public function __construct(){
        Xendit::setApiKey(env('SECRET_API_KEY'));
    }

    public function checkRequestSource($token)
    {
        if($token == env('CALLBACK_TOKEN_DEV')){
            return true;
        }
        return false;
    }

    public function sendMobileNotification($target,$data)
    {
        $token = env('NOTIF_API_KEY_DEV');
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

    public function createEwalletPayment($params){
        return \Xendit\EWallets::create($params);
    }

    public function createRetailPayment($params){
        return \Xendit\Retail::create($params);
    }

    public function retrieveInvoice($id){
        return \Xendit\Invoice::retrieve($id);
    }

    public function retrieveEwalletPayment($target,$type){
        return \Xendit\EWallets::getPaymentStatus($target, $type);
    }

    public function retrieveRetailPayment($id){
        return \Xendit\Retail::retrieve($id);
    }

    public function updateVA($id,$updateParams){
        return \Xendit\VirtualAccounts::update($id, $updateParams);
    }
}
