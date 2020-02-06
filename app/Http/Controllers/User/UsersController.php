<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Supplier_Salesmanager_ShopOwner;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

// use Tymon\JWTAuth\JWTAuth;

class UsersController extends Controller
{

    // const MODEL = "App\Users";

    // use RESTActions;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['addSupplier','addShop_Owner','addSalesManager']]);
    }
    public function account(){
        $user = AuthController::me();
        $user=user::where('id',$user->id)->with('role')->get();
        return response()->json($user);

}
    public function getInvalidUsers(Request $request)
    {
        $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $invalidList = user::where('validation', Null)->get();
        return response()->json($invalidList, 200);
        }
        return response()->json(['msg'=>'ERROR'],500);
    }

  
    public function validateUser($user_id)
    { $superadmin=AuthController::me();
        if ($superadmin->hasRole('Super_Admin')) {
        $user = user::findorfail($user_id);
        $user->validation = 1;
        $user->save();
        return response()->json(['msg' => 'user account has been validated'], 200);
        }
        return response()->json(['msg'=>'ERROR!'],500);
    }
    public function blockUser($user_id)
    { $superadmin=AuthController::me();
        if ($superadmin->hasRole('Super_Admin')) {
        $user = user::findorfail($user_id);
        $user->validation = 0;
        $user->save();
        return response()->json(['msg' => 'user has been blocked'], 200);
        }
        return response()->json(['msg'=>'ERROR!'],500);
    }
    public function addSupplier(Request $request)
    {
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $password = $request->input('password');
        $user = new User();
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $role = Role::where('name', 'Supplier')->first();
        $role->users()->save($user);
        $user->save();
        
        return response()->json(["msg" => "user added successfully !"], 200);

        return response()->json(["msg" => "ERROR !"], 500);
    }

    public function addShop_Owner(Request $request)
    {
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $password = $request->input('password');
        $user = new User();
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $role = Role::where('name', 'Shop_Owner')->first();
        $role->users()->save($user);
        $user->save();
        return response()->json(["msg" => "user added successfully !"], 200);

        return response()->json(["msg" => "ERROR !"], 500);
    }

    public function addSalesManager(Request $request)
    {
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User();
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->password = Hash::make($password);

        $role = Role::where('name', 'salesmanager')->first();
        $role->users()->save($user);
        $user->save();
        return response()->json(["msg" => "user added successfully !"], 200);

        return response()->json(["msg" => "Error !"], 500);
    }

    public function getSupplierOrderShop()
    {
        // if (!$user = JWTAuth::parseToken()->authenticate()) {
        //     return response()->json(["msg" => 'user_not_found'], 404);
        // }
        // $data = compact('user');
        // $supplier = User::where('id', $data['user']['id'])->first();
        $supplier = AuthController::me();
        return response()->json($supplier->getShopsThroughOrder()->distinct()->with('salesmanagerToShop')->distinct('salesmanagerToShop')->get(), 200);
    }

    public function getSupplierSalesmanagerShop()
    {
        $supplier = AuthController::me();
        // echo $supplier;
        // return response()->json($supplier->salesmanagerToSupplier()->get());
        $responseData = [];
        $data = $supplier->salesmanagerToSupplier()
            ->with(['shopOwners' => function ($query) use ($supplier) {
                $query->wherePivot('supplier_id', $supplier->id)->distinct();
            }])->distinct()->get();
        foreach ($data as $element) {
            // echo $element;
            if (sizeof($element['shopOwners'])) {
                $result = DB::table('supplier_salesmanager_shop_owner')
                    ->select('commission_amount')
                    ->where([
                        ['salesmanager_id', '=', $element['id']],
                        ['shop_owner_id', '=', $element['shopOwners'][0]->id],
                        ['supplier_id', '=', $supplier->id]
                    ])
                    ->first();
            } else {

                $result = DB::table('supplier_salesmanager_shop_owner')
                    ->select('commission_amount')
                    ->where([
                        ['salesmanager_id', '=', $element['id']],
                        // ['shop_owner_id', '=', $element['shopOwners'][0]->id],
                        ['supplier_id', '=', $supplier->id]
                    ])
                    ->first();
            }
            $element['commission_amount'] = $result->commission_amount;
            $responseData[] = $element;
        }
        return $responseData;
        // return response()->json($supplier->salesmanagerToSupplier()
        // ->with(['shopOwners' => function ($query) use ($supplier) {
        //     $query->wherePivot('supplier_id', $supplier->id)->distinct();
        // }])->distinct()->get());
    }

    public function addSalesManagerToSupplier(Request $request)
    {
        $supplier = AuthController::me();
        $salesManagerId = $request->input('salesmanager_id');
        $salesmanager = User::where('id', $salesManagerId)->first();
        $oldCount = $supplier->salesmanagerToSupplier()->count();
        $supplier->salesmanagerToSupplier()->attach($salesmanager);
        $newCount = $supplier->salesmanagerToSupplier()->count();
        return $newCount > $oldCount ? response()->json(["msg" => "All Is Good"], 200) : response()->json(["msg" => "Error"], 500);
    }

    public static function getUserByEmail($email)
    {
        return User::whereEmail($email)
            ->with('role')->first();
    }

    public function getSalesManagerByEmail(Request $request)
    {
        $supplier = AuthController::me();
        $email = $request->input('email');
        $supplierSalesmanagerList = $supplier->salesmanagerToSupplier()->distinct()->get();
        $salesManagerEmails = array();
        foreach ($supplierSalesmanagerList as $element) {
            $salesManagerEmails[] = $element['email'];
        }
        $salesmanagerList = User::whereNotIn('email', $salesManagerEmails)
            ->where('email', $email)
            ->whereHas('role', function ($q) {
                $q->where('name', 'SalesManager');
            })->get();
        return response()->json($salesmanagerList, 200);
    }


    public function getShopOwnerByEmail(Request $request)
    {
        // $supplier = AuthController::me();
        $email = $request->input('email');
        $shopsIds = $request->input('shopsIds');
        $shopList = User::where('email', $email)
            ->whereNotIn('id', $shopsIds)
            ->whereHas('role', function ($query) {
                $query->where('name', 'Shop_Owner')->distinct();
            })->get();
        return response()->json($shopList, 200);
    }

    public function linkSalesManagerToShop(Request $request)
    {
        $supplier = AuthController::me();
        $salesManagerId = $request->input('salesmanager_id');
        $shop_owner_id = $request->input('shop_owner_id');
        $commission_amount = $request->input('commission_amount');
        // $shop_owner = User::find($shop_owner_id);
        // $salesmanager = User::find($salesManagerId)->first();
        $row = Supplier_Salesmanager_ShopOwner::where([
            "supplier_id" => $supplier['id'],
            "salesmanager_id" => $salesManagerId,
            "shop_owner_id" => null
        ])->first();
        if ($row) {
            $row->shop_owner_id = $shop_owner_id;
            $row->commission_amount = $commission_amount;
            if ($row->save()) {
                return response()->json(["msg" => 'data updated'], 200);
            }
            return response()->json(["msg" => 'erreur while updating'], 500);
        }
        return response()->json(['msg' => 'no data found'], 404);
    }

    public function getSalesManagerList()
    {
        $supplier = AuthController::me();
        return response()->json($supplier->salesmanagerToSupplier);
    }
    

    public function getSupplierList()
    {
        $supplierList = User::whereHas('role', function ($query) {
            $query->where('name', '=', 'Supplier');
        })->get();
        return response()->json($supplierList, 200);
    }
    public function getShopsList()
    {
        $shoplist = User::whereHas('role', function ($query) {
            $query->where('name', '=', 'Shop_Owner ')->orwhere('name','=','Shop_Manager');
        })->get();
        return response()->json($shoplist, 200);
    }
    public function getSalesManagersList()
    {
        $SMList = User::whereHas('role', function ($query) {
            $query->where('name', '=', 'SalesManager');
        })->get();
        return response()->json($SMList, 200);
    }
    public function deleteUser($id)
    {
        $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $user = User::find($id);
        $user->delete();
        return response()->json(["msg" => "the user has been deleted successfully !!"]);
        }
        return response()->json(['msg'=>'ERROR',500]);
    }

    public function ShowUser($id)
    {
        $user = User::find($id);
        return response()->json($user);
    }

    public function UsersList()
    {
        $userlist = User::all();
        return response()->json($userlist);
    }


    public function GetUserByRole($id)
    {
        $user_role = User::where('role_id', $id)->get();
        return response()->json($user_role);
    }


    public function updateSalesmanagerCommission(Request $request)
    {
        $user=AuthController::me();
        if ($user->hasRole('Supplier')) {
        $shop_owner_id = $request->input('shop_owner_id');
        $salesmanager_id = $request->input('salesmanager_id');
        $supplier_id = $user->id;
        $commission_amount = $request->input('commission_amount');
        $updateRow = DB::table('supplier_salesmanager_shop_owner')
            ->where([
                ['salesmanager_id', '=', $salesmanager_id],
                ['shop_owner_id', '=', $shop_owner_id],
                ['supplier_id', '=', $supplier_id]
            ])
            ->update(['commission_amount' => $commission_amount]);
        if ($updateRow) {
            return response()->json(["msg" => "update Success ! "], 200);
        }
    }
        return response()->json(["msg" => "Update Error"]);
    }

}
