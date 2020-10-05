<?php

namespace App\Http\Controllers;

use App\Products_image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ProductsImageController extends Controller
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
        $data = Products_image::all();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],204);
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

        $data = Products_image::inRandomOrder()->limit($request['limit'])->get();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],204);
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

        $data = Products_image::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],204);
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
            'product_id' => 'required|exists:products,id',
            'image.*' => 'required|mimes:jpg,png,jpeg|max:2048',
        ]);

        $data = $request->all();
        if(!is_null($request['image']))
        {
            $array_image = $data['image'];
            for($i=0; $i<sizeOf($array_image); $i++)
            {
                $filename = 'product-'.Str::uuid().'.jpg';
                $data['image'][$i]->move(public_path("/product"),$filename);
                $imageURL = 'product/'.$filename;
                $data_image = array(
                    'product_id' => $request['product_id'],
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

        $data = Products_image::find($request['target_id']);

        if(!is_null($request['product_id'])){
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);
            $data->product_id = $request['product_id'];
        }

        if(!is_null($request['image'])){
            $request->validate([
                'image' => 'required|mimes:png,jpg,jpeg|max:2048'
            ]);

            $image_target = $data->image;
            if(File::exists(public_path($image_target)))
            {
                $status = File::delete(public_path($image_target));
            }

            $filename = 'product-'.Str::uuid().'.jpg';
            $request->file('image')->move(public_path("/product"),$filename);
            $imageURL = 'product/'.$filename;

            $data->image = $request['image'];
        }

        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }

    public function delete(Request $request){
        $request->validate([
            'target_id' => 'required|exists:products_images,id'
        ]);

        $data = Products_image::find($request['target_id']);
        $response = $data->delete();
        return response()->json([
            'status' => 1,
            'message' => 'Resource deleted!'
        ],200);

    }
}
