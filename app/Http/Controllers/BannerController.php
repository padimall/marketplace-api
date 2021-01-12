<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Banner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    public $helper;

    public function __construct(){
        $this->helper = new Helper();
    }

    public function showAll()
    {
        $data = Banner::all();

        if(sizeOf($data)==$this->helper->EMPTY_ARRAY){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Resource not found!'
            ],200);
        }

        for($i=0; $i<sizeOf($data); $i++)
        {
            $data[$i]->image = url('/').'/'.$data[$i]->image;
        }

        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function show(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        $data = Banner::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Resource not found!'
            ],200);
        }

        $data->image = url('/').'/'.$data->image;

        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function type(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $data = DB::table('banners')
                ->where('type',$request['name'])
                ->select('*')
                ->first();

        if(is_null($data)){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Resource not found!'
            ],200);
        }

        $data->image = url('/').'/'.$data->image;

        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource found!',
            'data' => $data
        ],200);



    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|mimes:png,jpg,jpeg',
            'type' => 'required|string',
        ]);

        $filename = 'banner-'.Str::uuid().'.jpg';
        $request->file('image')->move(public_path("/banner"),$filename);
        $imageURL = 'banner/'.$filename;

        $data = $request->all();
        $data['image'] = $imageURL;
        $response = Banner::create($data);

        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource created!'
        ],201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        $data = Banner::find($request['target_id']);

        if(!is_null($request['image'])){
            $request->validate([
                'image' => 'required|mimes:png,jpg,jpeg'
            ]);
            $image_target = $data->image;
            if(File::exists(public_path($image_target)))
            {
                $status = File::delete(public_path($image_target));
            }

            $filename = 'banner-'.Str::uuid().'.png';
            $request->file('image')->move(public_path("/banner"),$filename);
            $imageURL = 'banner/'.$filename;

            $data->image = $imageURL;
        }


        if(!is_null($request['type'])){
            $request->validate([
                'type' => 'required|string'
            ]);
            $data->type = $request['type'];
        }

        $data->save();
        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource updated!'
        ],200);
    }

    public function delete_image(Request $request)
    {
        $data = Agent::where('user_id',request()->user()->id)->first();
        $image_target = $data->image;

        if(File::exists(public_path($image_target)))
        {
            $status = File::delete(public_path($image_target));
        }
        $data->image = NULL;
        $data->save();
        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Image deleted!'
        ],200);

    }

    public function delete(Request $request){
        $request->validate([
            'target_id'
        ]);

        $data = Banner::find($request['target_id']);

        if(is_null($data))
        {
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Resource not found!'
            ],200);
        }

        $image_target = $data->image;

        if(File::exists(public_path($image_target)))
        {
            $status = File::delete(public_path($image_target));
        }

        $response = $data->delete();

        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Banner deleted!'
        ],200);
    }
}
