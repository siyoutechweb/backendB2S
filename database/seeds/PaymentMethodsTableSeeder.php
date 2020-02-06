<?php

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentmethod = new PaymentMethod();
        $paymentmethod->name = 'Credit Card';
        $paymentmethod->description = 'add new creditcard';
        $paymentmethod->save();
        $paymentmethod = new PaymentMethod();
        $paymentmethod->name = 'Debit Card';
        $paymentmethod->description = 'add new debit card';
        $paymentmethod->save();
        $paymentmethod = new PaymentMethod();
        $paymentmethod->name = 'Bank Transfers';
        $paymentmethod->description = 'A bank transfer to pay for online purchases.';
        $paymentmethod->save();
        $paymentmethod = new PaymentMethod();
        $paymentmethod->name = 'Direct Deposit';
        $paymentmethod->description = 'banks pull funds out of customer account to complete online payment';
        $paymentmethod->save();
        $paymentmethod = new PaymentMethod();
        $paymentmethod->name = 'chaque';
        $paymentmethod->description = 'A payment cheque is a document, written and signed by a customer';
        $paymentmethod->save();

        
       
    }
}
