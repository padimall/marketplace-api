<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Agent;
use App\Product;
use App\Cart;
use App\Invoices_group_log;
use App\Invoices_group;
use App\Invoices_product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Xendit\Xendit;

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

        if($request['status'] == 'PAID' || $request['status'] == 'SETTLE')
        {
            $status = 1;
            $data->status = $status;
            if($data->save())
            {
                $log = array(
                    'invoice_group_id' => $request['external_id'],
                    'status' => $status
                );

                if($responseLog = Invoices_group_log::create($log))
                {
                    return response()->json([
                        'status' => 1,
                        'message' => 'Payment receive'
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

    public function createInvoice()
    {
        $invoice_group = array(
            'external_payment_id' => null,
            'payment_id' => null,
            'amount' => 0,
            'status' => 0,
        );

        $group_response = Invoices_group::create($invoice_group);

        Xendit::setApiKey(env('SECRET_API_KEY'));
        $params = ['external_id' => $group_response['id'],
            'payer_email' => 'rudi97278@gmail.com',
            'description' => 'Pembayaran PadiMall - ',
            'amount' => 10000
        ];

        $createInvoice = \Xendit\Invoice::create($params);
        return response()->json([
            'status' => 1,
            'message' => 'Testing boy!',
            'detail' => $createInvoice
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

    public function store(Request $request)
    {

        $request->validate([
            'carts' => 'required|string',
            'payment_id' => 'required|string|exists:payments,id',
            'logistic_id' => 'required|string|exists:logistics,id'
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

        $invoice_group = array(
            'external_payment_id' => null,
            'payment_id' => $request['payment_id'],
            'amount' => 0,
            'status' => 0,
        );

        $group_response = Invoices_group::create($invoice_group);

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
                    'invoices_group_id' =>$group_response['id'],
                );

                $response = Invoice::create($invoice);
                $lastInvoice = $response['id'];
                $lastStore = $data[$i]->store;

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

        Xendit::setApiKey(env('SECRET_API_KEY'));
        $params = ['external_id' => $group_response->id,
            'payer_email' => request()->user()->email,
            'description' => 'Pembayaran PadiMall - '.request()->user()->name,
            'amount' => $totalAmount
        ];

        if($createInvoice = \Xendit\Invoice::create($params))
        {
            $group_response->amount = $totalAmount;
            $group_response->xendit_id = $createInvoice['id'];
            $group_response->save();

            return response()->json([
                'status' => 1,
                'message' => 'Resource created!',
                'xendit_detail' => $createInvoice
            ],201);
        }
        else {
            return response()->json([
                'status' => 0,
                'message' => 'Error when create an invoice!'
            ],200);
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
