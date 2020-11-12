<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Agent;
use App\Cart;
use App\Invoices_product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Xendit\Xendit;

class InvoiceController extends Controller
{
    public function testXendit()
    {
        Xendit::setApiKey(env('SECRET_API_KEY'));
        $getBalance = \Xendit\Balance::getBalance('CASH');
        // var_dump($getBalance);
        // $getAllInvoice = \Xendit\Invoice::retrieveAll();
        echo json_encode($getBalance);
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

        $data = Invoice::find($request['target_id']);
        if(is_null($data)){
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

    public function store2(Request $request)
    {
        $request->validate([
            'carts' => 'required|string'
        ]);

        $listCart = json_decode($request['carts'],true);
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

        $flagAgent = '';
        $lastInvoice = '';
        $tempAmount = 0;
        for($i=0; $i<sizeof($data); $i++)
        {
            $tempAgent = $data[$i]->agent_id;
            if($flagAgent != $tempAgent)
            {
                if($tempAmount != 0)
                {
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
                    'agent_id'=>$data[$i]->agent_id
                );

                $response = Invoice::create($invoice);
                $lastInvoice = $response['id'];

                $productInvoice = array(
                    'invoice_id' => $lastInvoice,
                    'product_id' => $data[$i]->product_id,
                    'name' => $data[$i]->name,
                    'price' => $data[$i]->price,
                    'quantity' => $data[$i]->quantity
                );

                if($response_product = Invoices_product::create($data_product))
                {
                    $tempAmount = $tempAmount + ($productInvoice['price']*$productInvoice['quantity']);
                }

                $flagAgent = $data[$i]->agent_id;

                if($i == (sizeof($data)-1)){
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

                if($response_product = Invoices_product::create($data_product))
                {
                    $tempAmount = $tempAmount + ($productInvoice['price']*$productInvoice['quantity']);
                }

                if($i == (sizeof($data)-1)){
                    $updateAmount = Invoice::find($lastInvoice);
                    $updateAmount->amount = $tempAmount;
                    $updateAmount->save();
                    $tempAmount = 0;
                }
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!'
        ],201);

    }
    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id|string',
            'supplier_id' =>'exists:supplier,id|nullable',
            'amount'=> 'required',
            'status'=> 'required',
            'product'=> 'required|string'
        ]);


        $data = $request->all();
        $data['user_id'] = request()->user()->id;

        if($response = Invoice::create($data))
        {
            $agentData = Agent::where('id',$request['agent_id'])->first();
            $data['product'] = json_decode($data['product'],true);

            if(!is_null($request['product']))
            {
                $array_product = $data['product'];
                for($i=0; $i<sizeOf($array_product); $i++)
                {
                    $checkRequest = new Request($array_product[$i]);
                    $checkRequest->validate([
                        'product_id'=> 'required|exists:products,id'
                    ]);

                    $data_product = array(
                        'invoice_id' => $response['id'],
                        'product_id' => $array_product[$i]['product_id'],
                        'name' => $array_product[$i]['name'],
                        'price' => $array_product[$i]['price'],
                        'quantity' => $array_product[$i]['quantity']
                    );
                    $response_product = Invoices_product::create($data_product);
                }
            }

            $params = [
                'external_id' => $response['id'],
                'payer_email' => request()->user()->email,
                'description' => 'Pembayaran di PadiMall ke toko '.$agentData->name,
                'amount' => $response['amount']
            ];
            Xendit::setApiKey(env('SECRET_API_KEY'));
            $createInvoice = \Xendit\Invoice::create($params);

        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!',
            'detail' => $createInvoice
        ],201);
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
        $data = Invoice::where('user_id',request()->user()->id)->get();

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

    public function delete($id){
        $data = Invoice::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}
