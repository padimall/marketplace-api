<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Invoices_product_rating;
use App\Invoice_product_rating_image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helper\Helper;

class InvoicesProductRatingController extends Controller
{


    public $helper;

    public function __construct(){
        $this->helper = new Helper();
    }

    public function censored(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:invoices_product_ratings,id',
            'reason' => 'required|string'
        ]);

        $data = Invoices_product_rating::find($request['target_id']);
        $data->censored_at = Carbon::now();
        $data->censored_reason = $request['reason'];
        if($data->save())
        {
            return response()->json([
                'status' => $this->helper->REQUEST_SUCCESS,
                'message' => 'Rating censored!'
            ],200);
        }
        else {
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Request failed!'
            ],200);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_product_id' => 'required|exists:invoices_products,id',
            'star' => 'required|integer',
            'description' => 'required|string',
            'show_name' => 'required|integer',
            'image.*'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $checkStatus = DB::table('invoices_products')
                        ->join('invoices','invoices.id','=','invoices_products.invoice_id')
                        ->where('invoices_products.id',$request['invoice_product_id'])
                        ->select('invoices.*')
                        ->first();

        if($checkStatus->status != 3)
        {
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Request failed, status not supported!'
            ],200);
        }

        if(isset($request['image']) && !is_array($request['image'])){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Use image[] instead of image!'
            ],200);
        }

        $exist = Invoices_product_rating::where('invoice_product_id',$request['invoice_product_id'])->first();

        if(!is_null($exist))
        {
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Rating exist!'
            ],200);
        }

        $data = $request->all();
        $data['name'] = request()->user()->name;

        $response = Invoices_product_rating::create($data);

        if(!is_null($request['image']))
        {
            $array_image = $data['image'];
            for($i=0; $i<sizeOf($array_image); $i++)
            {
                $filename = 'rating-'.Str::uuid().'.jpg';
                $data['image'][$i]->move(public_path("/rating"),$filename);
                $imageURL = 'rating/'.$filename;
                $data_image = array(
                    'invoice_product_rating_id' => $response['id'],
                    'image'=>$imageURL
                );

                $response_image = Invoice_product_rating_image::create($data_image);
            }
        }

        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Rating created!'
        ],201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:invoices_product_ratings,id',
        ]);

        $data = Invoices_product_rating::find($request['target_id']);

        if($data->censored_at != NULL)
        {
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Your rating was censored!'
            ],200);
        }

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
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Rating updated!'
        ],200);
    }
}
