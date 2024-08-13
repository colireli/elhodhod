<?php

namespace Modules\Cargo\Http\Controllers;

use App\Models\User;
use Modules\Cargo\Entities\Branch;
use Modules\Cargo\Entities\Client;
use Modules\Cargo\Entities\Exports\CompanyPaymentExport;
use Modules\Cargo\Entities\ClientPayment;
use Modules\Cargo\Entities\Shipment;
use Modules\Cargo\Entities\UploadedPayment;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use Storage;
use Str;
use Maatwebsite\Excel\Facades\Excel;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use ZipArchive;
use Auth;

class RapPayController extends Controller
{

    /**
     * @return Renderable
     */
    public function index()
    {
        $client_id = $_GET['client'] ?? abort(404);
        $user = auth()->user();
        $client = Client::findOrFail($client_id);
        if (auth()->check() && auth()->user()->role == 1) {
        } elseif (auth()->check() && auth()->user()->role == 3) {
            if (Branch::where('user_id',auth()->user()->id)->pluck('id')->first() != $client->branch_id) {
                abort(401);
            }
        } else {
            abort(401);
        }
        if (ClientPayment::where('client_id', $client->id)->where('picked', false)->count() > 0) {
            return redirect()->back()->with(['error_message_alert' => __('Please Confirm Old Payments')]);
        }
        $shipments = Shipment::query()
            ->where('paid_to_company', true)
            ->where('client_id', $client->id)
            ->where('paid_to_client', 0)
            ->get();
        $wants     = Shipment::query()
            ->where('paid_to_company', false)
            ->where('client_id', $client->id)
            ->where('paid_to_client', 0)
            ->where('final_status', '!=', 0)
            ->get();

        $client_id = $client->id;

        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.rappayment.index', compact('shipments', 'wants', 'client_id'));
        dd($client);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $client = Client::findOrFail($request->client);

        // if (auth()->check() && auth()->user()->role == 1) {
        //     $branch_id = $client->branch_id;
        // }elseif (auth()->check() && auth()->user()->role == 3) {

        //     $branch_id = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
        // }else{
        //     abort(401);
        // }

        if (auth()->check() && auth()->user()->role != 3) {
            abort(401);
        }
        $branch_id = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();

        $branch = Branch::findOrFail($branch_id);

        if (ClientPayment::where('client_id', $client->id)->where('picked', false)->count() > 0) {
            return redirect()->back()->with(['error_message_alert' => __('Please Confirm Old Payments')]);
        }

        if (!isset($request->checked_ids) || count($request->checked_ids ?? []) == 0) {
            return redirect()->back()->with(['error_message_alert' => __('Please select shipments')]);
        }
        $ids = $request->checked_ids;
        if (Shipment::where('client_id', '!=', $request->client)->whereIn('id', $ids)->count() > 0) {
            abort(401);
        }
        if (auth()->check() && auth()->user()->role == 3) {
            if (Shipment::where('branch_id', '!=', $branch_id)->whereIn('id', $ids)->count() > 0) {
                abort(401);
            }
        }
        $shipments = Shipment::find($ids);
        if ($shipments->where('final_status', 0)->count() > 0) {
            foreach ($shipments->where('final_status', 0)->pluck('code') as $er)
                $err .= $er . ' ' .  __('Should be Delivered or Returned\r\n');

            return redirect()->back()->with(['error_message_alert' => __($err)]);
        }
        if ($shipments->where('paid_to_client', 1)->count() > 0) {
            foreach ($shipments->where('paid_to_client', 1)->pluck('code') as $er)

            return redirect()->back()->with(['error_message_alert' => __('Shipment already uploaded')]);
        }
        if ($shipments->where('client_payment_id', '!=', null)->count() > 0) {
            return redirect()->back()->with(['error_message_alert' => __('Something went wrong with payment')]);
        }
        $new = $newData = [];
        $delivered = $returned = $total_net = $total_charge = $total_recovered = 0;
        foreach ($shipments as $shipment) {
            $new['track']              = $shipment->track ?? $shipment->track;
            $new['elhodhod_code']      = $shipment->code;
            $new['invoce_id']           = $shipment->order_id;
            $new['status']             = $shipment->final_status;
            $new['state']              = $shipment->to_state->name;
            $new['area']               = $shipment->to_area->name;
            $new['client']             = $shipment->reciver_name;
            $new['phone']              = $shipment->reciver_phone;
            $new['insurance_per']      = $shipment->insurance;
            $new['delivery_type']      = ($shipment->delivery_type == 1) ? 'home' : 'desk';
            $new['shipping_type']      = ($shipment->payment_type  == 2) ? 'free' : 'paid';
            if ($new['status'] == 1) {
                if($shipment->payment_type == 1){
                    $new['recovered_amount']    = $shipment->amount_to_be_collected + $shipment->shipping_cost;
                }else{
                    $new['recovered_amount']    = $shipment->amount_to_be_collected;
                }
                $new['shipping']            = $shipment->shipping_cost;
                $new['insurance']           = ($shipment->insurance * $shipment->amount_to_be_collected) / 100;
                $delivered++;
            } else {
                $new['recovered_amount']    = 0;
                $new['shipping']            = $shipment->return_cost;
                $new['insurance']           = 0;
                $returned++;
            }
            $new['status'] = $shipment->getFinalStatus();
            $shipment->save();

            $new['tax'] = $shipment->tax;
            $new['charge_fee']  = $new['shipping'] + $new['insurance'] + $shipment->tax;
            $new['net']         = $new['recovered_amount'] - $new['charge_fee'];

            $total_recovered    += $new['recovered_amount'];
            $total_charge       += $new['charge_fee'];
            $total_net          += $new['net'];



            $newData[] = array_map(function ($value) {
                return $value === 0 ? '0' : $value;
            }, $new);
        }
        $head = array_keys($new);
        $suffix = $client->name . '_' . now() . '_';
        $pdf_name = $suffix . Str::uuid()->toString() . '.pdf';
        $xlsx_name = $suffix . Str::uuid()->toString() . '.xlsx';

        try {
            DB::beginTransaction();
            $payment = ClientPayment::create([
                'user_id'   => $user->id,
                'client_id' => $request->client,
                'ip'        => $request->ip(),
                'net'       => $total_net,
                'collected' => $total_recovered,
                'charged'   => $total_charge,
                'delivered' => $delivered,
                'returned'  => $returned,
                'xls_file'  => $xlsx_name,
                'pdf_file'  => $pdf_name,
            ]);
            $code = '';
            for ($n = 0; $n < 7; $n++) {
                $code .= '0';
            }
            $code =   substr($code, 0, -strlen($payment->id));
            $payment->code =   'CUS-PAY-' . $code . $payment->id;
            $payment->save();

            Shipment::whereIn('id', $ids)->update([
                'paid_to_client'    => true,
                'client_payment_id' => $payment->id,
            ]);

            if (!Excel::store(new CompanyPaymentExport($newData, $head), $xlsx_name, 'payments')) {
                return redirect()->back()->with(['error_message_alert' => __('File can`t be stored')]);
            }

            $xls_file = storage_path('app/payments/') . $xlsx_name;
            $adminTheme = env('ADMIN_THEME', 'adminLte');
            $pdf = Pdf::loadView(
                'cargo::'.$adminTheme.'.pages.rappayment.payment_shipments',
                [
                    'data' => [
                        'code'          => $payment->code,
                        'delivered'     => $delivered,
                        'returned'      => $returned,
                        'collected'     => $total_recovered,
                        'charged'       => $total_charge,
                        'net'           => $total_net,
                        'branch'        => $branch->name,
                        'store'         => $client->name,
                        'client_code'   => $client->id,
                        'phone'         => User::find($client->user_id)->phone ?? '',
                    ]

                ]
            );
            if (!Storage::disk('payments')->put($pdf_name, $pdf->output())) {
                return redirect()->back()->with(['error_message_alert' => __('File can`t be stored')]);
            }
            $pdf_file = storage_path('app/payments/') . $pdf_name;
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            if (Storage::disk('payments')->exists($xlsx_name)) {
                Storage::disk('payments')->delete($xlsx_name);
            }
            if (Storage::disk('payments')->exists($pdf_name)) {
                Storage::disk('payments')->delete($pdf_name);
            }
            return redirect()->back()->with(['error_message_alert' => __('Something went wrong when updating data!')]);
        }

        $zip = new ZipArchive();
        $zipFileName = $payment->created_at . '__' . $payment->code . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

            $zip->addFile($xls_file, basename($xls_file));
            $zip->addFile($pdf_file, basename($pdf_file));


            $zip->close();
            flash(__('Payment Uploaded With Success'))->success();
            return $this->clientPayments($client->id);
        } else {
            return redirect()->back()->with(['error_message_alert' => __('Something went wrong while creating archive!')]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function clientPayments($client)
    {
        if(auth()->user()->role == 4 || auth()->user()->role == 3 || auth()->user()->role == 1){

            $client = Client::findOrFail($client);
            $user = auth()->user();
            if (auth()->check() && auth()->user()->role == 3) {
                if ($client->branch_id != Branch::where('user_id',auth()->user()->id)->pluck('id')->first()) {
                    abort(401);
                }
            }elseif (auth()->user()->role == 4) {
                $client_id = Client::where('user_id',auth()->user()->id)->first()->id ;
                if ($client->id != $client_id) {
                        abort(401);
                }
            }

        }else{
            abort(401);
        }
        $payments = ClientPayment::orderBy('id', 'DESC')->where('client_id', $client->id)->paginate(20);
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.rappayment.client', compact('payments', 'client'));
    }

/**
 * @return Renderable
 */

    public function xlsPaymentClient($payment)
    {
        $user = auth()->user();
        $payment = ClientPayment::findOrFail($payment);
        if (auth()->check() && auth()->user()->role == 1) {
        } elseif (auth()->check() && auth()->user()->role == 3) {
            $client = Client::findOrFail($payment->client_id);
            if ($client->branch_id != Branch::where('user_id',auth()->user()->id)->pluck('id')->first()) {
                abort(404);
            }
        } elseif ($payment->client_id != auth()->user()->id) {
                abort(401);
            }

        return Storage::disk('payments')->download($payment->xls_file, $payment->created_at . '__' . $payment->code . '.xlsx');
    }
/**
 * @return Renderable
 */
    public function pdfPaymentClient($payment)
    {
        $user = auth()->user();
        $payment = ClientPayment::findOrFail($payment);
        if (auth()->check() && auth()->user()->role == 1) {
        } elseif (auth()->check() && auth()->user()->role == 3) {
            $client = Client::findOrFail($payment->client_id);
            if ($client->branch_id != Branch::where('user_id',auth()->user()->id)->pluck('id')->first()) {
                abort(404);
            }
        } elseif ($payment->client_id != Client::where('user_id',auth()->user()->id)->pluck('id')->first()) {
                abort(401);
        }

        return Storage::disk('payments')->download($payment->pdf_file, $payment->created_at . '__' . $payment->code . '.pdf');
    }

    function getPicked($payment) {
        $user = auth()->user();

            $payment = ClientPayment::findOrFail($payment);
            $client = Client::findOrFail($payment->client_id);
            if ($payment->client_id != $client->id) {
                abort(401);
            }
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.rappayment.confirm-ajax', compact('payment'));
    }
/**
 * @return Renderable
 */
    public function picked($payment)
    {
        $user = auth()->user();
        $payment = ClientPayment::findOrFail($payment);
        $client = Client::findOrFail($payment->client_id);
        if ($payment->client_id != $client->id) {
            abort(401);
        }
        if ($payment->picked) {
            return redirect()->back()->with(['error_message_alert' => __('Payment Already Picked')]);
        }
        $payment->picked = true;
        $payment->save();
        return redirect()->back()->with(['message_alert' => __('Payment Picked with success')]);

    }

/**
 * @return Renderable
 */
    public function importDelivery()
    {
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.rappayment.import_delivery_case');

    }
/**
 * @return Renderable
 */
    public function importPayment()
    {
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.rappayment.import');
    }

/**
 * @return Renderable
 */
    public function ImportPaymentSub(Request $request)
    {
        $user = auth()->user();
        $err = "";
        if (auth()->check() && auth()->user()->role != 3) {
            abort(401);
        }


        $branch_id = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
        $request->validate([
            'file' => ['required', 'mimes:xls,xlsx,Xls,XLS,Xlsx,XLSX'],
        ]);
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if ($ext == 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }
        $spreadsheet     = $reader->load($request->file('file'));
        $sheet_data     = $spreadsheet->getActiveSheet()->toArray();
        $keys = array_shift($sheet_data);
        $diff = array_diff(['Tracking', 'status'], $keys);
        if (count($diff) > 0) {
            return redirect()->back()->with(['error_message_alert' => __('Please verify the field: ' . array_values($diff)[0])]);

        }
        $finalData = [];
        $tracks = [];
        foreach ($sheet_data as $row) {
            // dd($row);
            $fin = array_combine($keys, $row);
            // $fin['Total'] = (float)str_replace(',', '', $fin['Total']);
            // $fin['Frais livraison'] = (float)str_replace(',', '', $fin['Frais livraison']);
            if (isset($fin["Tracking"]) && $fin["Tracking"] !== null && !empty(trim($fin["Tracking"]))) {
                $tracks[] = $fin["Tracking"];
                $fin['status'] = strtolower($fin['status']);
                $stats[] = $fin['status'];
                $finalData[] = $fin;
            }
        }
        $diff_status = array_diff($stats, ['livre', 'retour']);
        if (!empty($diff_status)) {
            foreach ($diff_status as $stat) {

                $err .= $stat . ' ' .  __(' not valid!\r\n');
            }
            return redirect()->back()->with(['error_message_alert' => __($err)]);

        }

        $res = Shipment::whereIn('track', $tracks)->where('paid_to_company', 0)->select('track', 'branch_id')->get();
        $notFound = array_diff($tracks, $res->pluck('track')->toArray());
        $notBranch = array_diff($res->pluck('branch_id')->unique()->toArray(), [$branch_id]);
        if (!empty($notFound)) {
            foreach ($notFound as $not) {
                $err .= $not . ' ' .  __(' not found or already uploaded\r\n');
            }
            return redirect()->back()->with(['error_message_alert' => __($err)]);
        }
        if (!empty($notBranch)) {
            $notBranch = $res->whereIn('branch_id', $notBranch)->pluck('track');
            foreach ($notBranch as $not) {
                $err .= $not . ' ' .  __(' belongs to other branch\r\n');
            }
            return redirect()->back()->with(['error_message_alert' => __($err)]);

        }
        $shipments = Shipment::whereIn('track', $tracks)->select(['id', 'code', 'track', 'amount_to_be_collected', 'shipping_cost', 'company_cost', 'shipping_to_company', 'return_to_company', 'recovery_to_company', 'payment_type','company'])->get();
        $new = $newData = [];
        $delivered = $returned = $total_recovered = $total_charge = $total_net = 0;

        foreach ($finalData as $data) {
            $shipment = Shipment::where('track', $data['Tracking'])->first();
            $new['track']               = $data['Tracking'];
            $new['elhodhod_code']       = $shipment->code;
            $new['invoce_id']           = $shipment->order_id;
            $new['company_id']          = $shipment->company;
            $new['status']              = $data['status'];
            $new['state']               = $shipment->to_state->name;
            $new['area']                = $shipment->to_area->name;
            $new['insurance_per']       = $shipment->recovery_to_company;
            $new['delivery_type']       = ($shipment->delivery_type == 1) ? 'home' : 'desk';
            $new['shipping_type']       = ($shipment->payment_type  == 2) ? 'free' : 'paid';
            if ($new['status'] == 'livre') {
                if($shipment->payment_type == 1){
                    $new['recovered_amount'] = $shipment->amount_to_be_collected + $shipment->shipping_cost;
                }else{
                    $new['recovered_amount'] = $shipment->amount_to_be_collected;
                }
                $new['shipping'] = $shipment->shipping_to_company;
                $new['insurance'] = ($shipment->recovery_to_company * $shipment->amount_to_be_collected) / 100;
                $shipment->final_status = 1;
                $shipment->status_id = Shipment::DELIVERED_STATUS;
                $shipment->client_status = Shipment::CLIENT_STATUS_DELIVERED;
                $shipment->situation = 'Delivered';
                $delivered++;
            } else {
                $new['recovered_amount'] = 0;
                $new['shipping'] = $shipment->return_to_company;
                $new['insurance'] = 0;
                $shipment->final_status = 2;
                $shipment->status_id = Shipment::RETURNED_STATUS;
                $shipment->client_status = Shipment::CLIENT_STATUS_RECEIVED_BRANCH;
                $shipment->situation = 'Returned';
                $returned++;
            }
            $shipment->save();
            $new['tax'] = $shipment->tax;
            $new['charge_fee'] = $new['shipping'] + $new['insurance'] + $shipment->tax;
            $new['net'] = $new['recovered_amount'] - $new['charge_fee'];


            $total_recovered += $new['recovered_amount'];
            $total_charge += $new['charge_fee'];
            $total_net += $new['net'];
            $company_id = $new['company_id'];

            $newData[] = array_map(function ($value) {
                return $value === 0 ? '0' : $value;
            }, $new);
        }
        $head = array_keys($new);
        $suffix = $user->name . '_' . now() . '_';
        $xlsx_name = $suffix . Str::uuid()->toString() . '.xlsx';

        if (!Excel::store(new CompanyPaymentExport($newData, $head), $xlsx_name, 'payments')) {

            return redirect()->back()->with(['error_message_alert' => __('File can`t be stored')]);
        }


        $xls_file = storage_path('app/payments/') . $xlsx_name;
        $adminTheme = env('ADMIN_THEME', 'adminLte');

        $pdf = PDF::loadView(

                'cargo::'.$adminTheme.'.pages.rappayment.payment_branch',
                [
                    'data' => [
                        'delivered' => $delivered,
                        'returned' => $returned,
                        'collected' => $total_recovered,
                        'charged' => $total_charge,
                        'net' => $total_net,
                    ]

                ]
            );
            $pdf_name = $suffix . Str::uuid()->toString() . '.pdf';
            if (!Storage::disk('payments')->put($pdf_name, $pdf->output())) {

                return redirect()->back()->with(['error_message_alert' => __('File can`t be stored')]);

            }
            $pdf_file = storage_path('app/payments/') . $pdf_name;


        try {
            DB::beginTransaction();
            $payment = UploadedPayment::create([
                'user_id' => $user->id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
                'ip' => $request->ip(),
                'net' => $total_net,
                'collected' => $total_recovered,
                'charged' => $total_charge,
                'delivered' => $delivered,
                'returned' => $returned,
                'xls_file' => $xlsx_name,
                'pdf_file' => $pdf_name,
            ]);
            $code = '';
            for ($n = 0; $n < 7; $n++) {
                $code .= '0';
            }
            $code =   substr($code, 0, -strlen($payment->id));
            $payment->code =   'PAYMENT' . $code . $payment->id;
            $payment->save();

            Shipment::whereIn('track', $tracks)->update([
                'paid_to_company' => true,
                'uploaded_payment_id' => $payment->id,
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            if (Storage::disk('payments')->exists($xlsx_name)) {
                Storage::disk('payments')->delete($xlsx_name);
            }
            if (Storage::disk('payments')->exists($pdf_name)) {
                Storage::disk('payments')->delete($pdf_name);
            }
            return redirect()->back()->with(['error_message_alert' => __('Something went wrong when updating data!')]);
          }


            $zip = new ZipArchive();
            $zipFileName = $payment->created_at . '__' . $payment->code . '.zip';
            $zip = new ZipArchive();

            if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

                $zip->addFile($xls_file, basename($xls_file));
                $zip->addFile($pdf_file, basename($pdf_file));


                $zip->close();
                flash(__('Payment Uploaded With Success'))->success();
                return response()->download($zipFileName)->deleteFileAfterSend(true);
            } else {
                return redirect()->back()->with(['error_message_alert' => __('Something went wrong while creating archive!')]);

            }

    }

/**
* @return Renderable
*/

public function pdfPayment($payment)
    {
        $user = auth()->user();
        if (auth()->check() && auth()->user()->role == 1) {
            $payment = UploadedPayment::findOrFail($payment);
        } elseif (auth()->check() && auth()->user()->role == 3) {
            $payment = UploadedPayment::where('branch_id', Branch::where('user_id',auth()->user()->id)->pluck('id')->first())->where('id', $payment)->first();
            if (!$payment) {
                abort(404);
            }
        } else {
            abort(401);
        }
        return Storage::disk('payments')->download($payment->pdf_file, $payment->code . '.pdf');
    }
/**
* @return Renderable
*/
    public function xlsPayment($payment)
    {
        $user = auth()->user();
        if (auth()->check() && auth()->user()->role == 1) {
            $payment = UploadedPayment::findOrFail($payment);
        } elseif (auth()->check() && auth()->user()->role == 3) {
            $payment = UploadedPayment::where('branch_id', Branch::where('user_id',auth()->user()->id)->pluck('id')->first())->where('id', $payment)->first();
            if (!$payment) {
                abort(404);
            }
        } else {
            abort(401);
        }
        return Storage::disk('payments')->download($payment->xls_file, $payment->code . '.xlsx');
    }
/**
* @return Renderable
*/
    public function uploadedPayments(Request $request)
    {
        $user = auth()->user();
        if (auth()->check() && auth()->user()->role == 1) {
            $payments = UploadedPayment::orderBy('id', 'DESC')->paginate(20);
        } elseif (auth()->check() && auth()->user()->role == 3) {
            $payments = UploadedPayment::where('branch_id', Branch::where('user_id',auth()->user()->id)->pluck('id')->first())->orderBy('id', 'DESC')->paginate(20);
        } else {
            abort(401);
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.rappayment.uploaded', compact('payments'));
    }
/**
* @return Renderable
*/
    public function importCase(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:xls,xlsx,Xls,XLS,Xlsx,XLSX'],
        ]);
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if ($ext == 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }
        $spreadsheet     = $reader->load($request->file('file'));
        $sheet_data     = $spreadsheet->getActiveSheet()->toArray();
        $keys = array_shift($sheet_data);
        $diff = array_diff(['TrackColi', 'Tracking', 'Situation'], $keys);
        if (count($diff) > 0) {
            return redirect()->back()->with(['error_message_alert' => __('Please verify the field:') . ' ' . array_values($diff)[0]]);
        }
        $finalData = [];
        foreach ($sheet_data as $row) {
            $finalData[] = array_combine($keys, $row);
        }

        $msgFailed = [];
        $msgSuccess = [];

        $codes = array_column($finalData, 'TrackColi');
        $shipments = Shipment::whereIn('code', $codes)->where('final_status', 0)->select('id', 'code')->get();
        foreach ($finalData as $row) {
            $data = [
                'situation' => $row['Situation'],
                'track' => $row['Tracking'],
            ];
            if (!empty(trim($row['TrackColi']))) {
                if ($shipments->where('code', trim($row['TrackColi']))->count() > 0) {
                    $sh = Shipment::where('code', trim($row['TrackColi']))->first();
                    if ($sh->track != null && $sh->track != $data['track']) {
                        $msgFailed[] = $row['TrackColi'] . ' | ' . $row['Tracking'] . ' Tracking error ';
                    } else {
                        try {
                            Shipment::where('code', $row['TrackColi'])->update($data);
                            $msgSuccess[] = $row['TrackColi'] . ' | ' . $row['Tracking'] . ' updated ';
                        } catch (\Throwable $th) {
                            $msgFailed[] = $row['TrackColi'] . ' | ' . $row['Tracking'] . ' Tracking already exist! ';
                        }
                    }

                } else {
                    $msgFailed[] = $row['TrackColi'] . ' | ' . $row['Tracking'] . ' does not exist or already  or have a tracking! ';
                }
            }
        }
        foreach ($msgSuccess as $msg) {
            flash($msg)->success();
        }
        foreach ($msgFailed as $msg) {
            flash($msg)->error();
        }
        return back();


        //dd($sheet_data);
    }

}
