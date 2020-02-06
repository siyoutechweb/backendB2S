<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCommissionsTable extends Migration
{

    public function up()
    {
        Schema::create('commissions', function(Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->float('commission_amount')->nullable();
            // $table->unsignedInteger('order_id');
            // $table->foreign('order_id')->references('id')->on('orders');
            // $table->float('commission_percent')->nullable();
            // $table->unsignedInteger('supplier_id');
            // $table->foreign('supplier_id')->references('id')->on('users');
            // $table->unsignedInteger('shop_owner_id');
            // $table->foreign('shop_owner_id')->references('id')->on('users');
            // $table->unsignedInteger('sales_manager_id');
            // $table->foreign('sales_manager_id')->references('id')->on('users');
            // Schema declaration
            // Constraints declaration

        });
    }

    public function down()
    {
        Schema::drop('commissions');
    }
}
