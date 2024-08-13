<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use View;
use Session;
use Redirect;
use App\Order;
use App\Seller;
use Modules\Cargo\Entities\BusinessSetting;
use App\CustomerPackage;
use App\SellerPackage;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CommissionController;
use Auth;


use App\Http\Controllers\Controller;

class InstamojoController extends Controller
{

    public function __construct()
    {
        //
    }

   public function pay($shipment){

    $paymentSettings = resolve(\Modules\Payments\Entities\PaymentSetting::class)->toArray();
    $instamojo_payment = json_decode($paymentSettings['instamojo_payment'], true);

    $IM_API_KEY     = $instamojo_payment['IM_API_KEY'] ?? '';
    $IM_AUTH_TOKEN = $instamojo_payment['IM_AUTH_TOKEN'] ?? '';
    $INSTAMOJO_MODE   = $instamojo_payment['instamojo_sandbox'] == 1 ? 'sandbox' : 'live';

        if($INSTAMOJO_MODE == 'sandbox'){
               // testing_url
            $endPoint = 'https://test.instamojo.com/api/1.1/';

        }
        else{
                // live_url
                $endPoint = 'https://www.instamojo.com/api/1.1/';
        }

        $api = new \Instamojo\Instamojo(
                $IM_API_KEY,
                $IM_AUTH_TOKEN,
                $endPoint
            );

        //$shipment->client->responsible_mobile
        if(preg_match_all('/^(?:(?:\+|0{0,2})91(\s*[\ -]\s*)?|[0]?)?[789]\d{9}|(\d[ -]?){10}\d$/im', $shipment->client->responsible_mobile)){
        try {
            Session::put('order_id',$shipment->id);
            $response = $api->paymentRequestCreate(array(
                "purpose" => ucfirst(str_replace('_', ' ', $shipment->payment_type)),
                "amount" => round($shipment->tax + $shipment->shipping_cost + $shipment->insurance),
                "buyer_name" => $shipment->client->name,
                "send_email" => false,
                "email" => $shipment->client->email,
                "phone" => "01128512941",
                "redirect_url" => url('instamojo/payment/pay-success')
            ));

            return redirect($response['longurl']);

        }catch (Exception $e) {
            print('Error: ' . $e->getMessage());
        }
        }
        else{
            return redirect()->back()->with(['error_message_alert' => __('view.Invalid_phone_number')]);
        }

 }

// success response method.
 public function success(Request $request){

    $paymentSettings = resolve(\Modules\Payments\Entities\PaymentSetting::class)->toArray();
    $instamojo_payment = json_decode($paymentSettings['instamojo_payment'], true);

    $IM_API_KEY     = $instamojo_payment['IM_API_KEY'] ?? '';
    $IM_AUTH_TOKEN = $instamojo_payment['IM_AUTH_TOKEN'] ?? '';
    $INSTAMOJO_MODE   = $instamojo_payment['instamojo_sandbox'] == 1 ? 'sandbox' : 'live';

    try {

        if($INSTAMOJO_MODE == 'sandbox'){
              // testing_url
            $endPoint = 'https://test.instamojo.com/api/1.1/';
        }
        else{
               // live_url
            $endPoint = 'https://www.instamojo.com/api/1.1/';
        }

        $api = new \Instamojo\Instamojo(
            $IM_API_KEY,
            $IM_AUTH_TOKEN,
            $endPoint
        );

        $response = $api->paymentRequestStatus(request('payment_request_id'));

        if(!isset($response['payments'][0]['status']) ) {
            return redirect()->route('home')->with(['error_message_alert' => __('view.payment_failed')]);
            return redirect()->route('home');
        } else if($response['payments'][0]['status'] != 'Credit') {
            return redirect()->route('home')->with(['error_message_alert' => __('view.payment_failed')]);
        }
    }catch (\Exception $e) {
        return redirect()->route('home')->with(['error_message_alert' => __('view.payment_failed')]);
    }

    $payment = json_encode($response);
        $checkoutController = new CheckoutController;
        return $checkoutController->checkout_done( null, $payment, Session::get('order_id'));
  }

}