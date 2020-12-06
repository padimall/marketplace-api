<?php

namespace App\Http\Controllers;

use App\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

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

        $product = DB::table('products')
                    ->where('id',$request['product_id'])
                    ->first();

        $stock = $product->stock;

        $product_exist = DB::table('carts')
                        ->where('user_id',request()->user()->id)
                        ->where('product_id',$request['product_id'])
                        ->select('*')
                        ->first();

        if(!is_null($product_exist)){
            $quantity = $product_exist->quantity + $request['quantity'];
            $message = 'Product exist. Cart updated';
            if($quantity > $stock)
            {
                $quantity = $stock;
                $message = 'Cart set to max stock';
            }

            $update = array(
                'quantity' => $quantity
            );

            $update_exist = DB::table('carts')
                            ->where('user_id',request()->user()->id)
                            ->where('product_id',$request['product_id'])
                            ->update($update);

            return response()->json([
                'status' => 1,
                'message' => $message
            ],200);
        }
        else {
            $data = $request->all();
            $message = 'Resource created!';
            if($data['quantity'] > $stock)
            {
                $data['quantity'] = $stock;
                $message = 'Resource created! Cart set to max stock!';
            }

            $data['user_id'] = request()->user()->id;
            $response = Cart::create($data);
            return response()->json([
                'status' => 1,
                'message' => $message
            ],201);
        }
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
                ->select('carts.*','products.min_order','products.stock','products.agent_id','products.name','products.price','agents.name AS store','agents.image AS store_image','agents.address')
                ->where('carts.user_id',request()->user()->id)
                ->orderBy('products.agent_id','DESC')
                ->get();

        if(sizeOf($data)== 0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        $flagAgent = '';
        $index = 0;
        $cartData = array();
        $tempData = array();
        $tempProduct = array();
        $saveProductId = array();
        for($i=0; $i<sizeof($data); $i++)
        {
            $tempAgent = $data[$i]->agent_id;
            $coverImage = DB::table('products_images')
                            ->where('product_id',$data[$i]->product_id)
                            ->select('image')
                            ->first();
            $tempImage = null;

            if(!is_null($coverImage)){
				if(!is_null($coverImage->image)){
					$tempImage = url('/').'/'.$coverImage->image;
				}
            }

            if($flagAgent != $tempAgent)
            {
                if(sizeof($tempProduct)!=0){
                    $tempData[$index]['orders'] = $tempProduct;
                    $tempProduct = array();
                    $index++;
                }

                array_push($tempData,array(
                    'agent' => array(
                        'id' => $data[$i]->agent_id,
                        'name' => $data[$i]->store,
                        'image' => url('/').'/'.$data[$i]->store_image,
                        'address' => $data[$i]->address
                    )
                ));

                array_push($tempProduct,array(
                    'cart_id' => $data[$i]->id,
                    'product_id' => $data[$i]->product_id,
                    'name' => $data[$i]->name,
                    'image' => $tempImage,
                    'price' => $data[$i]->price,
                    'quantity' => $data[$i]->quantity,
                    'stock' => $data[$i]->stock,
                    'min_order' => $data[$i]->min_order,
                ));
                $flagAgent = $data[$i]->agent_id;

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
                    'image' => $tempImage,
                    'price' => $data[$i]->price,
                    'quantity' => $data[$i]->quantity,
                    'stock' => $data[$i]->stock,
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
