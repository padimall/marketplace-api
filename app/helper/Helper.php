<?php

namespace App\Helper;
use Xendit\Xendit;

class Helper
{
    public $EMPTY_ARRAY = 0;
    public $REQUEST_SUCCESS = 1;
    public $REQUEST_FAILED = 0;
    public $INVOICE_WAITING_FOR_PAYMENT = 0;
    public $INVOICE_WAITING_FOR_RESI = 1;
    public $INVOICE_ON_DELIVERY = 2;
    public $INVOICE_SUCCESS = 3;
    public $INVOICE_CANCELED = 4;
    public $INVOICE_GROUP_WAITING_FOR_PAYMENT = 0;
    public $INVOICE_GROUP_PAID = 1;
    public $INVOICE_GROUP_CANCELED = 2;
    public $AGENT_DEACTIVE = 0;
    public $AGENT_ACTIVE = 1;
    public $IS_NOT_ADMIN = 0;
    public $IS_ADMIN = 1;

    //usage
    //$this->helper->EMPTY_ARRAY



    public function __construct(){
		//CHANGE THIS TO EDIT XENDIT MODE
        Xendit::setApiKey(env('SECRET_API_KEY'));
    }

    public function checkRequestSource($token)
    {
		//CHANGE THIS TO EDIT XENDIT MODE
        if($token == env('CALLBACK_TOKEN')){
            return true;
        }
        return false;
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
