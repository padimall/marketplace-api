<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Invoices_product_rating;

class InvoicesProductRatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'invoice_product_id' => 'required|exists:invoices_products,id',
            'star' => 'required|integer',
            'description' => 'required|string',
            'show_name' => 'required|integer',
            'image.*'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);

        if(isset($request['image']) && !is_array($request['image'])){
            return response()->json([
                'status' => 0,
                'message' => 'Use image[] instead of image!'
            ],200);
        }

        $data = $request->all();
        $data->name = request()->user()->name;

        $response = Invoices_product_rating::create($data);

        return response()->json([
            'status' => 1,
            'message' => 'Rating created!'
        ],201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:invoices_product_ratings,id',
        ]);

        $data = Invoices_product_rating::find($request['target_id']);

        if(!is_null($request['star'])){
            $request->validate([
                'star' => 'required|integer'
            ]);
            $data->star = $request['star'];
        }

        if(!is_null($request['show_name'])){
            $request->validate([
                'show_name' => 'required|integer'
            ]);
            $data->show_name = $request['show_name'];
        }

        if(!is_null($request['description'])){
            $request->validate([
                'description' => 'required|string'
            ]);
            $data->description = $request['description'];
        }

        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Rating updated!'
        ],200);
    }
}
