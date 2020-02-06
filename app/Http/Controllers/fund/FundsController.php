<?php namespace App\Http\Controllers\fund;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Models\Fund;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class FundsController extends Controller {

public function FundsList(){
    $supplier=AuthController::me();
    $funds = Fund::where('supplier_id', $supplier->id)
    ->with('paymentmethods')
    ->get();
        return response()->json($funds);
    }

}

