<?php

namespace App\Http\Controllers;

use App\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request){
        $request->validate([
            'request_type'=>'required'
        ]);

        $type = $request['request_type'];
        if($type == 1){
            return $this->showAll();
        }
        else if($type == 2){
            return $this->show($request);
        }
        else if($type == 3){
            return $this->store($request);
        }
        else if($type == 4){
            return $this->update($request);
        }
        else if($type == 5){
            return $this->showLimit($request);
        }
    }

    public function showAll()
    {
        $data = Cart::all();
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
        $data = Cart::inRandomOrder()->limit($request['limit'])->get();
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

        $data = Cart::find($request['target_id']);
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

    public function store(Request $request)
    {
        //edit here

        $request->validate([
            'product_id'=> 'required|exists:products,id',
            'quantity'=> 'required|integer',
            'status'=> 'required',
        ]);

        $product_exist = DB::table('carts')
                    ->where('user_id',request()->user()->id)
                    ->where('product_id',$request['product_id'])
                    ->select('*')
                    ->first();

        if(!is_null($product_exist)){
            $quantitiy = $product_exist->quantity + $request['quantity'];
            $update = array(
                'quantity' => $quantitiy
            );

            $update_exist = DB::table('carts')
                            ->where('user_id',request()->user()->id)
                            ->where('product_id',$request['product_id'])
                            ->update($update);

            return response()->json([
                'status' => $update_exist,
                'message' => 'Product exist. Cart updated!'
            ],200);
        }


        $data = $request->all();
        $data['user_id'] = request()->user()->id;
        $response = Cart::create($data);
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

        $data = Cart::find($request['target_id']);

        if(!is_null($request['product_id'])){
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);
            $data->product_id = $request['product_id'];
        }

        if(!is_null($request['quantity'])){
            $request->validate([
                'quantity' => 'required'
            ]);
            $data->quantity = $request['quantity'];
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

        $data = DB::table('carts')
                ->join('products','products.id','=','carts.product_id')
                ->join('agents','agents.id','=','products.agent_id')
                ->select('carts.*','products.min_order','products.agent_id','products.name','products.price','agents.name AS store','agents.image AS store_image','agents.address')
                ->where('carts.user_id',request()->user()->id)
                ->orderBy('products.agent_id','DESC')
                ->get();

        if(sizeOf($data)== 0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        $flagSupplier = '';
        $index = 0;
        $cartData = array();
        $tempData = array();
        $tempProduct = array();
        $saveProductId = array();
        for($i=0; $i<sizeof($data); $i++)
        {
            $tempSupplier = $data[$i]->agent_id;
            if($flagSupplier != $tempSupplier)
            {
                if(sizeof($tempProduct)!=0){
                    $tempData[$index]['orders'] = $tempProduct;
                    $tempProduct = array();
                    $index++;
                }

                array_push($tempData,array(
                    'agent_id' => $data[$i]->agent_id,
                    'store' => $data[$i]->store,
                    'store_image' => $data[$i]->store_image,
                    'address' => $data[$i]->address,
                ));

                array_push($tempProduct,array(
                    'cart_id' => $data[$i]->id,
                    'product_id' => $data[$i]->product_id,
                    'name' => $data[$i]->name,
                    'price' => $data[$i]->price,
                    'quantity' => $data[$i]->quantity,
                    'min_order' => $data[$i]->min_order,
                ));
                $flagSupplier = $data[$i]->agent_id;

                if($i == (sizeof($data)-1)){
                    $tempData[$index]['orders'] = $tempProduct;
                    $tempProduct = array();
                    $index++;
                }
            }
            else {
                array_push($tempProduct,array(
                    'cart_id' => $data[$i]->id,
                    'product_id' => $data[$i]->product_id,
                    'name' => $data[$i]->name,
                    'price' => $data[$i]->price,
                    'quantity' => $data[$i]->quantity,
                    'min_order' => $data[$i]->min_order,
                ));

                if($i == (sizeof($data)-1)){
                    $tempData[$index]['orders'] = $tempProduct;
                    $tempProduct = array();
                    $index++;
                }
            }
        }


        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $tempData
        ],200);

    }

    public function delete(Request $request){
        $request->validate([
            'target_id' => 'required|exists:carts,id'
        ]);

        $data = Cart::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        $response = $data->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Resource deleted!'
        ],200);
    }
}
