<?php

namespace App\Http\Controllers;
use App\Checkout;
use App\Cart;
use App\Payment;
use App\Logistic;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{

    public function checkout(Request $request)
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

        $logistic = Logistic::all();
        $payment = DB::table('payments')
                    ->orderBy('method','ASC')
                    ->where('status',1)
                    ->get();
        $payment_group = array();
        $flagMethod = '';
        $tempMethod = '';
        $tempList = array();
        for($i=0; $i<sizeof($payment); $i++){
            $tempMethod = $payment[$i]->method;
            if($flagMethod != $tempMethod)
            {
                if(sizeof($tempList) != 0){
                    array_push($payment_group,[
                        'type' => $flagMethod,
                        'methods' => $tempList
                    ]);
                    $tempList = array();
                }

                array_push($tempList,array(
                    'id' => $payment[$i]->id,
                    'name'=>$payment[$i]->method_code
                ));

                if($i == (sizeof($payment)-1)){
                    array_push($payment_group,[
                        'type' => $tempMethod,
                        'methods' => $tempList
                    ]);
                    $tempList = array();
                }
            }
            else {
                array_push($tempList,array(
                    'id' => $payment[$i]->id,
                    'name'=>$payment[$i]->method_code
                ));

                if($i == (sizeof($payment)-1)){
                    array_push($payment_group,[
                        'type' => $tempMethod,
                        'methods' => $tempList
                    ]);
                    $tempList = array();
                }
            }

            $flagMethod = $tempMethod;
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
            'data' => array(
                'user' => array(
                    'name' => request()->user()->name,
                    'phone' => request()->user()->phone,
                    'address' => request()->user()->address
                ),
                'payments' => $payment_group,
                'logistics' => $logistic,
                'checkouts' => $tempData
            )
        ],200);
    }
}
