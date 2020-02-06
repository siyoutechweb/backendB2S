<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    // public function products() {
    //     return $this->belongsToMany(Product::class, 'product_order', 'order_id', 'product_id');
    // }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_order', 'order_id', 'product_id')->withPivot(['quantity'])->withTimestamps();
    }
    public function paymentmethods(){
        return $this->hasMany(PaymentMethod::class, 'payment_method_id');
    }
    // public function statuts() {
    //     return $this->belongsTo(Statut::class, 'statut_id');
    // }

    public function shopOwner() {
        return $this->belongsTo(User::class, 'shop_owner_id');
    }
    
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function shopOwners()
    {
        return $this->belongsTo(User::class, 'shop_owner_id');
    }

    public function commissions()
    {
        // return $this->hasOne(Commission::class);
        return $this->belongsTo(Commission::class, 'commission');
    }

    public function statut()
    {
        return $this->belongsTo(Statut::class, 'statut_id');
    }
}
