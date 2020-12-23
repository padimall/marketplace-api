<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Agent;
use App\Supplier;
use App\Product;
use App\Cart;
use App\Payment;
use App\Invoices_group_log;
use App\Invoices_group;
use App\Invoices_product;
use App\Invoices_logistic;
use App\Invoices_log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Xendit\Xendit;
use GuzzleHttp\Client;
use App\Helper\Helper;

class InvoiceController extends Controller
{
    public function callback(Request $request)
    {
        $data = Invoices_group::find($request['external_id']);

        if(is_null($data))
        {
            return response()->json([
                'status' => 0,
                'message' => 'External id not found'
            ],200);
        }


        if($request['status'] == 'PAID' || $request['status'] == 'SETTLED')
        {
            $status = 1;
            $data->status = $status;
            if($data->save())
            {
                $up_data = DB::table('invoices')
                            ->where('invoices_group_id',$data->id)
                            ->update(['status' => $status]);

                if($up_data)
                {
                    $list_inv = DB::table('invoices')
                            ->join('users','users.id','=','invoices.user_id')
                            ->where('invoices.invoices_group_id',$data->id)
                            ->select('invoices.id','users.device_id','invoices.agent_id')
                            ->get();

                    $device_id = $list_inv[0]->device_id;
                    $list_agent = array();

                    for($i=0; $i<sizeof($list_inv); $i++){
                        array_push($list_agent,$list_inv[$i]->agent_id);
                        $log_inv = array(
                            'invoice_id' => $list_inv[$i]->id,
                            'status' => $status
                        );

                        $save_log_inv = Invoices_log::create($log_inv);
                    }
                }

                $log = array(
                    'invoice_group_id' => $request['external_id'],
                    'status' => $status
                );

                if($responseLog = Invoices_group_log::create($log))
                {
                    $to = $device_id;
                    $data = [
                        'title'=>'Pembayaran diterima',
                        'body'=>'Pembayaran Anda telah diterima',
                        'android_channel_id'=>"001"
                    ];
                    $notif = new Helper();
                    $notif->sendMobileNotification($to,$data);

                    $listAgent = DB::table('agents')
                            ->join('users','users.id','=','agents.user_id')
                            ->whereIn('agents.id',$list_agent)
                            ->select('users.device_id')
                            ->get();

                    for($i=0; $i<sizeof($listAgent); $i++)
                    {
                        $to = $listAgent[$i]->device_id;
                        $data = [
                            'title'=>'Pesanan baru',
                            'body'=>'Tokomu dapat pesanan baru nih, ayo cek sekarang.',
                            'android_channel_id'=>"001"
                        ];
                        $notif = new Helper();
                        $notif->sendMobileNotification($to,$data);
                    }

                    return response()->json([
                        'status' => 1,
                        'message' => 'Payment receive'
                    ],200);
                }
            }
        }
        else if($request['status'] == 'EXPIRED')
        {
            if($data->status == 3)
            {
                return response()->json([
                    'status' => 1,
                    'message' => 'Have been processed!'
                ],200);
            }
            $status = 3;
            $batal = 4;
            $data->status = $status;
            if($data->save())
            {
                $up_data = DB::table('invoices')
                            ->where('invoices_group_id',$data->id)
                            ->update(['status' => $batal]);

                if($up_data)
                {
                    $list_inv = DB::table('invoices')
                            ->where('invoices_group_id',$data->id)
                            ->select('id')
                            ->get();

                    $device_id = $list_inv[0]->device_id;

                    $list_inv_id = array();

                    for($i=0; $i<sizeof($list_inv); $i++){
                        $log_inv = array(
                            'invoice_id' => $list_inv[$i]->id,
                            'status' => $batal
                        );

                        $save_log_inv = Invoices_log::create($log_inv);
                        array_push($list_inv_id,$list_inv[$i]->id);
                    }

                    $list_product = DB::table('invoices_products')
                                ->whereIn('invoice_id',$list_inv_id)
                                ->get();

                    $list_product_id = array();
                    $query = "UPDATE products SET stock = CASE";
                    $query_end = "END WHERE id IN (";
                    for($i=0; $i<sizeof($list_product); $i++)
                    {
                        $query = $query . " WHEN id = '".$list_product[$i]->product_id."' THEN stock+".$list_product[$i]->quantity." ";
                        $query_end = $query_end . "'".$list_product[$i]->product_id."'";
                        if($i < (sizeof($list_product)-1)){
                            $query_end = $query_end . ",";
                        }
                    }

                    $query_end = $query_end . ")";
                    $total = $query.$query_end;

                    $res = DB::statement($total);
                }

                $log = array(
                    'invoice_group_id' => $request['external_id'],
                    'status' => $status
                );

                if($responseLog = Invoices_group_log::create($log))
                {
                    $to = $device_id;
                    $data = [
                        'title'=>'Batas waktu pembayaran habis',
                        'body'=>'Batas waktu pembayaran produk pesanan Anda habis',
                        'android_channel_id'=>"001"
                    ];
                    $notif = new Helper();
                    $notif->sendMobileNotification($to,$data);

                    return response()->json([
                        'status' => 1,
                        'message' => 'Payment expired'
                    ],200);
                }
            }
        }
    }


