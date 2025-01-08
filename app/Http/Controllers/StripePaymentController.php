<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\PaymentIntent;
class StripePaymentController extends Controller
{
    //show the form
    public function showForm(){
        return view('stripe_payment');
    }

    //Handle stripe payment
    public function handlePayment(Request $request){
        // dd($request->all());

        //set secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try{
            //create a payment intent with amount and currency
            $paymentIntent = PaymentIntent::create([
                'amount' => 5000,
                'currency'=>'usd',
                'payment_method_data' => [
                'type' => 'card',
                'card' => [
                    'token' => $request->payment_method_id, // The Stripe token
                ]
            ],
                'confirmation_method'=>'manual',
                'confirm'=>true,
                'return_url' => route('payment.success'),
            ]);

            // dd($paymentIntent);
            //send the client secret to the frontend
            return response()->json([
                'client_secret'=>$paymentIntent->client_secret,
                'payment_intent' => $paymentIntent->id,
                'success_url' => route('payment.success')
            ]);
            
        } catch (ApiErrorException $e){
            return response()->json([
                'error' => $e->getMessage() 
            ],500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        // dd($request->all());

        // You can retrieve the PaymentIntent ID from the query parameters if needed
        $paymentIntentId = $request->query('payment_intent');
        
        // Retrieve the payment intent details to confirm the payment status
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

       
        if ($paymentIntent->status == 'succeeded') {
            return view('success', ['paymentIntent' => $paymentIntent]);
        }

        return view('failed');
    }

public function paymentFailed(Request $request){
    // Handle the payment failure
    return view('payment.failed');
}
}
