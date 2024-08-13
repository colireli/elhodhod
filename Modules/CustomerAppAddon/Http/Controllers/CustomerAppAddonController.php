<?php

namespace Modules\CustomerAppAddon\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mail;
use App\Mail\EmailManager;
use Illuminate\Support\Facades\Log;
use Image;
use Storage;
use Illuminate\Support\Facades\File;
use App\Models\AppRequest;
use Illuminate\Support\Facades\Http;

class CustomerAppAddonController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $ch = curl_init();
        $headers = array("Content-Type => application/json", "Accept: */*");

        $params = ['addon' => 'customerappaddon'];
        $url = rtrim('https://addons.bdaia.com/api/app-requests',"?") . "?" . http_build_query($params);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $app_request = curl_exec($ch);
        // print_r($server_output);
        // exit();
        curl_close($ch);

        $app_request = json_decode($app_request);

        return view('customerappaddon::settings', compact('app_request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('customerappaddon::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('customerappaddon::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('customerappaddon::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function sendCustomerAppInfo(Request $request){

        if (env('DEMO_MODE') == 'On') {
            return redirect()->back()->with(['error_message_alert' => __('view.demo_mode')]);
        }

        $request->validate([
            'application_icon' => 'required|mimes:jpg,png,jpeg,gif,svg|dimensions:width=1024,height=1024',
            'application_splash_screen' => 'required|mimes:jpg,png,jpeg,gif,svg|dimensions:width=512,height=512',
            'small_notifications_icon' => 'required|mimes:jpg,png,jpeg,gif,svg|dimensions:width=20,height=20',
            'google_service_android' => 'required|mimes:json',
            'google_service_ios' => 'required|mimes:plist,json',
        ]);

        $array['view'] = 'emails.view';
        $array['subject'] = "Add New App ".$request->app_name;
        if (!filter_var(env('MAIL_USERNAME'), FILTER_VALIDATE_EMAIL)) {
            $array['from'] = env('MAIL_FROM_ADDRESS');
        }else{
            $array['from'] = env('MAIL_USERNAME');
        }

        if ($request->hasFile('application_icon')){
            $image = $request->file('application_icon');
            $application_icon_file_name = "app_".time().'.'.$image->getClientOriginalExtension();
            Image::make($image)->save(base_path('public/storage/addons/') .$application_icon_file_name);
            $application_icon_src = env('APP_URL').'public/storage/addons/'.$application_icon_file_name;
        }

        if ($request->hasFile('application_splash_screen')){
            $image = $request->file('application_splash_screen');
            $application_splash_screen_file_name = "app_".time().'.'.$image->getClientOriginalExtension();
            Image::make($image)->save(base_path('public/storage/addons/') .$application_splash_screen_file_name);
            $application_splash_screen_src = env('APP_URL').'public/storage/addons/'.$application_splash_screen_file_name;
        }

        if ($request->hasFile('small_notifications_icon')){
            $image = $request->file('small_notifications_icon');
            $file_name = "app_".time().'.'.$image->getClientOriginalExtension();
            Image::make($image)->save(base_path('public/storage/addons/') .$file_name);
            $small_notifications_icon_src = env('APP_URL').'public/storage/addons/'.$file_name;
        }

        if ($request->hasFile('google_service_android')){
            $file = $request->file('google_service_android');
            $file_name = "app_android".time().'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/storage/addons/'), $file_name);
            $google_service_android_url = env('APP_URL').'public/storage/addons/'.$file_name;
        }

        if ($request->hasFile('google_service_ios')){
            $file = $request->file('google_service_ios');
            $file_name = "app_ios".time().'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/storage/addons/'), $file_name);
            $google_service_ios_url = env('APP_URL').'public/storage/addons/'.$file_name;
        }

        $array['content'] = [
            'Cargo Purchase Code' => $request->cargo_purchase_code,
            'Customer App Addon Purchase Code' => $request->addon_purchase_code,
            'App Name' => $request->app_name,
            'Application Icon' => $application_icon_src,
            'App Bundle ID' => $request->app_bundle_id,
            'Application splash screen background color' => $request->application_splash_screen_background,
            'Application splash screen' => $application_splash_screen_src,
            'Issuer ID' => $request->issuer_id,
            'Key ID' => $request->key_id,
            'Auth Key Content' => $request->auth_key_content,
            'Apple ID' => $request->apple_id,
            'Color of the small notificatios icon for android' => $request->small_notification_icon_color,
            'Small Notifications Icon' => $small_notifications_icon_src,
            'Android Google Service File' => $google_service_android_url,
            'IOS Google Service File' => $google_service_ios_url,
        ];

        try {

            Mail::to('cargo@spotlayer.com')->queue(new EmailManager($array));
        } catch (\Throwable $e) {
            dd($e->getMessage());
            Log::debug($e->getMessage());
        }


        $post = [

            'app_name' => $request->app_name,
            'app_status' => 'new',
            'cargo_purchase_code' => $request->cargo_purchase_code,
            'addon_purchase_code' => $request->addon_purchase_code,
            'app_logo' => $application_icon_file_name,
            'application_splash_screen' => $application_splash_screen_file_name,
            'user_id' => Auth()->user()->id,
            'user_email' => Auth()->user()->email,
            'module_name' => 'customerappaddon',
            'website_url' => env('APP_URL'),

        ];

        $ch = curl_init();
        $headers = array("Content-Type => application/json", "Accept: */*");


        curl_setopt($ch, CURLOPT_URL, 'https://addons.bdaia.com/api/send-app-request');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $server_output = curl_exec($ch);
        // print_r($server_output);
        // exit();
        curl_close($ch);

        return redirect()->back()->with(['message_alert' => __('cargo::view.an_email_has_been_sent')])->with('loader',true);;
    }
}