    public function testXendit(Request $request)
    {
        $send = $request->all();
        $data = DB::table('invoices_groups')
                ->where('status',0)
                ->update(['status' => 1,'xendit_id' => $send['id'].'tes']);

        return response()->json([
            'status' => 1,
            'message' => 'Testing boy!'
        ],200);
    }


    public function showAll()
    {
        $data = Invoice::all();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }
        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

     public function showLimit(Request $request)
    {
        $request->validate([
            'limit' => 'required'
        ]);

        $data = Invoice::inRandomOrder()->limit($request['limit'])->get();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function show(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        // $data = Invoice::find($request['target_id']);
        $data = DB::table('invoices')
                    ->join('agents','agents.id','=','invoices.agent_id')
                    ->where('invoices.id',$request['target_id'])
                    ->select('invoices.*','agents.name AS agent_name','agents.image')
                    ->first();

        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        if(!is_null($data->image))
        {
            $data->image = url('/').'/'.$data->image;
        }


        $logistic = DB::table('invoices_logistics')
                    ->join('logistics','logistics.id','=','invoices_logistics.logistic_id')
                    ->where('invoices_logistics.invoice_id',$data->id)
                    ->select('logistics.name')
                    ->first();

        $payment = DB::table('invoices_groups')
                    ->join('payments','payments.id','=','invoices_groups.payment_id')
                    ->where('invoices_groups.id',$data->invoices_group_id)
                    ->select('gate','method','method_code')
                    ->first();

        $product = DB::table('invoices_products')
                    ->where('invoice_id',$data->id)
                    ->get();

        $listProduct = array();
        for($i=0; $i<sizeof($product); $i++)
        {
            array_push($listProduct,$product[$i]->product_id);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$listProduct)
                    ->get();

        for($i=0; $i<sizeOf($product); $i++)
        {
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$product[$i]->product_id){
                    $product[$i]->image = url('/').'/'.$image[$j]->image;
                    break;
                }
            }
        }

        $user = array(
            'name' => request()->user()->name,
            'phone' => request()->user()->phone,
            'address' => request()->user()->address,
        );

