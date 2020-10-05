<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Main_category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class MainCategoryController extends Controller
{
    public function showAll()
    {
        $data = Main_category::all();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        for($i=0; $i<sizeof($data); $i++){
            if(!is_null($data[$i]['image']))
            {
                $data[$i]['image'] = url('/').'/'.$data[$i]['image'];
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
        $data = Main_category::inRandomOrder()->limit($request['limit'])->get();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        for($i=0; $i<sizeof($data); $i++){
            if(!is_null($data[$i]['image']))
            {
                $data[$i]['image'] = url('/').'/'.$data[$i]['image'];
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

        $data = Main_category::find($request['target_id']);

        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        if(!is_null($data['image']))
        {
            $data['image'] = url('/').'/'.$data['image'];
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
            'image' => 'required|mimes:png,jpg,jpeg|max:2008',
            'status' => 'required'
        ]);

        $filename = 'main-category-'.Str::uuid().'.jpg';
        $request->file('image')->move(public_path("/main-category"),$filename);
        $imageURL = 'main-category/'.$filename;

        $data = $request->all();
        $data['image'] = $imageURL;
        $response = Main_category::create($data);

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

        $data = Main_category::find($request['target_id']);

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

            $filename = 'main-category-'.Str::uuid().'.jpg';
            $request->file('image')->move(public_path("/main-category"),$filename);
            $imageURL = 'main-category/'.$filename;

            $data->image = $imageURL;
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

    public function sub(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:main_categories,id'
        ]);

        $data = DB::table('products_categories')
                ->join('main_categories','main_categories.id','=','products_categories.main_category_id')
                ->select('products_categories.*')
                ->where('main_categories.id',$request['target_id'])
                ->get();

        if(sizeof($data)==0)
        {
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

    public function delete($id){
        $data = Main_category::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}
