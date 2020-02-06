<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;

class CommissionsController extends Controller
{

    public function addCommission(Request $request)
    {
        $commission = new Commission();
        $commission->commission_percent = $request->input('commission_percent');
        $commission->shop_owner_id = $request->input('shop_owner_id');
        $commission->supplier_id = $request->input('supplier_id');
        $commission->sales_manager_id = $request->input('sales_manager_id');
        if ($commission->save()) {
            return response()->json(["msg" => "success!!"]);
        }
        return response()->json(["msg" => "error!!"]);
    }

    public function updateCommission($id, Request $request)
    {
        $commission = Commission::findOrFail($id);
        $commission->commission_percent = $request->input('commission_percent');
        if ($commission->save()) {
            return response()->json(["msg" => "success!!"]);
        }
        return response()->json(["msg" => "error!!"]);
    }
    public function DeleteCommission($id)
    {
        $commission = Commission::findorfail($id);
        $commission->delete();
        return response()->json("Commission has been Deleted Successfully !");
        return response()->json("ERROR !!");
    }
    public function GetCommissionbySupplier($id)
    {

        $sales_manager_list = User::with('commissions')->where('role_id', 1)->where('supplier_id', $id)->get();
        return response()->json($sales_manager_list);
    }
}
