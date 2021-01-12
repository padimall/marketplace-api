<?php

namespace App\Http\Controllers;

use App\Products_category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Helper\Helper;

class ProductsCategoryController extends Controller
{
    public function showAll()
    {
        $data = DB::table('products_categories')
                ->leftJoin(DB::raw('(select COUNT(id) AS count_product,category from products group by category) AS product_count'),'product_count.category','=','products_categories.id')
                ->select('product_count.count_product','products_categories.*')
                ->where('products_categories.status',1)
                ->get();

        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        for($i=0; $i<sizeof($data); $i++){
            if(!is_null($data[$i]->image))
            {
                $data[$i]->image = url('/').'/'.$data[$i]->image;
            }
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
        $data = Products_category::inRandomOrder()->limit($request['limit'])->get();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }
        for($i=0; $i<sizeof($data); $i++){
            if(!is_null($data[$i]->image))
            {
                $data[$i]->image = url('/').'/'.$data[$i]->image;
            }
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

        $data = Products_category::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        if(!is_null($data->image))
        {
            $data->image = url('/').'/'.$data->image;
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
            'name' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'status' => 'required',
            'main_category_id' => 'required|exists:main_categories,id|string'
        ]);

        $filename = 'product-category-'.Str::uuid().'.png';
        $request->file('image')->move(public_path("/product-category"),$filename);
        $imageURL = 'product-category/'.$filename;

        $data = $request->all();
        $data['image'] = $imageURL;
        $response = Products_category::create($data);

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

        $data = Products_category::find($request['target_id']);

        if(!is_null($request['name'])){
            $request->validate([
                'name' => 'required'
            ]);
            $data->name = $request['name'];
        }

        if(!is_null($request['image'])){
            $request->validate([
                'image' => 'required|mimes:png,jpg,jpeg|max:2008'
            ]);
            $image_target = $data->image;
            if(File::exists(public_path($image_target)))
            {
                $status = File::delete(public_path($image_target));
            }

            $filename = 'product-category-'.Str::uuid().'.png';
            $request->file('image')->move(public_path("/product-category"),$filename);
            $imageURL = 'product-category/'.$filename;

            $data->image = $imageURL;
        }

        if(!is_null($request['status'])){
            $request->validate([
                'status' => 'required'
            ]);
            $data->status = $request['status'];
        }

        if(!is_null($request['main_category_id'])){
            $request->validate([
                'main_category_id' => 'required|exists:main_categories,id'
            ]);
            $data->main_category_id = $request['main_category_id'];
        }

        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }

    public function delete($id){
        $data = Products_category::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}
