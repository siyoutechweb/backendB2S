<?php

namespace App\Models\Traits;

use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier_Salesmanager_ShopOwner;

trait Supplier
{
    public function suppliers()
    {
        return $this->belongsToMany(User::class, 'supplier_salesmanager_shop_owner', 'salesmanager_id', 'supplier_id')->withPivot(['shop_owner_id', 'commission_amount'])->withTimestamps();
    }

    // public function getShopsThroughSalesManager()
    // {
    //     return $this->hasManyThrough(Supplier_Salesmanager_ShopOwner::class, Supplier_Salesmanager_ShopOwner::class, 'supplier_id', 'id', 'id', 'salesmanager_id');
    // }

    public function getShopsThroughOrder()
    {
        return $this->hasManyThrough(User::class, Order::class, 'supplier_id', 'id', 'id', 'shop_owner_id');
    }

    public function getSupplierCategoryThroughProduct() {
        return $this->hasManyThrough(Category::class, Product::class, 'supplier_id', 'id', 'id', 'category_id');
    }
}
