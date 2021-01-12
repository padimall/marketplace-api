<?php

namespace App\Http\Controllers;

use App\Agents_affiliate_supplier;
use Illuminate\Http\Request;

class AgentsAffiliateSupplierController extends Controller
{
    public $helper;

    public function __construct(){
        $this->helper = new Helper();
    }

    public function showAll()
    {
        $data = Agents_affiliate_supplier::all();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Resource not found!'
            ],200);
        }
        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function showLimit(Request $request)
    {
        $request->validate([
            'limit' => 'required'
        ]);

        $data = Agents_affiliate_supplier::inRandomOrder()->limit($request['limit'])->get();
        if(sizeOf($data)==0){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Resource not found!'
            ],200);
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

        $data = Agents_affiliate_supplier::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Resource not found!'
            ],200);
        }
        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'agent_id' => 'required|exists:agents,id'
        ]);

        $data = $request->all();
        $response = Agents_affiliate_supplier::create($data);
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

        $data = Agents_affiliate_supplier::find($request['target_id']);

        if(!is_null($request['supplier_id'])){
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id'
            ]);
            $data->supplier_id = $request['supplier_id'];
        }

        if(!is_null($request['agent_id'])){
            $request->validate([
                'agent_id' => 'required|exists:agents,id'
            ]);
            $data->agent_id = $request['agent_id'];
        }

        $data->save();
        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource updated!'
        ],200);
    }

    public function delete($id){
        $data = Agents_affiliate_supplier::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}
