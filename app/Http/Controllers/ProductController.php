<?php

namespace App\Http\Controllers;

use App\Product;
use App\Products_image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
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
        $data = Product::all();
        $image = Products_image::all();

        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],404);
        }

        for($i=0; $i<sizeOf($data); $i++)
        {
            $temp = array();
            for($j=0; $j<sizeOf($image); $j++)
            {
                if($image[$j]['product_id']==$data[$i]['id']){
                    array_push($temp,$image[$j]['image']);
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

    public function showLimit(Request $request)
    {
        $request->validate([
            'limit' => 'required'
        ]);

        $data = Product::inRandomOrder()->limit($request['limit'])->get();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],404);
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

        $data = Product::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],404);
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
            'supplier_id' => 'required|exists:suppliers,id',
            'name'=> 'required',
            'price'=> 'required',
            'weight'=> 'required',
            'description'=> 'required',
            'category'=> 'required|exists:products_categories,id',
            'stock'=> 'required',
            'status'=> 'required',
            'image.*'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $data = $request->all();
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

    public function update(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        $data = Product::find($request['target_id']);

        if(!is_null($request['supplier_id'])){
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id'
            ]);
            $data->supplier_id = $request['supplier_id'];
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

    public function delete($id){
        $data = Product::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}
