<?php

namespace App\Http\Controllers;

use App\Product;
use App\Products_image;
use App\Supplier;
use App\Agent;
use App\Agents_affiliate_supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function showAll()
    {
        $data = DB::table('products')
                ->where('status',1)
                ->whereNull('deleted_at')
                // ->select('*')
                ->select('id','supplier_id','name','price','stock','agent_id')
                ->get();

        // Product::all();
        $image = Products_image::all();

        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]['product_id']==$data[$i]->id){
                    array_push($temp,array(
                        'id' => $image[$j]['id'],
                        'url' => url('/').'/'.$image[$j]['image']
                    ));
                }
            }
            $data[$i]->image = $temp;
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

        $data = DB::table('products')
                ->where('status',1)
                ->whereNull('deleted_at')
                // ->select('*')
                ->select('id','supplier_id','name','price','stock','agent_id')
                ->inRandomOrder()
                ->limit($request['limit'])
                ->get();

        $array_product_id = array();
        for ($i=0; $i<sizeOf($data); $i++)
        {
            array_push($array_product_id,$data[$i]->id);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$array_product_id)
                    ->get();

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$data[$i]->id){
                    array_push($temp,array(
                        'id' => $image[$j]->id,
                        'url' => url('/').'/'.$image[$j]->image
                    ));
                }
            }
            $data[$i]->image = $temp;
        }

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

        // $data = Product::find($request['target_id']);
        $data = DB::table('products')
                ->where('id',$request['target_id'])
                ->whereNull('deleted_at')
                ->first();

        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        $agentData = DB::table('agents')
                    ->where('id',$data->agent_id)
                    ->select('id','name','image','address')
                    ->first();
        $agentData->image = url('/').'/'.$agentData->image;

        $data->agent = $agentData;
        unset($data->agent_id);

        $image = DB::table('products_images')
                ->select('image','id')
                ->where('product_id',$request['target_id'])
                ->get();

        $temp = array();

        for($i=0; $i<sizeOf($image); $i++){
            array_push($temp,array(
                'id' => $image[$i]->id,
                'url' => url('/').'/'.$image[$i]->image
            ));
        }

        if(sizeof($temp)!=0){
            $data->image = $temp;
        }
        else {
            $data->image = NULL;
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function store(Request $request)
    {
        $agent_data = Agent::where('user_id',request()->user()->id)->first();
        if(is_null($agent_data)){
            $supplier_data = Supplier::where('user_id',request()->user()->id)->first();
            if(is_null($supplier_data)){
                return response()->json([
                    'status' => 0,
                    'message' => 'You are not a supplier or an agent!'
                ],401);
            }
            $myAgent = Agents_affiliate_supplier::where('supplier_id',$supplier_data->id)->first();

            if(is_null($myAgent)){
                return response()->json([
                    'status' => 0,
                    'message' => 'You are not a supplier or an agent!'
                ],401);
            }
        }

        $request->validate([
            'name'=> 'required',
            'price'=> 'required',
            'weight'=> 'required',
            'description'=> 'required',
            'category'=> 'required|exists:products_categories,id',
            'stock'=> 'required',
            'min_order'=> 'required',
            'image.*'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);

        if(isset($request['image']) && !is_array($request['image'])){
            return response()->json([
                'status' => 0,
                'message' => 'Use image[] instead of image!'
            ],200);
        }

        $data = $request->all();

        if(is_null($agent_data)){
            $data['agent_id'] = $myAgent->agent_id;
            $data['supplier_id'] = $supplier_data->id;
            $data['status'] = 0;
        }
        else{
            $data['agent_id'] = $agent_data->id;
            $data['supplier_id'] = NULL;
            $data['status'] = 1;
        }

        $response = Product::create($data);

        if(!is_null($request['image']))
        {
            $array_image = $data['image'];
            for($i=0; $i<sizeOf($array_image); $i++)
            {
                $filename = 'product-'.Str::uuid().'.jpg';
                $data['image'][$i]->move(public_path("/product"),$filename);
                $imageURL = 'product/'.$filename;
                $data_image = array(
                    'product_id' => $response['id'],
                    'image'=>$imageURL
                );
                $response_image = Products_image::create($data_image);
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!'
        ],201);
    }

    public function update_status(Request $request)
    {
        $request->validate([
            'target_id' => 'required',
            'status' => 'required'
        ]);

        $data = Product::find($request['target_id']);
        $is_agent = Agent::where('user_id',request()->user()->id)->first();

        if(!is_null($is_agent)){
            if($is_agent->id != $data->agent_id){
                return response()->json([
                    'status' => 0,
                    'message' => 'This is not your supplier product!'
                ],200);
            }
        }
        else {
            return response()->json([
                'status' => 0,
                'message' => 'You are not an agent!'
            ],200);
        }

        $data->status = $request['status'];
        $data->save();

        return response()->json([
            'status' => 1,
            'message' => 'Status updated!'
        ],200);

    }

    public function update(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        $data = Product::find($request['target_id']);

        $is_agent = Agent::where('user_id',request()->user()->id)->first();
        $is_supplier = Supplier::where('user_id',request()->user()->id)->first();

        $check_sup = false;

        if(!is_null($is_agent)){
            if($is_agent->id != $data->agent_id)
            {
                $check_sup = true;
            }
        }
        else {
            $check_sup = true;
        }

        if(!is_null($is_supplier) && $check_sup)
        {
            if($is_supplier->id != $data->supplier_id)
            {
                return response()->json([
                    'status' => 0,
                    'message' => 'This is not your product'
                ],200);
            }
        }
        else if(is_null($is_supplier) && $check_sup){
            return response()->json([
                'status' => 0,
                'message' => 'This is not your product'
            ],200);
        }


        if(!is_null($request['name'])){
            $request->validate([
                'name' => 'required'
            ]);
            $data->name = $request['name'];
        }

        if(!is_null($request['price'])){
            $request->validate([
                'price' => 'required'
            ]);
            $data->price = $request['price'];
        }

        if(!is_null($request['weight'])){
            $request->validate([
                'weight' => 'required'
            ]);
            $data->weight = $request['weight'];
        }

        if(!is_null($request['description'])){
            $request->validate([
                'description' => 'required'
            ]);
            $data->description = $request['description'];
        }

        if(!is_null($request['category'])){
            $request->validate([
                'category' => 'required|exists:products_categories,id'
            ]);
            $data->category = $request['category'];
        }

        if(!is_null($request['stock'])){
            $request->validate([
                'stock' => 'required'
            ]);
            $data->stock = $request['stock'];
        }

        if(!is_null($request['min_order'])){
            $request->validate([
                'min_order' => 'required'
            ]);
            $data->min_order = $request['min_order'];
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

    public function product_search(Request $request){
        $request->validate([
            'name' => 'required|string'
        ]);

        $data = DB::table('products')
                ->where('name','like','%'.$request['name'].'%')
                ->whereNull('deleted_at')
                ->get();

        $array_product_id = array();
        for ($i=0; $i<sizeOf($data); $i++)
        {
            array_push($array_product_id,$data[$i]->id);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$array_product_id)
                    ->get();

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$data[$i]->id){
                    array_push($temp,array(
                        'id' => $image[$j]->id,
                        'url' => url('/').'/'.$image[$j]->image
                    ));
                }
            }
            $data[$i]->image = $temp;
        }

        if(sizeOf($data)== 0){
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

    public function product_agent(Request $request)
    {
        $agent_data = Agent::where('user_id',request()->user()->id)->first();

        if(is_null($agent_data)){
            return response()->json([
                'status' => 0,
                'message' => 'You are not an agent!'
            ],401);
        }

        $data = DB::table('products')
                ->where('agent_id',$agent_data->id)
                ->where('supplier_id',NULL)
                ->whereNull('deleted_at')
                ->select('*')
                ->get();
        // Product::where('agent_id',$agent_data->id)->get();

        $array_product_id = array();
        for ($i=0; $i<sizeOf($data); $i++)
        {
            array_push($array_product_id,$data[$i]->id);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$array_product_id)
                    ->get();

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$data[$i]->id){
                    array_push($temp,array(
                        'id' => $image[$j]->id,
                        'url' => url('/').'/'.$image[$j]->image
                    ));
                }
            }
            $data[$i]->image = $temp;
        }

        if(sizeOf($data)== 0){
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

    public function product_supplier(Request $request)
    {
        $supplier_data = Supplier::where('user_id',request()->user()->id)->first();

        if(is_null($supplier_data)){
            return response()->json([
                'status' => 0,
                'message' => 'You are not a supplier!'
            ],401);
        }

        $data = Product::where('supplier_id',$supplier_data->id)->get();

        $array_product_id = array();
        for ($i=0; $i<sizeOf($data); $i++)
        {
            array_push($array_product_id,$data[$i]['id']);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$array_product_id)
                    ->get();

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$data[$i]['id']){
                    array_push($temp,array(
                        'id' => $image[$j]->id,
                        'url' => url('/').'/'.$image[$j]->image
                    ));
                }
            }
            $data[$i]['image'] = $temp;
        }

        if(sizeOf($data)== 0){
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

    public function product_my_supplier(Request $request)
    {
        $request->validate([
            'target_id' => 'required|string|exists:suppliers,id'
        ]);

        $data = Product::where('supplier_id',$request['target_id'])->get();

        $array_product_id = array();
        for ($i=0; $i<sizeOf($data); $i++)
        {
            array_push($array_product_id,$data[$i]['id']);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$array_product_id)
                    ->get();

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$data[$i]['id']){
                    array_push($temp,array(
                        'id' => $image[$j]->id,
                        'url' => url('/').'/'.$image[$j]->image
                    ));
                }
            }
            $data[$i]['image'] = $temp;
        }

        if(sizeOf($data)== 0){
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

    public function product_category(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $data = DB::table('products')
                ->join('products_categories','products_categories.id','=','products.category')
                ->where('products_categories.name','like','%'.$request['name'].'%')
                ->where('products.status',1)
                ->whereNull('deleted_at')
                ->select('products.*','products_categories.name AS category_name')
                ->get();

        $array_product_id = array();
        for ($i=0; $i<sizeOf($data); $i++)
        {
            array_push($array_product_id,$data[$i]->id);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$array_product_id)
                    ->get();

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$data[$i]->id){
                    array_push($temp,array(
                        'id' => $image[$j]->id,
                        'url' =>url('/').'/'.$image[$j]->image
                    ));
                }
            }
            $data[$i]->image = $temp;
        }

        if(sizeOf($data)== 0){
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

    public function product_main_category(Request $request){
        $request->validate([
            'target_id' => 'required|exists:main_categories,id'
        ]);

        $data = DB::table('products')
                ->join('products_categories','products_categories.id','=','products.category')
                ->join('main_categories','main_categories.id','=','products_categories.main_category_id')
                ->where('main_categories.id',$request['target_id'])
                ->whereNull('deleted_at')
                ->select('products.*')
                ->get();

        if(sizeof($data) == 0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        $array_product_id = array();
        for ($i=0; $i<sizeOf($data); $i++)
        {
            array_push($array_product_id,$data[$i]->id);
        }

        $image = DB::table('products_images')
                    ->whereIn('product_id',$array_product_id)
                    ->get();

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]->product_id==$data[$i]->id){
                    array_push($temp,array(
                        'id' => $image[$j]->id,
                        'url' =>url('/').'/'.$image[$j]->image
                    ));
                }
            }
            $data[$i]->image = $temp;
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }


    public function delete(Request $request){
        $request->validate([
            'target_id' => 'required|exists:products,id'
        ]);
        $data = Product::find($request['target_id']);
        $response = $data->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Resource deleted!'
        ],200);
    }
}
