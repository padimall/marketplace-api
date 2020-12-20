<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Invoice_product_rating_image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InvoiceProductRatingImageController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'invoice_product_rating_id' => 'required|exists:invoices_product_ratings,id',
            'image.*' => 'required|mimes:jpg,png,jpeg|max:2048',
        ]);

        if(isset($request['image']) && !is_array($request['image'])){
            return response()->json([
                'status' => 0,
                'message' => 'Use image[] instead of image!'
            ],200);
        }

        $data = $request->all();
        if(!is_null($request['image']))
        {
            $array_image = $data['image'];
            for($i=0; $i<sizeOf($array_image); $i++)
            {
                $filename = 'rating-'.Str::uuid().'.jpg';
                $data['image'][$i]->move(public_path("/rating"),$filename);
                $imageURL = 'rating/'.$filename;
                $data_image = array(
                    'invoice_product_rating_id' => $request['product_id'],
                    'image'=>$imageURL
                );
                $response_image = Invoice_product_rating_image::create($data_image);
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!'
        ],201);
    }

    public function delete(Request $request){
        $request->validate([
            'target_id' => 'required|exists:invoice_product_rating_images,id'
        ]);

        $data = Invoice_product_rating_image::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => 0,
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
            'status' => 1,
            'message' => 'Resource deleted!'
        ],200);
    }
}
