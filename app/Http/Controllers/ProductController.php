<?php

namespace App\Http\Controllers;

use App\Product;
use App\Products_image;
use App\Supplier;
use App\Agent;
use App\User;
use App\Agents_affiliate_supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function total()
    {
        $totalProduct = DB::table('products')->where('status',1)->whereNull('deleted_at')->count();
        $totalAgent = DB::table('agents')->count();
        $totalSupplier = DB::table('suppliers')->count();
        $totalUser = DB::table('users')
                    ->where('is_admin',0)
                    ->count();


        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'total' => array(
                'product' => $totalProduct,
                'agent' => $totalAgent,
                'supplier' => $totalSupplier,
                'user' => $totalUser,
            )
        ],200);
    }

    public function showAll()
    {
        $data = DB::table('products')
                ->join('products_categories','products_categories.id','=','products.category')
                ->where('products.status',1)
                ->whereNull('products.deleted_at')
                // ->select('*')
                ->select('products.id','products.supplier_id','products.name','products.category','products_categories.name AS category_name','products.price','products.stock','products.agent_id')
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
                ->join('products_categories','products_categories.id','=','products.category')
                ->where('products.status',1)
                ->whereNull('products.deleted_at')
                // ->select('*')
                ->select('products.id','products.supplier_id','products.name','products.category','products_categories.name AS category_name','products.price','products.stock','products.agent_id')
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
                ->join('products_categories','products_categories.id','=','products.category')
                ->where('products.id',$request['target_id'])
                ->whereNull('products.deleted_at')
                ->select('products.*','products_categories.name AS category_name')
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

        if(!is_null($agentData->image)){
            $agentData->image = url('/').'/'.$agentData->image;
        }

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

        $product_id = $request['target_id'];
        $ratings = DB::table('invoices_product_ratings')
                    ->join('invoices_products','invoices_products.id','=','invoices_product_ratings.invoice_product_id')
                    ->join(DB::raw("(SELECT COUNT(IPR.id) AS total_ratings,SUM(IPR.star) AS total_star FROM invoices_product_ratings AS IPR INNER JOIN invoices_products AS IP ON IPR.invoice_product_id = IP.id WHERE IP.product_id = '$product_id') AS rating_sumarry",[$request['target_id']]),DB::raw('1'),'=',DB::raw('1'))
                    ->where('invoices_products.product_id',$request['target_id'])
                    ->orderBy('star','DESC')
                    ->select('invoices_product_ratings.*','rating_sumarry.*')
                    ->first();


        if(!is_null($ratings)){
            $rating_id = $ratings->id;
            $rating_image = DB::table('invoice_product_rating_images')
                        ->where('invoice_product_rating_id',$rating_id)
                        ->select('*')
                        ->get();
        }


        // $rating_id = array();
        // if(sizeof($ratings)!=0){
        //     for($i=0; $i<sizeof($ratings); $i++)
        //     {
        //         array_push($rating_id,$ratings[$i]->id);
        //     }

        //     $rating_image = DB::table('invoice_product_rating_images')
        //                 ->whereIn('invoice_product_rating_id',$rating_id)
        //                 ->select('*')
        //                 ->get();

        //     for($i=0; $i<sizeof($ratings); $i++)
        //     {
        //         $temp = array();
        //         for($j=0; $j<sizeOf($rating_image); $j++)
        //         {
        //             if($rating_image[$j]->invoice_product_rating_id==$ratings[$i]->id){
        //                 array_push($temp,array(
        //                     'id' => $rating_image[$j]->id,
        //                     'url' => url('/').'/'.$rating_image[$j]->image
        //                 ));
        //             }
        //         }
        //         $ratings[$i]->images = $temp;
        //     }
        // }
        $average_star = (float)0;
        if(is_null($ratings->total_ratings))
        {
            if($ratings->total_ratings != 0){
                $average_star = (float)$ratings->total_star / $ratings->total_ratings;
            }
        }

        $rating_summary = [
            'average_star' => $average_star,
            'total_ratings' => $ratings->total_ratings,
            'sample' => [
                'id' => $ratings->id,
                'name' => $ratings->name,
                'star' => $ratings->star,
                'description' => $ratings->description,
                'show_name' => $ratings->show_name,
                'created_at' => $ratings->created_at,
                'updated_at' => $ratings->updated_at,
                'images' => $rating_image,
            ]
        ];
        $data->rating_summary = $rating_summary;

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data,
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
                ->join('products_categories','products_categories.id','=','products.category')
                ->where('products.name','like','%'.$request['name'].'%')
				->where('products.status',1)
                ->whereNull('products.deleted_at')
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

        if(sizeOf($data)== 0){
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
                        'url' => url('/').'/'.$image[$j]->image
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

    public function product_agent_id(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:agents,id'
        ]);

        $agent_data = Agent::where('id',$request['target_id'])->first();

        if(is_null($agent_data)){
            return response()->json([
                'status' => 0,
                'message' => 'You are not an agent!'
            ],200);
        }

        $data = DB::table('products')
                ->where('agent_id',$agent_data->id)
                ->where('supplier_id',NULL)
                ->whereNull('deleted_at')
                ->select('*')
                ->get();
        // Product::where('agent_id',$agent_data->id)->get();

        if(sizeOf($data)== 0){
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
                        'url' => url('/').'/'.$image[$j]->image
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

    public function product_shop(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:agents,id'
        ]);

        $agent_data = Agent::where('id',$request['target_id'])->first();

        if(is_null($agent_data)){
            return response()->json([
                'status' => 0,
                'message' => 'You are not an agent!'
            ],200);
        }

        $data = DB::table('products')
                ->where('agent_id',$agent_data->id)
                ->whereNull('deleted_at')
                ->where('status',1)
                ->select('*')
                ->get();
        // Product::where('agent_id',$agent_data->id)->get();

        if(sizeOf($data)== 0){
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
                        'url' => url('/').'/'.$image[$j]->image
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

        if(sizeOf($data)== 0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

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

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function product_supplier_id(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:suppliers,id'
        ]);
        $supplier_data = Supplier::where('id',$request['target_id'])->first();

        if(is_null($supplier_data)){
            return response()->json([
                'status' => 0,
                'message' => 'You are not a supplier!'
            ],200);
        }

        $data = Product::where('supplier_id',$supplier_data->id)->get();

        if(sizeOf($data)== 0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

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
                ->whereNull('products.deleted_at')
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
                ->whereNull('products.deleted_at')
				->where('products.status',1)
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
        $cekAgent = false;
        $cekSupplier = false;

        $agent = Agent::where('user_id',request()->user()->id)->first();
        $supplier = Supplier::where('user_id',request()->user()->id)->first();

        if(!is_null($agent))
        {
            if($agent->id == $data->agent_id){
                $cekAgent = true;
            }
        }
        else {
            if(!is_null($supplier)){
                if($supplier->id == $data->supplier_id){
                    $cekSupplier = true;
                }
            }
        }

        if($cekAgent || $cekSupplier){
            $response = $data->delete();
            return response()->json([
                'status' => 1,
                'message' => 'Resource deleted!'
            ],200);
        }
        else {
            return response()->json([
                'status' => 0,
                'message' => 'This is not your product!'
            ],200);
        }

    }

    public function delete_admin(Request $request){
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
