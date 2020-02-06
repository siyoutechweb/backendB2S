<?php namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiyouCommission;
use App\Models\User;
use App\Models\Role;

class SiyouCommissionsController extends Controller {

    public function addCommission(Request $request){
        $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $commission=new SiyouCommission();
        $commission->supplier_id=$request->input('choose Supplier');
        $commission->commission_percent=$request->input('put commission percent');
        $commission->deposit=0;
        $commission->save();
        return response()->json(["msg"=>"commission added successfuly"],200);
        }
        return response()->json(["msg"=>"ERROR"],500);
    }
    public function updateCommission($id, Request $request)
    {    $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $commission = SiyouCommission::findOrFail($id);
        $commission->commission_percent = $request->input('put commission percent');
        $commission->Deposit=$request->input('put the deposit amount');
        $commission->save();
            return response()->json(["msg" => "success!!"],200);
        }
        return response()->json(["msg" => "error!!"],500);
    }
    public function DeleteCommission($id)
    {
        $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $commission = SiyouCommission::findorfail($id);
        $commission->delete();
        return response()->json("Commission has been Deleted Successfully !");
        }
        return response()->json("ERROR !!");
    }
    public function GetCommission($id)
    {
        $siyoucommission = SiyouCommission::with('user')->where('id', $id)->get();
        return response()->json($siyoucommission);
    }
    public function GetsupplierCommission()
    {
        $supplier=AuthController::me();
        $siyoucommission = SiyouCommission::with('user')->where('supplier_id', $supplier->id)->get();
        return response()->json($siyoucommission);
    }
    public function GetCommissionlist()
    {
        $siyoucommission_list = SiyouCommission::with('user')->get();
        return response()->json($siyoucommission_list);
    }
    public function UpdateDeposit(Request $request,$supplier_id)
    {
        $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $siyoucommission = SiyouCommission::findorfail($supplier_id);
        $siyoucommission->deposit=$request->input('Deposit Amount');
        $user->save();
        return response()->json(['msg' => 'Supplier Deposit has been Updated'], 200);
    }
         return response()->json(['msg'=>'ERROR!'],500);
    
}
}