        $data->user = $user;
        $data->logistic = $logistic;
        $data->payment = $payment;
        $data->products = $product;

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function store(Request $request)
    {

        $request->validate([
            'carts' => 'required|string',
            'payment_id' => 'required|string|exists:payments,id',
            'logistics' => 'required|string'
        ]);

        $listCart = json_decode($request['carts'],true);
        $listLogistic = json_decode($request['logistics'],true);

        $data = DB::table('carts')
                ->join('products','products.id','=','carts.product_id')
                ->join('agents','agents.id','=','products.agent_id')
                ->select('carts.*','products.min_order','products.stock','products.agent_id','products.supplier_id','products.name','products.price','agents.name AS store','agents.image AS store_image','agents.address')
                ->whereIn('carts.id',$listCart)
                ->orderBy('products.agent_id','DESC')
                ->get();

        if(sizeOf($data)== 0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        $invoice_group = array(
            'external_payment_id' => null,
            'payment_id' => $request['payment_id'],
            'amount' => 0,
            'status' => 0,
            'user_id' => request()->user()->id
        );

        $listAgent = array();

        $group_response = Invoices_group::create($invoice_group);
        $invoice_group_id = $group_response['id'];

        $flagAgent = '';
        $lastInvoice = '';
        $lastStore = '';
        $tempAmount = 0;
        $totalAmount = 0;
        for($i=0; $i<sizeof($data); $i++)
        {
            $tempAgent = $data[$i]->agent_id;
            if($flagAgent != $tempAgent)
            {
                if($tempAmount != 0)
                {
                    $totalAmount = $totalAmount + $tempAmount;

                    $updateAmount = Invoice::find($lastInvoice);
                    $updateAmount->amount = $tempAmount;
                    $tempAmount = 0;
                    $updateAmount->save();
                }

                $invoice = array(
                    'user_id' => request()->user()->id,
                    'supplier_id' => $data[$i]->supplier_id,
                    'amount'=>0,
                    'status'=>0,
                    'agent_id'=>$data[$i]->agent_id,
                    'invoices_group_id' =>$invoice_group_id,
                );

                array_push($listAgent,$data[$i]->agent_id);

                $response = Invoice::create($invoice);
                $lastInvoice = $response['id'];
                $lastStore = $data[$i]->store;

                $logisticData = array(
                    'invoice_id'=> $response['id'],
                    'logistic_id'=> $listLogistic[$data[$i]->agent_id],
                    'resi'=>null,
                    'status'=>0
                );

                $inputLogistic = Invoices_logistic::create($logisticData);

                $productInvoice = array(
                    'invoice_id' => $lastInvoice,
                    'product_id' => $data[$i]->product_id,
                    'name' => $data[$i]->name,
                    'price' => $data[$i]->price,
                    'quantity' => $data[$i]->quantity
                );

                if($response_product = Invoices_product::create($productInvoice))
                {
                    $reduceStokProduct = Product::find($productInvoice['product_id']);
                    $reduceStokProduct->stock = $reduceStokProduct->stock - $productInvoice['quantity'];
                    $reduceStokProduct->save();

                    $tempAmount = $tempAmount + ($productInvoice['price']*$productInvoice['quantity']);
                }

                $flagAgent = $data[$i]->agent_id;

                if($i == (sizeof($data)-1)){
                    $totalAmount = $totalAmount + $tempAmount;
                    $updateAmount = Invoice::find($lastInvoice);
                    $updateAmount->amount = $tempAmount;
                    $tempAmount = 0;
                    $updateAmount->save();
                }
            }
            else {
                $productInvoice = array(
                    'invoice_id' => $lastInvoice,
                    'product_id' => $data[$i]->product_id,
                    'name' => $data[$i]->name,
                    'price' => $data[$i]->price,
                    'quantity' => $data[$i]->quantity
                );

                if($response_product = Invoices_product::create($productInvoice))
                {
                    $reduceStokProduct = Product::find($productInvoice['product_id']);
                    $reduceStokProduct->stock = $reduceStokProduct->stock - $productInvoice['quantity'];
                    $reduceStokProduct->save();

                    $tempAmount = $tempAmount + ($productInvoice['price']*$productInvoice['quantity']);
                }

                if($i == (sizeof($data)-1)){
                    $totalAmount = $totalAmount + $tempAmount;
                    $updateAmount = Invoice::find($lastInvoice);
                    $updateAmount->amount = $tempAmount;
                    $updateAmount->save();
                    $tempAmount = 0;
                }
            }

            $removeCart = Cart::find($data[$i]->id);
            $resDelete = $removeCart->delete();
        }

        $payment = Payment::find($request['payment_id']);

        if($payment->gate == 'XENDIT'){
            Xendit::setApiKey(env('SECRET_API_KEY'));
            $params = ['external_id' => $invoice_group_id,
                'payer_email' => request()->user()->email,
                'description' => 'Pembayaran PadiMall - '.request()->user()->name,
                'amount' => $totalAmount
            ];

            if($createInvoice = \Xendit\Invoice::create($params))
            {
                $group_response->amount = $totalAmount;
                $group_response->external_payment_id = $createInvoice['id'];
                $group_response->save();

                $to = request()->user()->device_id;
                $data = [
                    'title'=>'Pesanan dibuat',
                    'body'=>'Pesanan Anda telah berhasil dibuat. Terimaksih telah berbelanja di PadiMall',
                    'android_channel_id'=>"001"
                ];
                $notif = new Helper();
                $notif->sendMobileNotification($to,$data);

                return response()->json([
                    'status' => 1,
                    'message' => 'Resource created!',
                    'group_id' => $group_response['id']
                ],201);
            }
            else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Error when create an invoice!'
                ],200);
            }
        }
    }

    public function add_resi(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:invoices,id',
            'resi'=>'required|string'
        ]);

        $data = DB::table('invoices_logistics')
                ->where('invoice_id',$request['target_id'])
                ->where('resi',NULL)
                ->update(['resi' => $request['resi']]);

        $up_invoice = DB::table('invoices')
                     ->where('id',$request['target_id'])
                     ->where('status',1)
                     ->update(['status' => 2]);

        if($up_invoice){
            $log_inv = array(
                'invoice_id' => $request['target_id'],
                'status' => 2
            );
            $save_log = Invoices_log::create($log_inv);
        }


        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }

    public function track(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:invoices,id'
        ]);

        $data = DB::table('invoices_logistics')
                ->join('logistics','logistics.id','=','invoices_logistics.logistic_id')
                ->select('invoices_logistics.*','logistics.name')
                ->where('invoices_logistics.invoice_id',$request['target_id'])
                ->first();

        if(is_null($data->resi)){
            return response()->json([
                'status' => 0,
                'message' => 'Resi not found!'
            ],200);
        }
        else {
            $client = new Client();
            $header1 = [
                "headers" =>[
                    'Content-Type' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                ]
            ];

            if($data->name == 'PADISTIC')
            {
                $input_string = 'target_id='.$data->resi;
                $res = $client->request('POST','http://api-logistic.padimall.id/api/v1/tracking/package-public?'.$input_string,[
                    'headers' => $header1,
                ]);
                $res = json_decode($res->getBody(),true);

                return response()->json([
                    $res
                ],200);

            }
        }


    }

    public function update(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        $data = Invoice::find($request['target_id']);

        if(!is_null($request['supplier_id'])){
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id'
            ]);
            $data->supplier_id = $request['supplier_id'];
        }

        if(!is_null($request['amount'])){
            $request->validate([
                'amount' => 'required'
            ]);
            $data->amount = $request['amount'];
        }

        if(!is_null($request['status'])){
            $request->validate([
                'status' => 'required'
            ]);
            $data->status = $request['status'];
        }

        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }

    public function list(Request $request)
    {
        $group = DB::table('invoices_groups')
                ->where('user_id',request()->user()->id)
                ->orderBy('created_at','DESC')
                ->get();
        // Invoices_group::where('user_id',)->get();

        if(sizeOf($group)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        $listGroup = array();
        for($i=0; $i<sizeof($group); $i++)
        {
            array_push($listGroup,$group[$i]->id);
        }

        if(!is_null($request['status'])){
            $request->validate([
                'status' => 'required|integer'
            ]);

            $data = DB::table('invoices')
                    ->join('agents','agents.id','=','invoices.agent_id')
                    ->whereIn('invoices.invoices_group_id',$listGroup)
                    ->where('invoices.status',$request['status'])
                    ->select('invoices.*','agents.name AS agent_name','agents.image')
                    ->orderBy('invoices.created_at','DESC')
                    ->get();
        }
        else {
            $data = DB::table('invoices')
                    ->join('agents','agents.id','=','invoices.agent_id')
                    ->whereIn('invoices.invoices_group_id',$listGroup)
                    ->select('invoices.*','agents.name AS agent_name','agents.image')
                    ->get();
        }



        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        // $data = Invoice::where('user_id',request()->user()->id)->get();

        $listInvoice = array();
        for($i=0; $i<sizeof($data); $i++)
        {
            if(!is_null($data[$i]->image))
            {
                $data[$i]->image = url('/').'/'.$data[$i]->image;
            }
            array_push($listInvoice,$data[$i]->id);
        }

        $product = DB::table('invoices_products')
                    ->whereIn('invoice_id',$listInvoice)
                    ->get();

        $listProduct = array();
        for($i=0; $i<sizeof($product); $i++)
        {
            array_push($listProduct,$product[$i]->product_id);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$listProduct)
                    ->get();

        for($i=0; $i<sizeOf($product); $i++)
        {
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$product[$i]->product_id){
                    $product[$i]->image = url('/').'/'.$image[$j]->image;
                    break;
                }
            }
        }

        for($i=0; $i<sizeof($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeof($product); $j++)
            {
                if($data[$i]->id == $product[$j]->invoice_id)
                {
                    array_push($temp,$product[$j]);
                }
                $data[$i]->products = $temp;
            }
        }

        for($i=0; $i<sizeof($group); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeof($data); $j++)
            {
                if($group[$i]->id == $data[$j]->invoices_group_id)
                {
                    array_push($temp,$data[$j]);
                }
                $group[$i]->invoices = $temp;
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'invoice_groups' => $group
        ],200);
    }

    public function invoice_group_detail(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:invoices_groups,id'
        ]);

        // $group = Invoices_group::where('id',$request['target_id'])->first();
        $group = DB::table('invoices_groups')
                    ->join('payments','payments.id','=','invoices_groups.payment_id')
                    ->where('invoices_groups.id',$request['target_id'])
                    ->select('invoices_groups.*','payments.gate','payments.method','payments.method_code')
                    ->first();

        if(is_null($group)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        $group->payment = array(
            'gate' => $group->gate,
            'method' => $group->method,
            'method_code' => $group->method_code,
        );

        unset($group->gate);
        unset($group->method);
        unset($group->method_code);

        $user = array(
            'name' => request()->user()->name,
            'phone' => request()->user()->phone,
            'address' => request()->user()->address,
        );

        $group->user = $user;

        $data = DB::table('invoices')
                    ->join('agents','agents.id','=','invoices.agent_id')
                    ->where('invoices.invoices_group_id',$request['target_id'])
                    ->select('invoices.*','agents.name AS agent_name','agents.image')
                    ->get();

        if(sizeof($data) == 0)
        {
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        for($in=0; $in<sizeof($data); $in++)
        {
            if(!is_null($data[$in]->image))
            {
                $data[$in]->image = url('/').'/'.$data[$in]->image;
            }

            $logistic = DB::table('invoices_logistics')
                        ->join('logistics','logistics.id','=','invoices_logistics.logistic_id')
                        ->where('invoices_logistics.invoice_id',$data[$in]->id)
                        ->select('logistics.name')
                        ->first();

            $product = DB::table('invoices_products')
                        ->where('invoice_id',$data[$in]->id)
                        ->get();

            $listProduct = array();
            for($i=0; $i<sizeof($product); $i++)
            {
                array_push($listProduct,$product[$i]->product_id);
            }

            $image = DB::table('products_images')
                        ->whereIn('product_id',$listProduct)
                        ->get();

            for($i=0; $i<sizeOf($product); $i++)
            {
                for($j=0; $j<sizeOf($image); $j++)
                {
                    if($image[$j]->product_id==$product[$i]->product_id){
                        $product[$i]->image = url('/').'/'.$image[$j]->image;
                        break;
                    }
                }
            }

            $data[$in]->logistic = $logistic;
            $data[$in]->products = $product;
        }

        $group->invoices = $data;

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $group
        ],200);

    }

    public function invoice_seller(Request $request)
    {
        $agent_data = Agent::where('user_id',request()->user()->id)->first();

        if(is_null($agent_data)){

            $supplier_data = Supplier::where('user_id',request()->user()->id)->first();

            if(is_null($supplier_data)){
                return response()->json([
                    'status' => 0,
                    'message' => 'You are not a supplier nor an agent!'
                ],401);
            }

            $data = DB::table('invoices')
                ->join('suppliers','suppliers.id','=','invoices.supplier_id')
                ->where('invoices.supplier_id',$supplier_data->id)
                ->select('invoices.*','suppliers.name AS supplier_name','suppliers.image')
                ->get();

            if(sizeOf($data)==0){
                return response()->json([
                    'status' => 0,
                    'message' => 'Resource not found!'
                ],200);
            }


        }
        else {
            $data = DB::table('invoices')
                ->join('agents','agents.id','=','invoices.agent_id')
                ->where('invoices.agent_id',$agent_data->id)
                ->select('invoices.*','agents.name AS agents_name','agents.image')
                ->get();

            if(sizeOf($data)==0){
                return response()->json([
                    'status' => 0,
                    'message' => 'Resource not found!'
                ],200);
            }
        }

        $listInvoice = array();
        for($i=0; $i<sizeof($data); $i++)
        {
            if(!is_null($data[$i]->image))
            {
                $data[$i]->image = url('/').'/'.$data[$i]->image;
            }
            array_push($listInvoice,$data[$i]->id);
        }

        $product = DB::table('invoices_products')
                    ->whereIn('invoice_id',$listInvoice)
                    ->get();

        $listProduct = array();
        for($i=0; $i<sizeof($product); $i++)
        {
            array_push($listProduct,$product[$i]->product_id);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$listProduct)
                    ->get();

        for($i=0; $i<sizeOf($product); $i++)
        {
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$product[$i]->product_id){
                    $product[$i]->image = url('/').'/'.$image[$j]->image;
                    break;
                }
            }
        }

        for($i=0; $i<sizeof($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeof($product); $j++)
            {
                if($data[$i]->id == $product[$j]->invoice_id)
                {
                    array_push($temp,$product[$j]);
                }
                $data[$i]->products = $temp;
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);


    }


    public function pay(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:invoices_groups,id'
        ]);

        $data = DB::table('invoices_groups')
                ->join('payments','payments.id','=','invoices_groups.payment_id')
                ->where('invoices_groups.id',$request['target_id'])
                ->select('invoices_groups.*','payments.gate','payments.method','payments.method_code')
                ->first();

        if(is_null($data))
        {
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        if($data->gate == "XENDIT")
        {
            $external = $data->external_payment_id;

            Xendit::setApiKey(env('SECRET_API_KEY'));
            $getInvoice = \Xendit\Invoice::retrieve($external);

            if($data->method == "BANK")
            {
                $bank = $getInvoice['available_banks'];
                for($i=0; $i<sizeof($bank); $i++)
                {
                    if($bank[$i]['bank_code'] == $data->method_code)
                    {
                        $show = array(
                            'external_id' => $getInvoice['id'],
                            'invoice_url' => $getInvoice['invoice_url'],
                            'status' => $getInvoice['status'],
                            'bank_code' => $bank[$i]['bank_code'],
                            'expiry_date' => $getInvoice['expiry_date'],
                            'bank_account_number' => $bank[$i]['bank_account_number'],
                            'transfer_amount' => $bank[$i]['transfer_amount'],
                            'bank_branch' => $bank[$i]['bank_branch'],
                        );
                    }
                }

                return response()->json([
                    'status' => 1,
                    'message' => 'Resource found',
                    'data' => $show
                ],200);
            }
            else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Payment method not found'
                ],200);
            }
        }
        else {
            return response()->json([
                'status' => 0,
                'message' => 'Payment method not found'
            ],200);
        }




    }

    public function delete($id){
        $data = Invoice::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}
