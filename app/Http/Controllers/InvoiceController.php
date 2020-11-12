<?php

namespace App\Http\Controllers;

use App\Invoice;
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

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id|string',
            'supplier_id' =>'exists:supplier,id|nullable',
            'amount'=> 'required',
            'status'=> 'required',
            'product'=> 'required|string'
        ]);

        //All cart automatic to invoice

        // $cart = DB::table('carts')
        //         ->join('products','products.id','=','carts.product_id')
        //         ->join('suppliers','suppliers.id','=','products.supplier_id')
        //         ->select('carts.*','products.supplier_id','products.name','products.price','suppliers.name AS store')
        //         ->where('carts.user_id',request()->user()->id)
        //         ->orderBy('products.supplier_id','DESC')
        //         ->get();

        // $flagSupplier = '';
        // $tempInvoice = '';
        // $tempAmount = 0;
        // for($i=0; $i<sizeOf($cart); $i++)
        // {
        //     $tempSupplier = $cart[$i]->supplier_id;
        //     if($flagSupplier != $tempSupplier){

        //         if($tempAmount != 0)
        //         {
        //             $invoice_data = Invoice::find($tempInvoice);
        //             $invoice_data->amount = $tempAmount;
        //             $invoice_data->save();
        //             $tempAmount = 0;
        //         }

        //         $data = array(
        //             'user_id' => request()->user()->id,
        //             'supplier_id' => $tempSupplier,
        //             'amount' => 0,
        //             'status' => 1
        //         );
        //         $response = Invoice::create($data);
        //         $tempInvoice = $response['id'];

        //         $data_product = array(
        //             'invoice_id' => $tempInvoice,
        //             'product_id' => $cart[$i]->product_id,
        //             'name' => $cart[$i]->name,
        //             'price' => $cart[$i]->price,
        //             'quantity' => $cart[$i]->quantity
        //         );
        //         $response_product = Invoices_product::create($data_product);
        //         $tempAmount = $tempAmount + $cart[$i]->price*$cart[$i]->quantity;
        //     }
        //     else {
        //         $data_product = array(
        //             'invoice_id' => $tempInvoice,
        //             'product_id' => $cart[$i]->product_id,
        //             'name' => $cart[$i]->name,
        //             'price' => $cart[$i]->price,
        //             'quantity' => $cart[$i]->quantity
        //         );
        //         $response_product = Invoices_product::create($data_product);
        //         $tempAmount = $tempAmount + $cart[$i]->price*$cart[$i]->quantity;
        //     }
        //     $flagSupplier = $cart[$i]->supplier_id;
        //     if($i+1 == sizeOf($cart))
        //     {
        //         $invoice_data = Invoice::find($tempInvoice);
        //         $invoice_data->amount = $tempAmount;
        //         $invoice_data->save();
        //         $tempAmount = 0;
        //     }
        // }

        $data = $request->all();
        $data['user_id'] = request()->user()->id;

        if($response = Invoice::create($data))
        {
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
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!'
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
