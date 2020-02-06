<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Statut;
use App\Models\Tarif;
use App\Models\Fund;
use App\Models\SiyouCommission;
use App\Models\User;
use App\Models\Supplier_Salesmanager_ShopOwner;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{


    public function addOrder(Request $request)
    {
        $shop_owner = AuthController::me();
        $order = new Order();
        $supplier_id = $request->input('supplier_id');
        $shop_owner_id = $shop_owner->id;
        $order->shop_owner_id = $shop_owner_id;
        $statut = Statut::where('statut_name', 'waiting for supplier validation')->first();
        $order->order_price = $request->input('order_total_price');
        $order_weight = $request->input('order_weight');
        $payment_method_id = $request->input('Select Payment Method');
        $order_date = Carbon::now();
        $company_id = $request->input('company_id');
        $order->company_id=$company_id;
        $order->payment_method_id=$payment_method_id;
        $order->order_weight=$order_weight;
        $order->order_date=$order_date;
        $order->supplier_id = $supplier_id;
        $order->statut_id = $statut->id;
        $orderProductsList = $request->input('order_products_list');
        $tarif=Tarif::where('company_id',$order->company_id)
          ->where('kg',$order->order_weight)->value('price');
          $order->shipping_price=$tarif;
          if (($order->payment_method_id = 3)||($order->payment_method_id = 5)){
        $fund=new Fund();
        $required_date = $request->input('put the Payment date');
        $fund->supplier_id=$supplier_id;
        $fund->shop_owner_id=$shop_owner->id;
        $fund->payment_date=$required_date;
        $fund->amount=$order->order_price;
        $fund->payment_method_id=$order->payment_method_id;
        $fund->save();
        }
        try {
            if ($this->ifSalesmanager($supplier_id, $shop_owner_id)) {
                $commission = new Commission();
                $commission->save();
                if ($order->commissions()->associate($commission)->save()) {
                    foreach ($orderProductsList as $item) {
                        $order->products()->attach($item['product_id'], ['quantity' => $item['product_quantity']]);
                    }
                }
            } else {
                if ($order->save()) {
                    foreach ($orderProductsList as $item) {
                        $order->products()->attach($item['product_id'], ['quantity' => $item['product_quantity']]);
                    }
                }
            }
            return response()->json(["msg" => "Order has been added Successfully !"], 200);
        } catch (Exception $error) {
            return response()->json(['msg' => $error], 500);
        }
    }

    public function ifSalesmanager($supplier_id, $shop_owner_id)
    {
        $salesmanager = DB::table('supplier_salesmanager_shop_owner')
            ->where([
                ['shop_owner_id', '=', $shop_owner_id],
                ['supplier_id', '=', $supplier_id]
            ])
            ->count();
        if ($salesmanager > 0) {
            return true;
        } else {
            return false;
        }
    }


    // public function ifSalesmanager(Request $request)
    // {
    //     $order = new Order();
    //     $supplier = AuthController::me();
    //     $shop_owner_id = $request->input('shop_owner_id');
    //     $salesmanager = DB::table('supplier_salesmanager_shop_owner')
    //         ->where([
    //             ['shop_owner_id', '=', $shop_owner_id],
    //             ['supplier_id', '=', $supplier->id]
    //         ])
    //         ->count();
    //         // echo $salesmanager;
    //     if ($salesmanager > 0) {
    //         $commission = new Commission();
    //         $order->order_price = 12;
    //         $order->supplier_id = $supplier->id;
    //         $order->shop_owner_id = $shop_owner_id;
    //         $order->statut_id = 1;
    //         $commission->save();
    //         // $order->save();
    //         $order->commissions()->associate($commission)->save();
    //     }
    // }

    public function getShopOwnerOrderList()
    {
        $shop_owner = AuthController::me();
        $orderList = Order::where('shop_owner_id', $shop_owner->id)
            ->with('supplier')
            ->with('statut')
            ->with('products')
            ->get();
        return response()->json($orderList, 200);
    }

    public function getSupplierOrderList()
    {
        $resultData = [];
        $supplier = AuthController::me();
        $orderList = Order::where('supplier_id', $supplier->id)
            ->with('shopOwner')
            ->with('commissions')
            ->with('statut')
            ->with('products')
            ->get();
        foreach ($orderList as $order) {
            $commission_amount = $this->getSalesmanagerCommissionAmount($order->supplier_id, $order->shop_owner_id);
            $commission_value = ($order->order_price * $commission_amount) / 100;
            $order['salesmanager_commission_percentage'] = $commission_amount;
            $order['commission_value'] = $commission_value;
            $resultData[] = $order;
        
        }
        return response()->json($resultData, 200);
    }

    public function getSalesmanagerCommissionAmount($supplier_id, $shop_owner_id)
    {
        $result = DB::table('supplier_salesmanager_shop_owner')
            ->select('commission_amount')
            ->where([
                ['shop_owner_id', '=', $shop_owner_id],
                ['supplier_id', '=', $supplier_id]
            ])
            ->first();
        return $result->commission_amount;
    }

    public function getSalesmanagerOrderList()
    {
        $salesmanager = AuthController::me();
        // echo($salesmanager->id);
        $supplierList = DB::table('supplier_salesmanager_shop_owner')
            ->select('supplier_id')
            ->where('salesmanager_id', $salesmanager->id)
            ->get();
        $supplierList = $salesmanager->suppliers;
        $suppliersIds = [];
        foreach ($supplierList as $supplier) {
            $suppliersIds[] = $supplier->id;
        }
        $orderList = Order::has('commissions')
            ->with('commissions')
            ->with('supplier')
            ->with('shopOwner')
            ->with('products')
            ->with('statut')
            ->whereIn('supplier_id', $suppliersIds)->get();
        return response()->json($orderList, 200);
        // $data = $salesmanager->suppliers;
        // foreach ($data as $elem) {
        //     echo $elem->orders;
        // }
        // return response()->json($salesmanager->suppliers()
        //     ->with('orders')
        //     ->with('orders.commissions')->get(), 200);
    }

    public function updateOrderStatus(Request $request, $order_id)
     {
        $status = $request->input('status');
        $statut = null;
        // $order_id = $request->input('id');
        $order = Order::find($order_id);
        switch ($status) {
            case 'confirm':
                $this->calculateCommissionValue($order);
                // $this->calculatesiyouCommissionValue($order);
                $statut = Statut::where('statut_name', 'validated by supplier')->first();
                break;
            case 'rejected':
                $statut = Statut::where('statut_name', 'rejected by supplier')->first();
                break;
        }
        $order->statut_id = $statut['id'];
        if ($order->save()) {
            return response()->json(["msg" => "Order Updated Succeffully !"], 200);
        }
        return response()->json(["msg" => "Error while updating Order !"], 500);
    }

    public function calculateCommissionValue($order)
    {                                   
        $result = DB::table('supplier_salesmanager_shop_owner')
            ->select('commission_amount')
            ->where([
                ['supplier_id', '=', $order->supplier_id],
                ['shop_owner_id', '=', $order->shop_owner_id]
            ])->first();
            $result1 = DB::table('siyoucommission')
            ->select('commission_percent')
            ->where('supplier_id', '=', $order->supplier_id
            )->first();
        $commission_amount = $result->commission_amount;
        $siyoucommission_percent=$result1->commission_percent;
        $commission_value = ($order->order_price * $commission_amount) / 100;
        $siyoucommission_value = ($order->order_price * $siyoucommission_percent) / 100;
        $commission = Commission::where('id', $order->commission)
            ->update(['commission_amount' => $commission_value]);
       $siyoucommission=SiyouCommission::where('supplier_id',$order->supplier_id)->first();
       $newsiyoucommission=$siyoucommission->replicate();
       $newsiyoucommission->commission_amount=$siyoucommission_value;
       $newsiyoucommission->Deposit_rest=$newsiyoucommission->Deposit-$siyoucommission_value;
       $newsiyoucommission->order_id=$order->id;
       $newsiyoucommission->save();
    }
 
}
