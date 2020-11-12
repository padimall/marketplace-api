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
        $cartData = DB::table('carts')
                ->join('products','products.id','=','carts.product_id')
                ->join('agents','agents.id','=','products.agent_id')
                ->select('carts.*','products.min_order','products.stock','products.agent_id','products.name','products.price','agents.name AS store','agents.image AS store_image','agents.address')
                ->whereIn('carts.id',$listCart)
                ->orderBy('products.agent_id','DESC')
                ->get();

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!',
            'detail' => $cartData
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
