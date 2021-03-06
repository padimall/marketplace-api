<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Helper\Helper;

class AgentController extends Controller
{
    public $helper;

    public function __construct(){
        $this->helper = new Helper();
    }

    public function showAll()
    {
        $data = Agent::all();
        if(sizeOf($data)==$this->helper->EMPTY_ARRAY){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
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

        $data = Agent::inRandomOrder()->limit($request['limit'])->get();
        if(sizeOf($data)==$this->helper->EMPTY_ARRAY){
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

    public function detail_id(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:agents,id'
        ]);

        $data = Agent::where('id',$request['target_id'])->first();

        if(is_null($data)){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Resource not found!'
            ],200);
        }

        $supplier = DB::table('suppliers')
                    ->join('agents_affiliate_suppliers','agents_affiliate_suppliers.supplier_id','=','suppliers.id')
                    ->where('agents_affiliate_suppliers.agent_id',$data['id'])
                    ->select('suppliers.*')
                    ->get();

        if(sizeof($supplier)!=$this->helper->EMPTY_ARRAY)
        {
            for($i=0; $i<sizeof($supplier); $i++){
                if(!is_null($supplier[$i]->image))
                {
                    $supplier[$i]->image = url('/').'/'.$supplier[$i]->image;
                }
            }
        }

        $data['supplier'] = $supplier;

        if(!is_null($data['image']))
        {
            $data['image']=url('/').'/'.$data['image'];
        }

        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function show(Request $request)
    {
        $data = Agent::where('user_id',request()->user()->id)->first();

            if(is_null($data)){
                return response()->json([
                    'status' => $this->helper->REQUEST_FAILED,
                    'message' => 'You are not an agent!'
                ],200);
            }

        $supplier = DB::table('suppliers')
                    ->join('agents_affiliate_suppliers','agents_affiliate_suppliers.supplier_id','=','suppliers.id')
                    ->where('agents_affiliate_suppliers.agent_id',$data['id'])
                    ->select('suppliers.*')
                    ->get();

        if(sizeof($supplier)!=$this->helper->EMPTY_ARRAY)
        {
            for($i=0; $i<sizeof($supplier); $i++){
                if(!is_null($supplier[$i]->image))
                {
                    $supplier[$i]->image = url('/').'/'.$supplier[$i]->image;
                }
            }
        }

        $data['supplier'] = $supplier;

        if(!is_null($data['image']))
        {
            $data['image']=url('/').'/'.$data['image'];
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
            'name' => 'required',
            'phone' => 'required|unique:agents,phone',
            'address' => 'required|string',
            'image'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $supplierExist = Supplier::where('user_id',request()->user()->id)->first();

        if(!is_null($supplierExist)){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Supplier Exist!'
            ],422);
        }

        $agentExist = Agent::where('user_id',request()->user()->id)->first();

        if(!is_null($agentExist)){
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'Agent Exist!'
            ],422);
        }

        $random = strtolower(Str::random(5));
        while(Agent::where('agent_code',$random)->first()){
            $random = strtolower(Str::random(5));
        }

        $data = $request->all();
        $data['user_id'] = request()->user()->id;
        $data['agent_code'] = $random;
        $data['status'] = $this->helper->AGENT_ACTIVE;

        if(!is_null($request['image']))
        {
            $filename = 'agent-'.Str::uuid().'.jpg';
            $request->file('image')->move(public_path("/agent"),$filename);
            $imageURL = 'agent/'.$filename;
            $data['image'] = $imageURL;
        }

        $response = Agent::create($data);
        return response()->json([
            'status' => $this->helper->REQUEST_SUCCESS,
            'message' => 'Resource created!'
        ],201);
    }

    public function update_status(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:agents,id',
            'status' => 'required|between:0,1|integer'
        ]);

        $data = Agent::where('id',$request['target_id'])->first();

        if($request['status'] == $this->helper->AGENT_DEACTIVE || $request['status'] == $this->helper->AGENT_ACTIVE)
        {
            if($request['status'] == $this->helper->AGENT_ACTIVE){
                $data->status = $this->helper->AGENT_ACTIVE;

                $data->save();

                $product = DB::table('products')
                            ->where('agent_id',$data->id)
                            ->update(['status' => $this->helper->AGENT_ACTIVE,'updated_at' => Carbon::now()]);

                return response()->json([
                    'status' => $this->helper->REQUEST_SUCCESS,
                    'message' => 'Agent activated! Products shown!'
                ],200);
            }
            else if($request['status'] == $this->helper->AGENT_DEACTIVE)
            {
                $data->status = $this->helper->AGENT_DEACTIVE;
                $data->save();

                $product = DB::table('products')
                            ->where('agent_id',$data->id)
                            ->update(['status' => $this->helper->AGENT_DEACTIVE,'updated_at' => Carbon::now()]);

                return response()->json([
                    'status' => $this->helper->REQUEST_SUCCESS,
                    'message' => 'Agent de-activated! Products hidden!'
                ],200);
            }
        }
        else {
            return response()->json([
                'status' => $this->helper->REQUEST_FAILED,
                'message' => 'status is 0 or 1'
            ],200);
        }

    }

    public function update(Request $request)
    {

        $data = Agent::where('user_id',request()->user()->id)->first();

        if(!is_null($request['name'])){
            $request->validate([
                'name' => 'required'
            ]);
            $data->name = $request['name'];
        }

        if(!is_null($request['phone'])){
            $request->validate([
                'phone' => 'required|unique:agents,phone'
            ]);
            $data->phone = $request['phone'];
        }

        if(!is_null($request['address'])){
            $request->validate([
                'address' => 'required|string'
            ]);
            $data->address = $request['address'];
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

            $filename = 'agent-'.Str::uuid().'.jpg';
            $request->file('image')->move(public_path("/agent"),$filename);
            $imageURL = 'agent/'.$filename;

            $data->image = $imageURL;
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

    public function delete($id){
        $data = Agent::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}
