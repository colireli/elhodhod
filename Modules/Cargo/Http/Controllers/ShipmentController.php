<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller;
use Modules\Cargo\Http\DataTables\ShipmentsDataTable;
use Modules\Cargo\Http\Requests\ShipmentRequest;
use Modules\Cargo\Entities\Shipment;
use Modules\Cargo\Entities\ShipmentSetting;
use Modules\Cargo\Entities\ClientPackage;
use Modules\Cargo\Entities\Client;
use Modules\Cargo\Entities\Package;
use Modules\Cargo\Entities\Cost;
use Modules\Cargo\Http\Helpers\ShipmentPRNG;
use Modules\Cargo\Http\Helpers\MissionPRNG;
use Modules\Cargo\Entities\PackageShipment;
use Modules\Cargo\Http\Helpers\ShipmentActionHelper;
use Modules\Cargo\Http\Helpers\StatusManagerHelper;
use Modules\Cargo\Http\Helpers\TransactionHelper;
use Modules\Cargo\Entities\Mission;
use Modules\Cargo\Entities\ShipmentMission;
use Modules\Cargo\Entities\ShipmentReason;
use Modules\Cargo\Entities\Country;
use Modules\Cargo\Entities\State;
use Modules\Cargo\Entities\Area;
use Modules\Cargo\Entities\Staff;
use Modules\Cargo\Entities\ClientAddress;
use Modules\Cargo\Entities\DeliveryTime;
use Modules\Cargo\Entities\Branch;
use Modules\Cargo\Entities\BusinessSetting;
use Modules\Cargo\Entities\PlanFee;
use Modules\Cargo\Entities\PlanAreaFee;
use Modules\Cargo\Entities\ApiModel;
use Modules\Cargo\Entities\Company;
use Modules\Cargo\Entities\CompanyPlanFee;
use Modules\Cargo\Utility\CSVUtility;
use DB;
use Str;
use Modules\Cargo\Http\Helpers\UserRegistrationHelper;
use app\Http\Helpers\ApiHelper;
use App\Models\User;
use Modules\Cargo\Events\AddShipment;
use Modules\Cargo\Events\CreateMission;
use Modules\Cargo\Events\ShipmentAction;
use Modules\Cargo\Events\UpdateMission;
use Modules\Cargo\Events\UpdateShipment;
use Modules\Acl\Repositories\AclRepository;
use Modules\Cargo\Http\Controllers\ClientController;
use Modules\Cargo\Http\Requests\RegisterRequest;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Modules\Cargo\Entities\Exports\CompanyPaymentExport;
use Maatwebsite\Excel\Facades\Excel;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

use Auth;
use Carbon\Carbon;

class ShipmentController extends Controller
{
    private $aclRepo;

    public function __construct(AclRepository $aclRepository)
    {
        $this->aclRepo = $aclRepository;
        // check on permissions
        $this->middleware('user_role:1|0|3|4')->only('index', 'shipmentsReport' ,'create');
        $this->middleware('user_role:4')->only('ShipmentApis');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(ShipmentsDataTable $dataTable , $status = 'all' , $type = null)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.shipments')
            ]
        ]);
        $actions = new ShipmentActionHelper();
        if($status == 'all'){
            $actions = $actions->get('all');
        }else{
            $actions = $actions->get($status, $type);
        }

        $data_with = ['actions'=> $actions , 'status' => $status];
        $share_data = array_merge(get_class_vars(ShipmentsDataTable::class), $data_with);
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return $dataTable->render('cargo::'.$adminTheme.'.pages.shipments.index', $share_data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.shipments'),
                'path' => fr_route('shipments.index')
            ],
            [
                'name' => __('cargo::view.add_shipment'),
            ],
        ]);

        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.shipments.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $order_id_validation = 'nullable|unique:shipments,order_id';
        $request->validate([
            'Shipment.type'            => 'required',
            'Shipment.branch_id'       => 'required',
            'Shipment.shipping_date'   => 'nullable',
            'Shipment.collection_time' => 'nullable',
            'Shipment.client_id'       => 'required',
            'Shipment.client_phone'    => 'required|min:5',
            'Shipment.follow_up_country_code'    => 'nullable',
            'Shipment.client_address'  => 'required',
            'Shipment.reciver_name'    => 'required|string|min:3|max:50',
            'Shipment.reciver_phone'   => 'required|min:9|max:10',
            'Shipment.country_code'    => 'nullable',
            'Shipment.reciver_address' => 'required|string|min:8',
            // 'Shipment.from_country_id' => 'required',
            // 'Shipment.to_country_id'   => 'required',
            // 'Shipment.from_state_id'   => 'required',
            'Shipment.to_state_id'     => 'required',
            // 'Shipment.from_area_id'    => 'required',
            'Shipment.to_area_id'      => 'required',
            'Shipment.payment_type'    => 'required',
            'Shipment.payment_method_id' => 'required',
            'Shipment.order_id'          => $order_id_validation,
            'Shipment.attachments_before_shipping' => 'nullable',
            'Shipment.amount_to_be_collected'      => 'required',
            // 'Shipment.delivery_time'    => 'nullable',
            'Shipment.total_weight'     => 'required',
        ]);

        // Calculating "delivery time"  for The shipment is automatic

            // Calculating "delivery time"  for The shipment is automatic
            if(!isset($request->Shipment['shipping_date'])){

                $defult_shipping_date = ShipmentSetting::getVal('def_shipping_date');
                if($defult_shipping_date == null )
                {
                    $shipping_data = Carbon::now()->format('d-m-Y');
                }else{
                    $shipping_data = Carbon::now()->addDays($defult_shipping_date)->format('d-m-Y');
                }
                $shippingDate = $shipping_data;
            }else{
                $shippingDate = $request->Shipment['shipping_date'];
            }

            $collectionTime = $request->Shipment['collection_time'];

            $shippingDate = date("d-m-Y", strtotime($shippingDate));
            $collectionTime = date("H:i:s", strtotime($collectionTime));
            $shippingDateTime = Carbon::parse($shippingDate . ' ' . $collectionTime);
            $currentDateTime = Carbon::now();

            $deliveryTime = $currentDateTime->diffForHumans($shippingDateTime, true);

            $request->merge(['Shipment' => array_merge($request->Shipment, ['delivery_time' => $deliveryTime])]);
            $request->merge(['Shipment' => array_merge($request->Shipment, ['delivery_type' => $request->Shipment['delivery_type']])]);
            $request->merge(['Shipment' => array_merge($request->Shipment, ['type' => $request->Shipment['type']])]);
        try {
            DB::beginTransaction();
                $model = $this->storeShipment($request);
                if(isset($request->image)){
                    $model->addFromMediaLibraryRequest($request->image)->toMediaCollection('attachments');
                }
                event(new AddShipment($model));
            DB::commit();
            return redirect()->route('shipments.show', $model->id)->with(['message_alert' => __('cargo::messages.created')]);
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    private function storeShipment($request , $token = null)
    {
        $model = new Shipment();
        $model->fill($request->Shipment);
        $model->code = -1;
        $model->status_id = Shipment::DRAFT_STATUS;
        $date = date_create();
        $today = date("Y-m-d");

        if(isset($token)){
            $user = User::where('remember_token', $token)->first();
            $userClient = Client::where('user_id',$user->id)->first();

            if(isset($user))
            {
                $model->client_id = $userClient->id;

                $model->branch_id = $userClient->branch_id;
                $addresse = ClientAddress::where('client_id',$userClient->id)->first();
                if($addresse){
                    $model->from_country_id = $addresse->country_id;
                    $model->to_country_id  = $addresse->country_id;
                    $model->from_state_id  = $addresse->state_id;
                    $model->from_area_id  = $addresse->area_id;
                }
                $defult_shipping_date = ShipmentSetting::getVal('def_shipping_date');
                if($defult_shipping_date == null )
                {
                    $shipping_data = Carbon::now()->format('d-m-Y');
                }else{
                    $shipping_data = Carbon::now()->addDays($defult_shipping_date)->format('d-m-Y');
                }
                $shippingDate = $shipping_data;

                $model->shipping_date = date("d-m-Y", strtotime($shippingDate));


                // Validation
                // if(!isset($request->Shipment['type']) || !isset($request->Shipment['branch_id']) || !isset($request->Shipment['shipping_date']) || !isset($request->Shipment['client_address']) || !isset($request->Shipment['reciver_name']) || !isset($request->Shipment['reciver_phone']) || !isset($request->Shipment['reciver_address']) || !isset($request->Shipment['to_state_id']) || !isset($request->Shipment['to_area_id']) || !isset($request->Shipment['payment_method_id']) || !isset($request->Shipment['payment_type']) || !isset($request->Package))
                // {
                //     $message = 'Please make sure to add all required fields';
                //     return $message;
                // }
                if(!isset($request->Shipment['reciver_name']) || !isset($request->Shipment['reciver_phone']) || !isset($request->Shipment['reciver_address']) || !isset($request->Shipment['to_state_id']) || !isset($request->Shipment['to_area_id']) || !isset($request->Package))
                {
                    $message = 'Please make sure to add all required fields';
                    return $message;
                }else {
                    if($request->Shipment['type'] != Shipment::POSTPAID && $request->Shipment['type'] != Shipment::PREPAID ){
                        $model->type = 1;
                    }

                    $model->to_area_id = Area::where('name' , ShipmentActionHelper::getClosestMatch($request->Shipment['to_area_id'],$userClient->id, $request->Shipment['to_state_id']))->where('state_id', $request->Shipment['to_state_id'])->pluck('id')->first();
                    if(!$model->to_area_id){
                        return  __('invalid area name') ;
                    }


                    // if(!ClientAddress::where('client_id',$userClient->id)->where('id',$request->Shipment['client_address'])->first() ){
                    //     return 'Invalid Client Address';
                    // }

                    // if(!Country::where('covered',1)->where('id',$request->Shipment['from_country_id'])->first() || !Country::where('covered',1)->where('id',$request->Shipment['to_country_id'])->first() ){
                    //     return 'Invalid Country';
                    // }

                    // if(!State::where('covered',1)->where('id',$request->Shipment['from_state_id'])->first() || !State::where('covered',1)->where('id',$request->Shipment['to_state_id'])->first() ){
                    //     return 'Invalid State';
                    // }

                    // if(!Area::where('state_id', $request->Shipment['from_state_id'])->where('id',$request->Shipment['from_area_id'])->first() || !Area::where('state_id', $request->Shipment['to_state_id'])->where('id',$request->Shipment['to_area_id'])->first() ){
                    //     return 'Invalid Area';
                    // }

                    if(isset($request->Shipment['payment_method_id'])){
                        $paymentSettings = resolve(\Modules\Payments\Entities\PaymentSetting::class)->toArray();
                      	if(!isset($paymentSettings[$request->Shipment['payment_method_id']])){
                            $model->payment_method_id = "cash_payment";
                        }
                    }else{
                        $model->payment_method_id = "cash_payment";
                    }

                    // if(isset($request->Shipment['delivery_time'])){
                    //     $delivery_time = DeliveryTime::where('id', $request->Shipment['delivery_time'] )->first();
                    //     if(!$delivery_time){
                    //         return 'Invalid Delivery Time';
                    //     }
                    // }

                }


                if(!isset($request->Shipment['type'])){
                    $model->type = 1;
                }
                if(!isset($request->Shipment['payment_type'])){
                    $model->payment_type = 2;
                }
                if(!isset($request->Shipment['delivery_type'])){
                    $model->delivery_type = 1;
                }


                if(!isset($request->Shipment['client_phone'])){
                    $model->client_phone = $userClient->responsible_mobile;
                }


                if(!isset($request->Shipment['amount_to_be_collected'])){
                    $model->amount_to_be_collected = 0;
                }

            }else{
                return response()->json(['message' => 'invalid or Expired Api Key' ] );
            }
        }

        if (!$model->save()) {
            return response()->json(['message' => new \Exception()] );
        }

        if(ShipmentSetting::getVal('def_shipment_code_type')=='random'){
            $barcode = ShipmentPRNG::get();
                }else{
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('shipment_code_count'); $n++){
                $code .= '0';
            }
            $code       =   substr($code, 0, -strlen($model->id));
            $barcode    =   $code.$model->id;
        }
        $model->barcode = $barcode;
        $model->code = ShipmentSetting::getVal('shipment_prefix').$barcode;

        if( auth()->user() && auth()->user()->role == 4 ){ // IF IN AUTH USER == CLIENT
            $client = Client::where('user_id',auth()->user()->id)->first();
            $model->client_id = $client->id;
        }

        if (!$model->save()) {
            return response()->json(['message' => new \Exception()] );
        }

        $costs = $this->applyShipmentCost($model,$request->Package);

        $model->fill($costs);
        if (!$model->save()) {
            return response()->json(['message' => new \Exception()] );
        }

        $counter = 0;
        if (isset($request->Package)) {
            if (!empty($request->Package)) {

                if (isset($request->Package[$counter]['package_id'])) {

                    if(isset($token))
                    {
                        $total_weight = 0;
                    }

                    foreach ($request->Package as $package) {
                        if(isset($token))
                        {
                            if(!Package::find($package['package_id'])){
                              	$package['package_id'] = 2 ;
                               // return 'Package invalid';
                            }

                            if(!isset($package['qty'])){
                                $package['qty'] = 1;
                            }
                            if(!isset($package['description'])){
                                $package['description'] = 'product';
                            }
                            if(!isset($package['weight'])){
                                $package['weight'] = 1;
                            }
                            if(!isset($package['length'])){
                                $package['length'] = 1;
                            }
                            if(!isset($package->width)){
                                $package['width'] = 1;
                            }
                            if(!isset($package['height'])){
                                $package['height'] = 1;
                            }

                            $total_weight = $total_weight + $package['weight'];
                        }
                        $package_shipment = new PackageShipment();
                        $package_shipment->fill($package);
                        $package_shipment->shipment_id = $model->id;
                        if (!$package_shipment->save()) {
                            throw new \Exception();
                        }
                    }

                    if(isset($token))
                    {
                        $model->total_weight = $total_weight;
                        if (!$model->save()) {
                            return response()->json(['message' => new \Exception()] );
                        }
                    }
                }
            }
        }

        // $this->createExternColi($model,$request->Package);

        if(isset($token))
        {
            $model->status_id = Shipment::SAVED_STATUS;
            if (!$model->save()) {
                return response()->json(['message' => new \Exception()] );
            }
            $this->createExternColi($model,$request->Package);
            $message = 'Shipment added successfully';
            return response()->json(['code' => $model->code , 'order_id' => $model->order_id, 'message' => $message]);
        }else {
            return $model;
        }
    }

    public function storeAPI(Request $request)
    {
        try {
            $apihelper = new ApiHelper();
            $user = $apihelper->checkUser($request);

            if($user){
                DB::beginTransaction();
                    $message = $this->storeShipment($request , $request->header('token'));
                DB::commit();
                return response()->json(['message' => $message ] );
            }else{
                return response()->json(['message' => 'Not Authorized'] );
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }
    public function getShipmentsAPI(Request $request)
    {
        try {
            $apihelper = new ApiHelper();
            $user = $apihelper->checkUser($request);

            if($user){
                $userClient = Client::where('user_id',$user->id)->first();
                $shipments = new Shipment();

                $shipments = $shipments->where('client_id',$userClient->id);
                if (isset($request->code) && !empty($request->code)) {
                    $shipments = $shipments->where('code', $request->code);
                }
                if (isset($request->type) && !empty($request->type)) {
                    $shipments = $shipments->where('type', $request->type);
                }
                $shipments = $shipments->with(['pay','from_address'])->orderBy('client_id')->orderBy('id','DESC')->paginate(20);
                return response()->json($shipments);
            }else{
                return response()->json(['message' => 'Not Authorized'] );
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    public function show($id)
    {
        $shipment = Shipment::find($id);
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.shipments'),
                'path' => fr_route('shipments.index')
            ],
            [
                'name' => __('cargo::view.shipment') .' | '.$shipment->code,
            ],
        ]);
        if($shipment->track){
            $statut = $this->getStatutExternColi($shipment->company,$shipment->track) ;
            $activity = json_encode(json_decode($statut['response']), JSON_PRETTY_PRINT);
            $shipment['activity'] =  $activity  ;
            $shipment['statut_activity'] =  $statut['Situation']  ;

        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.shipments.show', compact('shipment'));
    }

    public function edit($id)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.shipments'),
                'path' => fr_route('shipments.index')
            ],
            [
                'name' => __('cargo::view.edit_shipment'),
            ],
        ]);
        $item = Shipment::findOrFail($id);
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.shipments.edit')->with(['model' => $item]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'Shipment.type'            => 'required',
            'Shipment.branch_id'       => 'required',
            'Shipment.shipping_date'   => 'nullable',
            'Shipment.collection_time' => 'nullable',
            'Shipment.client_id'       => 'required',
            'Shipment.client_phone'    => 'required|min:5',
            'Shipment.country_code'    => 'nullable',
            'Shipment.client_address'  => 'required',
            'Shipment.reciver_name'    => 'required|string|min:3|max:50',
            'Shipment.reciver_phone'   => 'required|min:9|max:10',
            'Shipment.follow_up_country_code'   => 'nullable',
            'Shipment.reciver_address' => 'required|string|min:8',
            // 'Shipment.from_country_id' => 'required',
            // 'Shipment.to_country_id'   => 'required',
            // 'Shipment.from_state_id'   => 'required',
            'Shipment.to_state_id'     => 'required',
            // 'Shipment.from_area_id'    => 'required',
            'Shipment.to_area_id'      => 'required',
            'Shipment.payment_type'    => 'required',
            'Shipment.payment_method_id' => 'required',
            'Shipment.order_id'          => 'nullable',
            'Shipment.attachments_before_shipping' => 'nullable',
            'Shipment.amount_to_be_collected'      => 'required',
            'Shipment.delivery_time'    => 'nullable',
            'Shipment.total_weight'     => 'required',
            'Shipment.tax'           => 'nullable',
            'Shipment.insurance'     => 'nullable',
            'Shipment.shipping_cost' => 'nullable',
            'Shipment.return_cost'   => 'nullable',
        ]);

        try {
            DB::beginTransaction();
            $model = Shipment::find($id);
            $this->deleteExternColi($model->company,$model->track);
            $model->fill($request->Shipment);

            $costs = $this->applyShipmentCost($model,$_POST['Package']);
            $model->fill($costs);
            if (!$model->save()) {
                throw new \Exception();
            }
            if($model->status_id != Shipment::DRAFT_STATUS){

                $this->createExternColi($model,$_POST['Package']);
            }

            foreach (PackageShipment::where('shipment_id', $model->id)->get() as $pack) {
                $pack->delete();
            }
            $counter = 0;

            if (isset($_POST['Package'])) {
                if (!empty($_POST['Package'])) {
                    if (isset($_POST['Package'][$counter]['package_id'])) {

                                    foreach ($_POST['Package'] as $package) {
                            $package_shipment = new PackageShipment();
                            $package_shipment->fill($package);
                            $package_shipment->shipment_id = $model->id;
                            if (!$package_shipment->save()) {
                                throw new \Exception();
                            }
                        }
                    }
                }
            }

            event(new UpdateShipment($model));
            DB::commit();

            $model->syncFromMediaLibraryRequest($request->image)->toMediaCollection('attachments');
            return redirect()->route('shipments.show', $model->id)->with(['message_alert' => __('cargo::messages.saved')]);;
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;
            return back();
        }

    }

    public function import(Request $request)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.shipments'),
                'path' => fr_route('shipments.index')
            ],
            [
                'name' => __('cargo::view.import_shipments'),
            ],
        ]);
        $shipment = new Shipment;
        $columns = $shipment->getTableColumns();
        $countries = Country::where('covered',1)->get();
        $states    = State::where('covered',1)->get();
        $areas     = Area::get();
        $packages  = Package::all();
        $branches  = Branch::where('is_archived', 0)->get();
        $paymentsGateway = BusinessSetting::where("key","payment_gateway")->where("value","1")->get();
        $deliveryTimes   = DeliveryTime::all();
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.shipments.import')
        ->with(['columns' => $columns, 'countries' => $countries, 'states' => $states, 'areas' => $areas, 'packages' => $packages, 'branches' => $branches, 'deliveryTimes' => $deliveryTimes ]);
    }

    public function parseImport(Request $request)
    {

        $request->validate([
            'shipments_file' => 'required|mimes:csv,txt,xls,xlsx,Xls,XLS,Xlsx,XLSX',
            // "columns"        => "required|array|min:5",
        ]);



        $data = [];

        $ext = strtolower($request->file('shipments_file')->getClientOriginalExtension());
        if($ext == 'csv' || $ext == 'txt'){

            $path = $request->file('shipments_file')->getRealPath();
            $csv = new CSVUtility("testfile");
            $csv->readCSV($path);
            $totalRows = $csv->totalRows();



            for($row=0; $row<$totalRows; $row++) {

                $value = $csv->getRow($row);
                array_push($data,$value);

            }

        }else{

            if ($ext == 'xls') {
                $reader = new Xls();
            } else {
                $reader = new Xlsx();
            }
            $xlsx = $reader->load($request->file('shipments_file'));
            $xlsx = $xlsx->getActiveSheet()->toArray();
            $totalRows = count($xlsx);

            for($row=0; $row<$totalRows; $row++) {

                $value = $xlsx[$row];
                array_push($data,$value);

            }
        }



        // print_r($data[0]);
        // exit ;
        $request->columns = $data[0] ;
        // if(count($data[0]) != count($request->columns)){

        //     return redirect()->back()->with(['error_message_alert' => __('cargo::view.this_file_you_are_trying_to_import_is_not_the_file_that_you_should_upload')]);
        // }

        // if(!in_array('delivery_type',$request->columns) || !in_array('type',$request->columns) || !in_array('client_phone',$request->columns) || !in_array('client_address',$request->columns) || !in_array('branch_id',$request->columns) || !in_array('shipping_date',$request->columns) || !in_array('reciver_name',$request->columns) || !in_array('reciver_phone',$request->columns) || !in_array('reciver_address',$request->columns) || !in_array('to_state_id',$request->columns) || !in_array('to_area_id',$request->columns) || !in_array('payment_type',$request->columns) || !in_array('payment_method_id',$request->columns) || !in_array('package_id',$request->columns) ){
        //     return back()->with(['error_message_alert' => __('cargo::view.make_sure_all_required_parameters_in_CSV')]);
        // }

        if(!in_array('amount_to_be_collected',$request->columns) || !in_array('description',$request->columns) || !in_array('qty',$request->columns) || !in_array('reciver_name',$request->columns) || !in_array('reciver_phone',$request->columns) || !in_array('to_state_id',$request->columns) || !in_array('to_area',$request->columns) ){
            return redirect()->route('shipments.import')->with(['error_message_alert' => __('make_sure_all_required_parameters_in_CSV')]);
        }

        if(auth()->user()->can('import-shipments')){
            if(!in_array('client_id',$request->columns)){
                return redirect()->route('shipments.import')->with(['error_message_alert' => __('make_sure_all_required_parameters_in_CSV')]);
            }
        }

    //    try {
            $user_role = auth()->user()->role;
            $admin  = 1;
            $auth_staff  = 0;
            $auth_branch = 3;
            $auth_client = 4;

            unset($data[0]);

            if($user_role == $auth_client){
                $client = Client::where('user_id',auth()->user()->id)->first();
                $new_shipment['branch_id'] = $client->branch_id;
                $new_shipment['client_id'] = $client->id;
                $new_shipment['client_phone'] = $client->responsible_mobile;
                $addresse = ClientAddress::where('client_id',$client->id)->first();
                if($addresse){
                    $new_shipment['from_country_id'] = $addresse->country_id;
                    $new_shipment['to_country_id']  = $addresse->country_id;
                    $new_shipment['from_state_id']  = $addresse->state_id;
                    $new_shipment['from_area_id']   = $addresse->area_id;
                }
                $new_shipment['payment_method_id'] = "cash_payment";
                $new_shipment['type'] = 1 ;
                $new_shipment['payment_type'] = 1;
                $new_shipment['delivery_type'] = 1;
                $new_package['package_id'] = 2;
              	$new_package['qty'] = 1;
                $new_package['description'] = "produit";



                $defult_shipping_date = ShipmentSetting::getVal('def_shipping_date');
                if($defult_shipping_date == null )
                {
                    $shipping_data = Carbon::now()->format('d-m-Y');
                }else{
                    $shipping_data = Carbon::now()->addDays($defult_shipping_date)->format('d-m-Y');
                }
                $shippingDate = $shipping_data;

                $new_shipment['shipping_date'] = date("d-m-Y", strtotime($shippingDate));
            }

            $coli_alert = [];
            foreach ($data as $row) {
                try{
                    $error_message = null ;
                    for ($i=0; $i < count($row); $i++) {

                        if($user_role != $auth_client){
                            if($request->columns[$i] == 'client_id'){
                                if(!Client::find($row[$i])){

                                    return redirect()->route('shipments.import')->with(['error_message_alert' => __('invalid_client')]);
                                }
                                $client = Client::where('id',$row[$i])->first();
                                $new_shipment['branch_id'] = $client->branch_id;
                                $new_shipment['client_id'] = $client->id;
                                $new_shipment['client_phone'] = $client->responsible_mobile;
                                $addresse = ClientAddress::where('client_id',$client->id)->first();
                                if($addresse){
                                    $new_shipment['from_country_id'] = $addresse->country_id;
                                    $new_shipment['to_country_id']  = $addresse->country_id;
                                    $new_shipment['from_state_id']  = $addresse->state_id;
                                    $new_shipment['from_area_id']   = $addresse->area_id;
                                }
                                $new_shipment['payment_method_id'] = "cash_payment";
                                $new_shipment['type'] = 1 ;
                                $new_shipment['payment_type'] = 1;
                                $new_shipment['delivery_type'] = 1;
                                $new_package['package_id'] = 2;
                              	$new_package['qty'] = 1;
                              	$new_package['description'] = "produit";



                                $defult_shipping_date = ShipmentSetting::getVal('def_shipping_date');
                                if($defult_shipping_date == null )
                                {
                                    $shipping_data = Carbon::now()->format('d-m-Y');
                                }else{
                                    $shipping_data = Carbon::now()->addDays($defult_shipping_date)->format('d-m-Y');
                                }
                                $shippingDate = $shipping_data;

                                $new_shipment['shipping_date'] = date("d-m-Y", strtotime($shippingDate));
                            }
                        }


                        // Validation

                        if($request->columns[$i] == 'type'){
                            if(intval($row[$i]) != Shipment::POSTPAID && intval($row[$i]) != Shipment::PREPAID ){

                                $error_message = __('invalid_type');
                            // return back()->with(['error_message_alert' => __('cargo::view.invalid_type')]);
                            }
                        }

                        if($request->columns[$i] == 'delivery_type'){
                            if($row[$i] == "home" || intval($row[$i]) == 1){
                                $row[$i] = 1 ;
                            }else{
                                $row[$i] = 2 ;
                            }
                            if(intval($row[$i]) != Shipment::DESK && intval($row[$i]) != Shipment::HOME ){

                                $error_message = __('invalid delivery type') ;
                            //  return back()->with(['error_message_alert' => __('invalid delivery type')]);
                            }
                        }

                        if($request->columns[$i] == 'branch_id'){
                            if(!Branch::find($row[$i])){

                                $error_message = __('invalid_branch') ;
                                // return back()->with(['error_message_alert' => __('cargo::view.invalid_branch')]);
                            }
                        }

                        // if($request->columns[$i] == 'client_address'){
                        //     if(!ClientAddress::where('client_id',$client->id)->where('id',$row[$i])->first()){

                        //         return back()->with(['error_message_alert' => __('cargo::view.invalid_client_address')]);
                        //     }
                        // }

                        // if($request->columns[$i] == 'from_country_id' || $request->columns[$i] == 'to_country_id'){
                        //     if(!Country::find($row[$i])){
                        //         return back()->with(['error_message_alert' => __('cargo::view.invalid_country')]);
                        //     }
                        // }

                        if($request->columns[$i] == 'from_state_id' || $request->columns[$i] == 'to_state_id' ){
                            if(!State::find($row[$i])){

                                $error_message = __('invalid_state') ;
                                // return back()->with(['error_message_alert' => __('cargo::view.invalid_state')]);
                            }
                        }

                        // if($request->columns[$i] == 'from_area_id' || $request->columns[$i] == 'to_area_id'){
                        //     if(!Area::find($row[$i])){
                        //         return back()->with(['error_message_alert' => __('cargo::view.invalid_area')]);
                        //     }
                        // }

                        if($request->columns[$i] == 'payment_method_id'){
                            $paymentSettings = resolve(\Modules\Payments\Entities\PaymentSetting::class)->toArray();
                            if(!isset($paymentSettings[$row[$i]])){

                                $error_message = __('invalid_payment_method');
                            //  return back()->with(['error_message_alert' => __('cargo::view.invalid_payment_method')]);
                            }
                        }

                        if($request->columns[$i] == 'payment_type'){
                            if($row[$i] != Shipment::POSTPAID && $row[$i] != Shipment::PREPAID){

                                $error_message = __('invalid_payment_type');
                            // return back()->with(['error_message_alert' => __('cargo::view.invalid_payment_type')]);
                            }
                        }

                        if($request->columns[$i] == 'package_id'){
                            if(!Package::find($row[$i])){

                                $error_message = __('invalid_package');
                            //    return back()->with(['error_message_alert' => __('cargo::view.invalid_package')]);
                            }
                        }


                        if($request->columns[$i] == 'to_area'){
                            $new_shipment['to_area_id']  = Area::where('name' , ShipmentActionHelper::getClosestMatch($row[$i],$client->id, $row[$i-1]))->where('state_id', $row[$i-1])->pluck('id')->first();
                            if(!isset($new_shipment['to_area_id']) || empty($new_shipment['to_area_id'])){

                                $error_message = __('invalid_area') ;
                            // return redirect()->route('shipments.import')->with(['error_message_alert' => __('cargo::view.invalid_area')]);
                            }
                        }

                        // End Validation

                        if($request->columns[$i] != 'package_id' && $request->columns[$i] != 'description' && $request->columns[$i] != 'height' && $request->columns[$i] != 'width' && $request->columns[$i] != 'length' && $request->columns[$i] != 'weight' && $request->columns[$i] != 'qty' )
                        {

                            if($request->columns[$i] == 'amount_to_be_collected'){

                                if($row[$i] == "" || $row[$i] == " " || !is_numeric($row[$i]))
                                {
                                    $new_shipment[$request->columns[$i]] = 0;
                                }else{
                                    $new_shipment[$request->columns[$i]] = $row[$i];
                                }
                            }elseif($request->columns[$i] == 'client_phone'){
                                if($row[$i] == "" || $row[$i] == " ")
                                {
                                    $new_shipment[$request->columns[$i]] = $client->responsible_mobile ?? $auth_user->phone;
                                }else{
                                    $new_shipment[$request->columns[$i]] = $row[$i];
                                }
                            }
                            else {
                                $new_shipment[$request->columns[$i]] = $row[$i];
                            }

                        }else{
                            if($request->columns[$i] == 'package_id')
                            {
                                $new_package[$request->columns[$i]] = intval($row[$i]);
                            }else{
                                if($request->columns[$i] != 'description')
                                {
                                    if($row[$i] == "" || $row[$i] == " " || !is_numeric($row[$i]))
                                    {
                                        $new_package[$request->columns[$i]] = 1;

                                        if($request->columns[$i] == 'weight'){
                                            $new_shipment['total_weight'] = 1;
                                        }
                                    }else{
                                        $new_package[$request->columns[$i]] = $row[$i];
                                        if($request->columns[$i] == 'weight'){
                                            $new_shipment['total_weight'] = $row[$i];
                                        }
                                    }
                                }else {
                                    $new_package[$request->columns[$i]] = $row[$i];
                                }
                            }

                        }

                    }
                    unset($new_shipment["to_area"]);
                    // print_r($new_shipment);
                    // exit;

                    $request['Shipment'] = $new_shipment;
                    $packages[0] = isset($new_package)?$new_package:null;
                    $request['Package'] = $packages;
                    if($error_message == null){

                        $this->storeShipment($request);
                    }else{
                        array_push($coli_alert, $row) ;
                      //	var_dump($error_message); die;
                    }
                } catch(\Throwable $th) {
                    $error_message = $th->getMessage();
                    array_push($coli_alert, $row) ;
                  //	var_dump($error_message); die;
                }

                // return redirect()->route('shipments.import')->with(['error_message_alert' => __($th->getMessage())]);


            }
            if(count($coli_alert) > 0){
                $suffix = 'error_' . now() . '_';
                $xlsx_name = $suffix . strtolower($request->file('shipments_file')->getClientOriginalName());
                return Excel::download(new CompanyPaymentExport($coli_alert, $request->columns), $xlsx_name);
               // dd($error_message);
            }
            return redirect()->route('shipments.index')->with(['message_alert' => __('cargo::messages.created')]);
            // return back()->with(['message_alert' => __('cargo::messages.imported')]);


    }

    public function change(Request $request, $to)
    {
        if (isset($request->ids)) {
            $action = new StatusManagerHelper();
            $response = $action->change_shipment_status($request->ids, $to);
            if ($response['success']) {
                event(new ShipmentAction($to,$request->ids));
                return back()->with(['message_alert' => __('cargo::messages.saved')]);
            }
        } else {
            return back()->with(['error_message_alert' => __('cargo::messages.select_error')]);
        }
    }

    public function createPickupMission(Request $request, $type)
    {
        try {

            if(!is_array($request->checked_ids)){
                $request->checked_ids = json_decode($request->checked_ids, true);
            }

            DB::beginTransaction();
            $model = new Mission();
            $model->fill($request['Mission']);
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->type = Mission::PICKUP_TYPE;
            if (!$model->save()) {
                throw new \Exception();
            }

            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = $code.$model->id;
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;

            if (!$model->save()) {
                throw new \Exception();
            }

            //change shipment status to requested
            $action = new StatusManagerHelper();

            $response = $action->change_shipment_status($request->checked_ids, Shipment::REQUESTED_STATUS, $model->id);

            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);

            foreach ($request->checked_ids as $shipment_id) {
                if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::PICKUP_TYPE) == 0)
                {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                }
            }

            event(new ShipmentAction( Shipment::REQUESTED_STATUS,$request->checked_ids));

            event(new CreateMission($model));

            DB::commit();
            if($request->is('api/*')){
                 return $model;
            }else{
                return back()->with(['message_alert' => __('cargo::messages.created')]);
            }

        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function createDeliveryMission(Request $request, $type)
    {
        try {
            $request->checked_ids = json_decode($request->checked_ids, true);
            DB::beginTransaction();
            $model = new Mission();
            // $model->fill($request['Mission']);
            $model->code = -1;
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->type = Mission::DELIVERY_TYPE;
            $model->otp  = MissionPRNG::get();
            // if(ShipmentSetting::getVal('def_shipment_conf_type')=='otp'){
            //     $model->otp = MissionPRNG::get();
            // }
            if (!$model->save()) {
                throw new \Exception();
            }
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;
            if (!$model->save()) {
                throw new \Exception();
            }
            foreach ($request->checked_ids as $shipment_id) {


                if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::DELIVERY_TYPE) == 0) {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                }
            }
            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);

            event(new CreateMission($model));
            DB::commit();

            if($request->is('api/*')){
                 return $model;
            }else{
                return back()->with(['message_alert' => __('cargo::messages.created')]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function createTransferMission(Request $request, $type)
    {
        try {
            $request->checked_ids = json_decode($request->checked_ids, true);
            DB::beginTransaction();
            $model = new Mission();
            $model->fill($request['Mission']);
            $model->code = -1;
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->type = Mission::TRANSFER_TYPE;
            if (!$model->save()) {
                throw new \Exception();
            }
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;
            if (!$model->save()) {
                throw new \Exception();
            }
            foreach ($request->checked_ids as $shipment_id) {
                // if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::TRANSFER_TYPE) == 0) {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                // }
            }

            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);


            event(new CreateMission($model));
            DB::commit();

            if($request->is('api/*')){
                 return $model;
            }else{
                return back()->with(['message_alert' => __('cargo::messages.created')]);
            }

        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function createSupplyMission(Request $request, $type)
    {
        try {
            if(!is_array($request->checked_ids)){
                $request->checked_ids = json_decode($request->checked_ids, true);
            }

            DB::beginTransaction();
            $model = new Mission();
            $model->fill($request['Mission']);
            $model->code = -1;
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->type = Mission::SUPPLY_TYPE;
            if (!$model->save()) {
                throw new \Exception();
            }
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;
            if (!$model->save()) {
                throw new \Exception();
            }
            foreach ($request->checked_ids as $shipment_id) {
                if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::SUPPLY_TYPE) == 0) {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                }
            }

            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);


            event(new CreateMission($model));
            DB::commit();

            if($request->is('api/*')){
                 return $model;
            }else{
                return back()->with(['message_alert' => __('cargo::messages.created')]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function createReturnMission(Request $request, $type)
    {
        try {
            $request->checked_ids = json_decode($request->checked_ids, true);
            DB::beginTransaction();
            $model = new Mission();
            $model->fill($request['Mission']);
            $model->code = -1;
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->otp  = MissionPRNG::get();
            $model->type = Mission::RETURN_TYPE;
            if (!$model->save()) {
                throw new \Exception();
            }
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;
            if (!$model->save()) {
                throw new \Exception();
            }

            foreach ($request->checked_ids as $shipment_id) {
                if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::RETURN_TYPE) == 0) {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                }
            }

            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);

            event(new CreateMission($model));
            DB::commit();

            if($request->is('api/*')){
                 return $model;
            }else{
                            return back()->with(['message_alert' => __('cargo::messages.created')]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function removeShipmentFromMission(Request $request , $fromApi = false)
    {
        $shipment_id = $request->shipment_id;
        $mission_id = $request->mission_id;
        try {
            DB::beginTransaction();

            $mission = Mission::find($mission_id);
            $shipment = Shipment::find($shipment_id);
            if($mission && $shipment && in_array($mission->status_id , [Mission::APPROVED_STATUS,Mission::REQUESTED_STATUS,Mission::RECIVED_STATUS])){

                $action = new StatusManagerHelper();
                if($mission->type == Mission::getType(Mission::PICKUP_TYPE)){
                    $response = $action->change_shipment_status([$shipment_id], Shipment::SAVED_STATUS, $mission_id);
                }elseif(in_array($mission->type , [Mission::getType(Mission::DELIVERY_TYPE) ,Mission::getType(Mission::RETURN_TYPE) , Mission::getType(Mission::TRANSFER_TYPE) ]) && $mission->status_id == Mission::RECIVED_STATUS){
                    $response = $action->change_shipment_status([$shipment_id], Shipment::RETURNED_STATUS, $mission_id);
                }elseif(in_array($mission->type , [Mission::getType(Mission::DELIVERY_TYPE) ,Mission::getType(Mission::RETURN_TYPE) , Mission::getType(Mission::TRANSFER_TYPE) ]) && in_array($mission->status_id , [Mission::APPROVED_STATUS,Mission::REQUESTED_STATUS]) ){
                    $response = $action->change_shipment_status([$shipment_id], Shipment::RETURNED_STOCK, $mission_id);
                }

                if($shipment_mission = $mission->shipment_mission_by_shipment_id($shipment_id)){
                    $shipment_mission->delete();
                }
                $shipment_reason = new ShipmentReason();
                $shipment_reason->reason_id = $request->reason;
                $shipment_reason->shipment_id = $request->shipment_id;
                $shipment_reason->type = "Delete From Mission";
                $shipment_reason->save();
                //Calaculate Amount
                $helper = new TransactionHelper();
                $helper->calculate_mission_amount($mission_id);

                $mission_shipments = ShipmentMission::where('mission_id',$mission->id)->get();
                if(count($mission_shipments) == 0){
                    $mission->status_id = Mission::DONE_STATUS;
                    $mission->save();
                }
                event(new UpdateMission( $mission_id));
                // event(new ShipmentAction( Shipment::SAVED_STATUS,[$shipment]));
                DB::commit();
                if($fromApi)
                {
                    return true;
                }
                return back()->with(['message_alert' => __('cargo::messages.deleted')]);
            }else{
                return back()->with(['error_message_alert' => __('cargo::messages.invalid')]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function pay($shipment_id)
    {
        $shipment = Shipment::find($shipment_id);
        if(!$shipment || $shipment->paid == 1){
            flash("Invalid Link")->error();
            return back();
        }

        // return $shipment;
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.shipments.pay', compact('shipment'));
    }

    public function ajaxGetEstimationCost(Request $request)
    {
        $request->validate([
            'total_weight' => 'required|numeric|min:0',
        ]);
        $costs = $this->applyShipmentCost($request,$request->package_ids);
        $formated_cost["tax"] = format_price($costs["tax"]);
        $formated_cost["insurance"] = format_price(($costs["insurance"] * $costs["amount_to_be_collected"]) / 100);

        $formated_cost["delivery_type"] = ($costs["delivery_type"] == Shipment::HOME)? 'HOME' : 'DESK' ;
        $formated_cost["return_cost"] = format_price($costs["return_cost"]);
        $formated_cost["shipping_cost"] = format_price($costs["shipping_cost"]);
        $formated_cost["total_cost"] = format_price($costs["shipping_cost"] + $costs["tax"] + (($costs["insurance"] * $costs["amount_to_be_collected"]) / 100));

        return $formated_cost;
    }

    public function CompanyShipmentCost($shipment, $clientx)
    {
        if($shipment && $clientx){

            $covered = PlanFee::where('state_id', $shipment->to_state_id)->where('plan_id', $clientx->plan_id)->where('active', 1)->first();

            if($covered){

                $covered = PlanAreaFee::where('plan_fee_id', $covered->id)->where('area_id', $shipment->to_area_id)->where('active', 1)->first();
            }
            if($covered && $covered->company != 0){
                $_company = Company::find($covered->company);
                $company_fee = CompanyPlanFee::where('state_id', $shipment->to_state_id)->where('plan_id', $_company->plan_id)->where('active', 1)->first();
                $shipping_cost = ($shipment->delivery_type == Shipment::DESK && $covered->desk_fee > 0) ?  $covered->desk_fee : $covered->home_fee;
                // $array['tax'] = 0;
                $array['insurance'] = $covered->recovery_rate;
                $array['return_cost'] = $covered->return_fee;
                $array['shipping_cost'] = $shipping_cost;
                $array['company_cost'] = $shipping_cost;
                $array['shipping_to_company'] = ($shipment->delivery_type == Shipment::DESK && $covered->desk_fee > 0) ? $company_fee->desk_fee : $company_fee->home_fee;
                $array['return_to_company'] = $company_fee->return_fee;
                $array['recovery_to_company'] = $company_fee->recovery_rate;
                $array['company'] = $covered->company;
                $array['delivery_type'] =  ($shipment->delivery_type == Shipment::DESK && $covered->desk_fee > 0)? Shipment::DESK : Shipment::HOME ;
                return $array;
            }else{

                return null;
            }
        }
        return null;

    }

    public function applyShipmentCost($request,$packages)
    {
        $client_costs    = Client::where('id', $request['client_id'] )->first();
        $idPackages      = array_column($packages, 'package_id');
        $client_packages = ClientPackage::where('client_id', $request['client_id'])->whereIn('package_id',$idPackages)->get();

        // $from_country_id = $request['from_country_id'];
        // $to_country_id = $request['to_country_id'];

        // if (isset($request['from_state_id']) && isset($request['to_state_id'])) {
        //     $from_state_id = $request['from_state_id'];
        //     $to_state_id = $request['to_state_id'];
        // }
        // if (isset($request['from_area_id']) && isset($request['to_area_id'])) {
        //     $from_area_id = $request['from_area_id'];
        //     $to_area_id = $request['to_area_id'];
        // }

        $total_weight = 0 ;
        $package_extras = 0;

        if($client_packages){
            foreach ($client_packages as $pack) {
                $total_weight += isset($pack['weight']) ? $pack['weight'] : 1;
                $extra = $pack['cost'];
                $package_extras += $extra;
            }
        }else{
            foreach ($packages as $pack) {
                $total_weight += isset($pack['weight']) ? $pack['weight'] : 1;
                $extra = Package::find($pack['package_id'])->cost;
                $package_extras += $extra;
            }
        }

        //$weight =  $request['total_weight'];
        $weight = isset($request['total_weight']) ? $request['total_weight'] : $total_weight;

        $array = ['return_cost' => 0, 'shipping_cost' => 0, 'tax' => 0, 'insurance' => 0];

        // Shipping Cost = Default + kg + Covered Custom  + Package extra
        // $covered_cost = Cost::where('from_country_id', $from_country_id)->where('to_country_id', $to_country_id);

        // if (isset($request['from_area_id']) && isset($request['to_area_id'])) {
        //     $covered_cost = $covered_cost->where('from_area_id', $from_area_id)->where('to_area_id', $to_area_id);
        //     if(!$covered_cost->first()){
        //         $covered_cost = Cost::where('from_country_id', $from_country_id)->where('to_country_id', $to_country_id);

        //         if (isset($request['from_state_id']) && isset($request['to_state_id'])) {
        //             $covered_cost = $covered_cost->where('from_state_id', $from_state_id)->where('to_state_id', $to_state_id);
        //             if(!$covered_cost->first()){
        //                 $covered_cost = Cost::where('from_country_id', $from_country_id)->where('to_country_id', $to_country_id);
        //                 $covered_cost = $covered_cost->where('from_state_id', 0)->where('to_state_id', 0);
        //             }
        //         }else{
        //             $covered_cost = $covered_cost->where('from_area_id', 0)->where('to_area_id', 0);
        //             if(!$covered_cost->first()){
        //                 $covered_cost = Cost::where('from_country_id', $from_country_id)->where('to_country_id', $to_country_id);
        //                 $covered_cost = $covered_cost->where('from_state_id', 0)->where('to_state_id', 0);
        //             }
        //         }
        //     }
        // }else{

        //     if (isset($request['from_state_id']) && isset($request['to_state_id'])) {
        //         $covered_cost = $covered_cost->where('from_state_id', $from_state_id)->where('to_state_id', $to_state_id);
        //     }else{
        //         $covered_cost = $covered_cost->where('from_area_id', 0)->where('to_area_id', 0);
        //         if(!$covered_cost->first()){
        //             $covered_cost = Cost::where('from_country_id', $from_country_id)->where('to_country_id', $to_country_id);
        //             $covered_cost = $covered_cost->where('from_state_id', 0)->where('to_state_id', 0);
        //         }
        //     }

        // }

        // $covered_cost = $covered_cost->first();
        $array = $this->CompanyShipmentCost($request,$client_costs)? $this->CompanyShipmentCost($request,$client_costs):$array;
        $covered_cost = (object) $array;

        if($covered_cost && isset($covered_cost->company) && $covered_cost->company !=0){
            $def_return_cost    =   null;
            $def_shipping_cost  =   null;
            $def_insurance  =   null;
        }else{
            $def_return_cost      = $client_costs && $client_costs->def_return_cost ? $client_costs->def_return_cost : ShipmentSetting::getCost('def_return_cost');
            $def_shipping_cost      = $client_costs && $client_costs->def_shipping_cost ? $client_costs->def_shipping_cost : ShipmentSetting::getCost('def_shipping_cost');
            $def_insurance      = $client_costs && $client_costs->def_insurance ? $client_costs->def_insurance : ShipmentSetting::getCost('def_insurance');
        }
        $def_return_cost_gram = $client_costs && $client_costs->def_return_cost_gram   ? $client_costs->def_return_cost_gram   : ShipmentSetting::getCost('def_return_cost_gram');

        $def_shipping_cost_gram = $client_costs && $client_costs->def_shipping_cost_gram ? $client_costs->def_shipping_cost_gram : ShipmentSetting::getCost('def_shipping_cost_gram');

        $def_return_mile_cost_gram = $client_costs && $client_costs->def_return_mile_cost_gram ? $client_costs->def_return_mile_cost_gram : ShipmentSetting::getCost('def_return_mile_cost_gram');
        $def_return_mile_cost      = $client_costs && $client_costs->def_return_mile_cost ? $client_costs->def_return_mile_cost : ShipmentSetting::getCost('def_return_mile_cost');

        $def_mile_cost_gram = $client_costs && $client_costs->def_mile_cost_gram ? $client_costs->def_mile_cost_gram : ShipmentSetting::getCost('def_mile_cost_gram');
        $def_mile_cost      = $client_costs && $client_costs->def_mile_cost ? $client_costs->def_mile_cost : ShipmentSetting::getCost('def_mile_cost');

        $def_insurance_gram = 0 ;//    $client_costs && $client_costs->def_insurance_gram ? $client_costs->def_insurance_gram : ShipmentSetting::getCost('def_insurance_gram');


        $def_tax_gram = $client_costs && $client_costs->def_tax_gram ? $client_costs->def_tax_gram : ShipmentSetting::getCost('def_tax_gram');
        $def_tax      = $client_costs && $client_costs->def_tax ? $client_costs->def_tax : ShipmentSetting::getCost('def_tax');




        if ($covered_cost != null) {
            if($weight > 1){
                if(ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {
                    $return_cost = (float) ($def_return_cost != null ? $def_return_cost : $covered_cost->return_cost) + (float) ( $def_return_cost_gram * ($weight -1));
                    $shipping_cost_first_one = (float) ($def_shipping_cost != null ? $def_shipping_cost : $covered_cost->shipping_cost) + $package_extras;
                    $shipping_cost_for_extra = (float) ( $def_shipping_cost_gram * ($weight -1));
                } else if(ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $return_cost = (float) $def_return_mile_cost ?? $covered_cost->return_mile_cost + (float) ( $def_return_mile_cost_gram * ($weight -1));
                    $shipping_cost_first_one = (float) ($def_mile_cost ?? $covered_cost->mile_cost) + $package_extras;
                    $shipping_cost_for_extra = (float) ( $def_mile_cost_gram * ($weight -1));
                }
                $insurance = (float) $def_insurance != null ? $def_insurance : $covered_cost->insurance ; //    + (float) ( $def_insurance_gram * ($weight -1));

                $tax_for_first_one = (($def_tax ?? $covered_cost->tax * $shipping_cost_first_one) / 100 );

                $tax_for_exrea = (( $def_tax_gram * $shipping_cost_for_extra) / 100 );

                $shipping_cost = $shipping_cost_first_one + $shipping_cost_for_extra;
                $tax = $tax_for_first_one + $tax_for_exrea;

            }else{

                if(ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {

                    $return_cost = (float) $def_return_cost != null ? $def_return_cost : $covered_cost->return_cost;
                    $shipping_cost = (float) ($def_shipping_cost != null ? $def_shipping_cost : $covered_cost->shipping_cost) + $package_extras;
                } else if(ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $return_cost = (float) $def_return_mile_cost ?? $covered_cost->return_mile_cost;
                    $shipping_cost = (float) ($def_mile_cost ?? $covered_cost->mile_cost) + $package_extras;
                }
                $insurance = (float) $def_insurance != null ? $def_insurance : $covered_cost->insurance ;
                $tax = (($def_tax ?? $covered_cost->tax * $shipping_cost) / 100 );
            }

            $array['tax'] = $tax;
            $array['insurance'] = $insurance;
            $array['return_cost'] = $return_cost;
            $array['shipping_cost'] = $shipping_cost;

        } else {
            if($weight > 1){
                if(ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {
                    $return_cost = $def_return_cost + (float) ( $def_return_cost_gram * ($weight -1));
                    $shipping_cost_first_one = $def_shipping_cost + $package_extras;
                    $shipping_cost_for_extra = (float) ( $def_shipping_cost_gram * ($weight -1));

                } else if(ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $return_cost = $def_return_mile_cost + (float) ( $def_return_mile_cost_gram * ($weight -1));
                    $shipping_cost_first_one = $def_mile_cost + $package_extras;
                    $shipping_cost_for_extra = (float) ( $def_mile_cost_gram * ($weight -1));
                }

                $insurance = $def_insurance ;   //  + (float) ( $def_insurance_gram * ($weight -1));
                $tax_for_first_one = (( $def_tax * $shipping_cost_first_one) / 100 );
                $tax_for_exrea = ((ShipmentSetting::getCost('def_tax_gram') * $shipping_cost_for_extra) / 100 );

                $shipping_cost = $shipping_cost_first_one + $shipping_cost_for_extra;
                $tax = $tax_for_first_one + $tax_for_exrea;

            }else{
                if(ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {
                    $return_cost = $def_return_cost;
                    $shipping_cost = $def_shipping_cost + $package_extras;
                } else if(ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $return_cost = $def_return_mile_cost;
                    $shipping_cost = $def_mile_cost + $package_extras;
                }
                $insurance = $def_insurance;
                $tax = (( $def_tax * $shipping_cost) / 100 );
            }

            $array['tax'] = $tax;
            $array['insurance'] = $insurance;
            $array['return_cost'] = $return_cost;
            $array['shipping_cost'] = $shipping_cost;


        }
        $array['amount_to_be_collected'] = $request['amount_to_be_collected'];

        return $array;
    }

    public function print($shipment, $type = 'invoice')
    {
        $shipment = Shipment::find($shipment);
        if($type == 'label'){
            $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.shipments.print-label', compact('shipment'));
        }elseif($type == 'invoice'){
            breadcrumb([
                [
                    'name' => __('cargo::view.dashboard'),
                    'path' => fr_route('admin.dashboard')
                ],
                [
                    'name' => __('cargo::view.shipments'),
                    'path' => fr_route('shipments.index')
                ],
                [
                    'name' => __('cargo::view.shipment').' '.$shipment->code,
                    'path' => fr_route('shipments.show', $shipment->id)
                ],
                [
                    'name' => __('cargo::view.print_invoice'),
                ],
            ]);
            $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.shipments.print-invoice', compact('shipment'));
        }elseif($type == 'tracking'){
            $client = Client::where('id', $shipment->client_id)->first();
            $PackageShipment = PackageShipment::where('shipment_id',$shipment->id)->get();
            $ClientAddress = ClientAddress::where('client_id',$shipment->client_id)->first();
            $adminTheme = env('ADMIN_THEME', 'adminLte');
            return view('cargo::'.$adminTheme.'.pages.shipments.print-tracking')->with(['model' => $shipment,'client' => $client , 'PackageShipment' => $PackageShipment , 'ClientAddress' => $ClientAddress ]);

        }
    }

    public function printTracking($shipment)
    {

        $shipment = Shipment::find($shipment);
        $client = Client::where('id', $shipment->client_id)->first();
        $PackageShipment = PackageShipment::where('shipment_id',$shipment->id)->get();
        $ClientAddress = ClientAddress::where('client_id',$shipment->client_id)->first();

        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.shipments.print-tracking')->with(['model' => $shipment,'client' => $client , 'PackageShipment' => $PackageShipment , 'ClientAddress' => $ClientAddress ]);
    }

    public function printStickers(Request $request)
    {
        $request->checked_ids = json_decode($request->checked_ids, true);
        $shipments = Shipment::whereIn('id', $request->checked_ids)->get();

        $adminTheme = env('ADMIN_THEME', 'adminLte');

        $pdf = Pdf::loadView('cargo::'.$adminTheme.'.pages.shipments.print-stickers', compact('shipments'),[],
            ['format' => [106,100]]);

        if($pdf->stream('tickets.pdf')){
            return view('cargo::'.$adminTheme.'.pages.shipments.print-stickers', compact('shipments'));

        }

    }

    public function ShipmentApis()
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.shipment_apis'),
            ],
        ]);
        $client = Client::where('user_id',auth()->user()->id)->first();

        $countries = Country::where('covered',1)->get();
        $states    = State::where('covered',1)->get();
        $areas     = Area::get();
        $packages  = Package::all();
        $branches   = Branch::where('is_archived', 0)->get();
        $paymentsGateway = BusinessSetting::where("key","payment_gateway")->where("value","1")->get();
        $addresses       = ClientAddress::where('client_id', $client->id )->get();
        $deliveryTimes   = DeliveryTime::all();

        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.shipments.apis')
        ->with(['countries' => $countries, 'states' => $states, 'areas' => $areas, 'packages' => $packages, 'branches' => $branches, 'paymentsGateway' => $paymentsGateway, 'deliveryTimes' => $deliveryTimes, 'client' => $client, 'addresses' => $addresses ]);
    }

    public function ajaxGgenerateToken()
    {
        $userRegistrationHelper = new UserRegistrationHelper(auth()->user()->id);
        $token = $userRegistrationHelper->setApiTokenGenerator();

        return response()->json($token);
    }

    public function createMissionAPI(Request $request)
    {

        $apihelper = new ApiHelper();
        $user = $apihelper->checkUser($request);

        if($user){
            $request->validate([
                'checked_ids'       => 'required',
                'type'              => 'required',
                'Mission.client_id' => 'required',
                'Mission.address'   => 'required',
            ]);

            $count = 0;
            foreach($request->checked_ids as $id){
                if(Shipment::whereIn('id', $request->checked_ids)->pluck('mission_id')->first()){
                    $count++;
                }
            }
            if($count >= 1){
                return response()->json(['message' => 'this shipment already in mission'] );
            }else{
                switch($request->type){
                    case Mission::PICKUP_TYPE:
                        $mission = $this->createPickupMission($request,$request->type);
                        break;
                    case Mission::DELIVERY_TYPE:
                        $mission = $this->createDeliveryMission($request,$request->type);
                        break;
                    case Mission::TRANSFER_TYPE:
                        $mission = $this->createTransferMission($request,$request->type);
                        break;
                    case Mission::SUPPLY_TYPE:
                        $mission = $this->createSupplyMission($request,$request->type);
                        break;
                    case Mission::RETURN_TYPE:
                        $mission = $this->createReturnMission($request,$request->type);
                        break;
                }
                return response()->json($mission);
            }
        }else{
            return response()->json(['message' => 'Not Authorized'] );
        }

    }

    public function BarcodeScanner()
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.barcode_scanner'),
            ],
        ]);
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.shipments.barcode-scanner');
    }
    public function ChangeStatusByBarcode(Request $request)
    {
        if((auth()->user()->can('shipments-barcode-scanner') || auth()->user()->role == 1 || auth()->user()->role == 3 ) && ($request->createTransferMission || $request->returnMission)){
            if($request->checked_ids){
                $checked_ids = json_decode($request->checked_ids, true);
                $request->checked_ids = Shipment::whereIn('code', $checked_ids)->pluck('id');
            }else{
                return back()->with(['message_alert' => __('cargo::view.no_shipments_added') ]);
            }
            $count = 0;
            foreach($checked_ids as $id){
                if(Shipment::whereIn('code', $checked_ids)->pluck('mission_id')->first()){
                    $count++;
                }
            }
            if($count >= 1){
                return response()->json(['message' => 'this shipment already in mission'] );
            }else{
                    if($request->createTransferMission){
                        $request['Mission']= [
                            'to_branch_id' => $request->to_branch_id,
                            'captain_id' => $request->captain_id,
                            'amount' => Shipment::whereIn('code', $checked_ids)->get()->sum('amount_to_be_collected')
                        ];
                        $this->createTransferMission($request,Mission::TRANSFER_TYPE);
                    }else{
                        $request['Mission']= [
                            'to_branch_id' => $request->to_branch_id,
                            'captain_id' => $request->captain_id,
                            'amount' => Shipment::whereIn('code', $checked_ids)->get()->sum('amount_to_be_collected')
                        ];
                        $this->createReturnMission($request,Mission::RETURN_TYPE);
                    }
                    return back()->with(['message_alert' => __('cargo::messages.created')]);
            }

        }else{


            if($request->checked_ids){
                $request->checked_ids = json_decode($request->checked_ids, true);
            }else{
                return back()->with(['message_alert' => __('cargo::view.no_shipments_added') ]);
            }
            $user_role = auth()->user()->role;
            $action    = new StatusManagerHelper();
            $shipments = Shipment::whereIn('code',$request->checked_ids)->get();

            if(count($shipments) > 0){
                foreach($shipments as $shipment){
                    if($shipment)
                    {
                        $mission = Mission::where('id',$shipment->mission_id)->first();

                        $request->request->add(['ids' => [$shipment->id] ]);
                        if($user_role == 5){ // ROLE 5 == DRIVER

                            if( $shipment->status_id == Shipment::CAPTAIN_ASSIGNED_STATUS) // casa if shipment in delivery mission
                            {
                                $to = Shipment::RECIVED_STATUS;
                                $response = $action->change_shipment_status($request->ids, $to, $mission->id ?? null );
                                if ($response['success']) {
                                    event(new ShipmentAction($to,$request->ids));
                                }else{
                                    return back()->with(['error_message_alert' => __('cargo::messages.somthing_wrong') ]);
                                }
                            }else{
                                $message = __('cargo::view.cant_change_this_shipment').$shipment->code;
                                return back()->with(['error_message_alert' => $message ]);
                            }

                        }elseif(auth()->user()->can('shipments-barcode-scanner') || $user_role == 1 || $user_role == 3 ){ // ROLE 1 == ADMIN; ROLE 3 == Branch
                            if($request->force_change_status){
                                if($request->RETURNED_CLIENT_GIVEN  && Shipment::DELIVERED_STATUS != $shipment->status_id){
                                    $to = Shipment::RETURNED_CLIENT_GIVEN;
                                }elseif($request->DELIVERED_STATUS  && Shipment::RETURNED_CLIENT_GIVEN != $shipment->status_id){
                                    $to = Shipment::DELIVERED_STATUS;
                                }elseif($request->APPROVED_STATUS  && (Shipment::REQUESTED_STATUS == $shipment->status_id || Shipment::SAVED_STATUS == $shipment->status_id)){
                                    $to = Shipment::APPROVED_STATUS;
                                }
                                $response = $action->change_shipment_status($request->ids, $to, $mission->id ?? null );
                                if ($response['success']) {
                                    event(new ShipmentAction($to,$request->ids));
                                }else{
                                    return back()->with(['error_message_alert' => __('cargo::messages.somthing_wrong') ]);
                                }
                            }elseif($mission && $mission->type == Mission::getType(Mission::PICKUP_TYPE) && $mission->status_id == Mission::RECIVED_STATUS){
                                // casa if shipment in packup mission
                                $to = Shipment::APPROVED_STATUS;
                                $response = $action->change_shipment_status($request->ids, $to, $mission->id ?? null );
                                if ($response['success']) {
                                    event(new ShipmentAction($to,$request->ids));
                                }else{
                                    return back()->with(['error_message_alert' => __('cargo::messages.somthing_wrong') ]);
                                }

                            }elseif($shipment->status_id == Shipment::RETURNED_STATUS)
                            {
                                // casa if shipment in returned mission
                                $to = Shipment::RETURNED_STOCK;
                                $response = $action->change_shipment_status($request->ids, $to, $mission->id ?? null );
                                if ($response['success']) {
                                    event(new ShipmentAction($to,$request->ids));
                                }else{
                                    return back()->with(['error_message_alert' => __('cargo::messages.somthing_wrong') ]);
                                }

                            }else
                            {
                                $message = __('cargo::view.cant_change_this_shipment').$shipment->code;
                                return back()->with(['error_message_alert' => $message ]);
                            }
                        }
                    }else{
                        $message = __('cargo::view.no_shipment_with_this_barcode').$shipment->code;
                        return back()->with(['error_message_alert' => $message ]);
                    }
                }
                return back()->with(['message_alert' => __('cargo::messages.saved') ]);
            }else{
                return back()->with(['error_message_alert' => __('cargo::view.no_shipment_with_this_barcode') ]);
            }
        }

    }

    public function trackingView(Request $request)
    {
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.shipments.tracking-view');
    }

    public function tracking(Request $request)
    {
        $shipment = Shipment::where('code', $request->code)->orWhere('order_id', $request->code)->first();
        $client = Client::where('id', $shipment->client_id)->first();
        $PackageShipment = PackageShipment::where('shipment_id',$shipment->id)->get();
        $ClientAddress = ClientAddress::where('client_id',$shipment->client_id)->first();

        $adminTheme = env('ADMIN_THEME', 'adminLte');
        if($shipment){
            return view('cargo::'.$adminTheme.'.pages.shipments.tracking')->with(['model' => $shipment,'client' => $client , 'PackageShipment' => $PackageShipment , 'ClientAddress' => $ClientAddress ]);
        }else{
            $error = __('cargo::messages.invalid_code');
            return view('cargo::'.$adminTheme.'.pages.shipments.tracking')->with(['error' => $error]);
        }
    }

    public function calculator(Request $request)
    {
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.shipments.shipment-calculator');
    }

    public function calculatorStore(Request $request)
    {

        $request->validate([
            'Shipment.type'            => 'required',
            'Shipment.branch_id'       => 'required',
            'Shipment.client_phone'    => 'required_if:if_have_account,==,0',
            'Shipment.reciver_name'    => 'required|string|min:3|max:50',
            'Shipment.reciver_phone'   => 'required',
            'Shipment.reciver_address' => 'required|string|min:8',
            // 'Shipment.from_country_id' => 'required',
            // 'Shipment.to_country_id'   => 'required',
            // 'Shipment.from_state_id'   => 'required',
            'Shipment.to_state_id'     => 'required',
            // 'Shipment.from_area_id'    => 'required',
            'Shipment.to_area_id'      => 'required',
            'Shipment.payment_type'    => 'required',
            'Shipment.payment_method_id' => 'required',
        ]);
        $ClientController = new ClientController(new AclRepository);

        $shipment = $request->Shipment;

        if($request->if_have_account == '1')
        {
            $client = Client::where('email', $request->client_email)->first();
            Auth::loginUsingId($client->user_id);
        }elseif($request->if_have_account == '0'){
            // Add New Client

            $request->request->add(['name' => $request->client_name ]);
            $request->request->add(['email' => $request->client_email ]);
            $request->request->add(['password' => $request->client_password ]);
            $request->request->add(['responsible_mobile' => $request->Shipment['client_phone'] ]);
            $request->request->add(['responsible_name' => $request->client_name ]);
            $request->request->add(['national_id' => $request->national_id ?? '' ]);
            $request->request->add(['branch_id' => $request->Shipment['branch_id'] ]);
            $request->request->add(['terms_conditions' => '1' ]);
            $client = $ClientController->registerStore($request ,true);
        }

        if($client)
        {
            $shipment['client_id']    = $client->id;
            $shipment['client_phone'] = $client->responsible_mobile;

            // Add New Client Address
            $request->request->add(['client_id' => $client->id ]);
            $request->request->add(['address' => $request->client_address ]);
            $request->request->add(['country' => $request->Shipment['from_country_id'] ]);
            $request->request->add(['state'   => $request->Shipment['from_state_id'] ]);
            if(isset($request->area))
            {
                $request->request->add(['area' => $request->Shipment['from_area_id'] ]);
            }
            $new_address        = $ClientController->addNewAddress($request , $calc = true);
            if($new_address)
            {
                $shipment['client_address'] = $new_address->id;
            }

        }
        $request->Shipment = $shipment;
        $model = $this->storeShipment($request);
        return redirect()->route('shipments.show', $model->id)->with(['message_alert' => __('cargo::messages.created')]);
    }

    public function ajaxGetShipmentByBarcode(Request $request)
    {
        $apihelper = new ApiHelper();
        $user = $apihelper->checkUser($request);

        if($user){
            $userClient = Client::where('user_id',$user->id)->first();
            $barcode    = $request->barcode;
            $shipment   = Shipment::where('client_id', $userClient->id)->where('code' , $barcode)->first();
            return response()->json($shipment);
        }else{
            return response()->json(['message' => 'Not Authorized'] );
        }
    }

    public function shipmentsReport(ShipmentsDataTable $dataTable , $status = 'all' , $type = null)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.shipments_report')
            ]
        ]);

        $data_with = [];
        $share_data = array_merge(get_class_vars(ShipmentsDataTable::class), $data_with);

        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return $dataTable->render('cargo::'.$adminTheme.'.pages.shipments.report', $share_data);
    }

    public function deleteExternColi($company_id, $tracking)
    {
        if($tracking && $company_id && $company_id !=0){
            $company = Company::find($company_id);
            if($company != null){
                $apiModel = ApiModel::find($company->model_id);
                if ($apiModel != null) {
                    try {
                        $response = Http::post($company->delete_order, [$apiModel->tracking => $tracking, $apiModel->api_token => $company->api_token, $apiModel->user_guid  => $company->user_guid,]);
                    } catch (\Throwable $th) {}
                }
            }
        }
    }

    public function searchJsonValue($data , $searchKey)
    {
        if(is_array($data) || is_object($data)){
            foreach($data as $key => $value){
                if(is_array($value) || is_object($value)){
                    $result = $this->searchJsonValue($value, $searchKey);
                    if($result !== null){
                        return $result ;
                    }
                }elseif($key == $searchKey){
                    return $value ;
                }
            }
        }
        return null ;
    }
    public function searchJsonValueEnd($data , $searchKey)
    {
        if(is_array($data) || is_object($data)){
            if(is_array($data)){
                $data = end($data);
            }
            foreach($data as $key => $value){
                if(is_array($value) || is_object($value)){
                  if(is_array($data)){
                		$data = end($data);
            		}
                    $result = $this->searchJsonValue($value, $searchKey);
                    if($result !== null){
                        return $result ;
                    }
                }elseif($key == $searchKey){
                    return $value ;
                }
            }
        }
        return null ;
    }


/**
 * @return Renderable
 */


    public function createExternColi($model, $packages)
    {

        if($model->company && $model->company !=0){

            $company = Company::find($model->company);
            if($company != null){

                $apiModel = ApiModel::find($company->model_id);
            }
            if ($apiModel != null) {
                $url = $company->create_order ;//   'https://app.noest-dz.com/api/public/create/order';
                $api_token = $company->api_token; // get_ship_setting('nw_api_token');
                $guid = $company->user_guid;  //   get_ship_setting('nw_user_guid');
                $product = '';
                if (isset($packages)) {
                    if (!empty($packages)) {
                        $counter = 0;
                        foreach($packages as $package){
                            $product .= ($counter + 1).'.'.$package['description'] . ' (' .  $package['qty'] . ') ' . PHP_EOL;
                            $counter++;
                        }
                    }
                }
                $type_id = explode("/", $apiModel->type_id);

                $data = [
                    $apiModel->api_token    => $api_token,
                    $apiModel->user_guid    => $guid,
                    $apiModel->reference    => $model->code,
                    $apiModel->client       => $model->reciver_name,
                    $apiModel->phone        => $model->reciver_phone,
                    $apiModel->adresse      => $model->reciver_address ?? Area::find($model->to_area_id)->name,
                    $apiModel->wilaya_id    => $model->to_state_id,
                    $apiModel->commune      => Area::find($model->to_area_id)->name,
                    $apiModel->montant      => ($model->payment_type == 2)?($model->amount_to_be_collected):($model->amount_to_be_collected + $model->shipping_cost),
                    $apiModel->produit      => ($product != '')?$product:'package',
                    $type_id[0]      		=> $type_id[1]?$type_id[1]:1,
                    $apiModel->poids        => (int) $model->total_weight,
                    $apiModel->stop_desk    => ($model->delivery_type == Shipment::DESK) ? 1 : 0,
                    $apiModel->stock        => 0,

                ];

                try {

                  $response = Http::withHeaders([
                                      $apiModel->api_token    => $api_token,
                                      $apiModel->user_guid    => $guid
                                  ])
                                  ->withBody(json_encode(['Colis' => [$data]]), 'application/json')
                                  ->post($url,$data);
								if (!($response->successful() && $response->status() == 200)) {
                                    $response = Http::post($url, $data);
                                }

                    if ($response->successful() && $response->status() == 200) {
                        $model->track = $this->searchJsonValue($response->json(), $apiModel->tracking);
                      	//$model->track = $response->json($apiModel->tracking)?$response->json($apiModel->tracking) : $response->json()['Colis'][0][$apiModel->tracking];

                        if (!$model->save()) {
                            return response()->json(['message' => new \Exception()] );
                        }
                    } else {
                        $message = "Something went wrong With ".$apiModel->name." Api! : ".$response->body() ;
                      	return ['error' => true,'message' =>$message] ;
                        // return back()->with(['error_message_alert' => __($message)]);
                       // return response()->json(["status" => false, "message" => "Something went wrong With ".$apiModel->name." Api!" . $response->body()]);
                    }
                } catch (\Throwable $th) {
                    $message = "Cant be created ".$apiModel->name." Api! error :". $th ;
                    return ['error' => true,'message' =>$message] ;
                    // return back()->with(['error_message_alert' => __($message)]);
                    // return response()->json(["status" => false, "message" => "Cant be created ".$apiModel->name." Api!"]);
                }
            }else{
                $message = "Cant find Api!";
                return ['error' => true,'message' =>$message] ;
              
                // return back()->with(['error_message_alert' => __($message)]);
               // return response()->json(["status" => false, "message" => "Cant find Api!"]);
            }
        }
      return ['error' => false,'message' =>"Done!"] ;
    }

    public function getStatutExternColi($company_id, $tracking)
    {
        if($tracking && $company_id && $company_id !=0){
            $company = Company::find($company_id);
            if($company != null){
                $apiModel = ApiModel::find($company->model_id);
                if ($apiModel != null) {
                    try {
                        $activity = json_decode($apiModel->activity,true);
                        $data = [$apiModel->tracking.'s' => [$tracking] , $apiModel->tracking => $tracking, $apiModel->api_token => $company->api_token, $apiModel->user_guid  => $company->user_guid];
                        $data_body = [$apiModel->tracking => $tracking];
                        $response = Http::withHeaders([
                                      $apiModel->api_token    => $company->api_token,
                                      $apiModel->user_guid    => $company->user_guid
                                  ])
                                  ->withBody(json_encode(['Colis' => [$data_body]]), 'application/json')
                                  ->post($company->tracking_order, $data);
								if (!($response->successful() && $response->status() == 200)) {
                                    $response = Http::post($company->tracking_order, $data);
                                }
                        if ($response->successful() && $response->status() == 200) {

                            // return $response->body();
                            // $res = json_decode($response, true); 'COL-19B-03057824'

                            // print_r(current(array_keys($response->json())));
                            // exit;

                            return ['response' => $response->body(), 'Situation' => $this->searchJsonValueEnd($response->json(), $activity['KEY'])] ;

                        } else {
                            $message = "Something went wrong With ".$apiModel->name." Api! : ".$response->body() ;
                            return ['response' => $message , 'Situation' => null];
                        }
                    } catch (\Throwable $th) {
                        return $th->getMessage();
                    }
                }
            }
        }
    }

    public function getFinalStatutExternColi(Request $request = null)
    {
        if($request){
            $shipments_ = json_decode($request->checked_ids, true);
        }else{
            $shipments_ = [];
        }
        $action = new StatusManagerHelper();
        if (count($shipments_) <= 0 && auth()->user()->role == 4){

                $userClient = Client::where('user_id',auth()->user()->id)->first();
                $shipments = Shipment::where('client_id',$userClient->id)
                                        ->where('status_id','!=',Shipment::DELIVERED_STATUS)
                                        ->where('status_id','!=',Shipment::RETURNED_ON_RECEIVER)
                                        ->where('status_id','!=',Shipment::CLOSED_STATUS)
                                        ->where('track','!=',null)
                                        ->take(6)->get();
        }else{
            $shipments = Shipment::whereIn('id', $shipments_)->get();
        }


        foreach ($shipments as $shipment) {

            $tracking = $shipment->track ;
            $company_id = $shipment->company ;
            if($tracking && $company_id && $company_id !=0){
                $company = Company::find($company_id);
                if($company != null){
                    $apiModel = ApiModel::find($company->model_id);
                    $activity = json_decode($apiModel->activity,true);  //explode(",", $apiModel->activity);
                    if ($apiModel != null) {
                        try {
                            $data = [$apiModel->tracking.'s' => [$tracking], $apiModel->tracking => $tracking, $apiModel->api_token => $company->api_token, $apiModel->user_guid  => $company->user_guid];

                            $data_body = [$apiModel->tracking => $tracking];
                            $response = Http::withHeaders([
                                      $apiModel->api_token    => $company->api_token,
                                      $apiModel->user_guid    => $company->user_guid
                                  ])
                                  ->withBody(json_encode(['Colis' => [$data_body]]), 'application/json')
                                  ->post($company->tracking_order, $data);
                          		if (!($response->successful() && $response->status() == 200)) {
                                    $response = Http::post($company->tracking_order, $data);
                                }
                            //  $response = Http::post($company->tracking_order, $data);
                            // if(current(array_keys($response->json())) == $tracking){

                               //    $response->json()[$tracking][$activity['KEY']];

                            // }else{

                            //     $res = $response->json()[$activity['KEY']];
                            // }
                            // if(is_array($res)){
                            //     $res = json_encode(end($res));
                            // }

                            if ($response->successful() && $response->status() == 200) {
                              $res = $this->searchJsonValueEnd($response->json(), $activity['KEY']);
                                if($activity['DELIVERED_STATUS'] && str_contains($res, $activity['DELIVERED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::DELIVERED_STATUS);

                                }elseif($activity['RETURNED_ON_RECEIVER'] && str_contains($res, $activity['RETURNED_ON_RECEIVER'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::RETURNED_ON_RECEIVER);

                                }
                                elseif($activity['ALERT_STATUS'] && str_contains($res, $activity['ALERT_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::ALERT_STATUS);

                                }
                                elseif($activity['RETURNED_STATUS'] && str_contains($res, $activity['RETURNED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::RETURNED_STATUS);

                                }
                                elseif($activity['SUPPLIED_STATUS'] && str_contains($res, $activity['SUPPLIED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::SUPPLIED_STATUS);

                                }
                                elseif($activity['PENDING_STATUS'] && str_contains($res, $activity['PENDING_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::PENDING_STATUS);

                                }
                                elseif($activity['RECIVED_STATUS'] && str_contains($res, $activity['RECIVED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::RECIVED_STATUS);

                                }
                                elseif($activity['CAPTAIN_ASSIGNED_STATUS'] && str_contains($res, $activity['CAPTAIN_ASSIGNED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::CAPTAIN_ASSIGNED_STATUS);

                                }
                                elseif($activity['CLOSED_STATUS'] && str_contains($res, $activity['CLOSED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::CLOSED_STATUS);

                                }
                                elseif($activity['APPROVED_STATUS'] && str_contains($res, $activity['APPROVED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::APPROVED_STATUS);

                                }
                                elseif($activity['REQUESTED_STATUS'] && str_contains($res, $activity['REQUESTED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::REQUESTED_STATUS);

                                }
                                elseif($activity['SAVED_STATUS'] && str_contains($res, $activity['SAVED_STATUS'])){
                                    $response = $action->change_shipment_status([$shipment->id], Shipment::SAVED_STATUS);

                                }

                            }
                        } catch (\Throwable $th) {}
                    }
                }
            }
        }
        return back()->with(['message_alert' => __('Status Updated') ]);
    }

    public function ajaxGetbarcode(Request $request){
        if(auth()->user()->role != 1){
            if(auth()->user()->can('shipments-barcode-scanner') || auth()->user()->role == 3 ){
                $branch_id = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
                if(auth()->user()->role == 0){
                    $staff_id = Staff::where('branch_id',$branch_id)->where('user_id',auth()->user()->id)->pluck('id')->first();
                    if(!isset($staff_id)){
                        return false;
                    }
                }
                $shipment = Shipment::where('code', $request->barcode)->where('status_id', Shipment::SAVED_STATUS)->where('branch_id', $branch_id)->first();
                if($shipment){
                    return true;
                }else {
                    return false;
                }
            }else{
                return false;
            }
        }else{
            $shipment = Shipment::where('code', $request->barcode)->first();
            if($shipment){
                return true;
            }else {
                return false;
            }
        }
    }

    /**
     * Remove multi user from database.
     * @param Request $request
     * @return Renderable
     */
    public function multiDestroy(Request $request)
    {
        $ids = json_decode($request->checked_ids, true);
        $userClient = Client::where('user_id',auth()->user()->id)->first();
        $ids = Shipment::whereIn('id',$ids)->where('status_id',Shipment::DRAFT_STATUS)->where('client_id',$userClient->id)->pluck('id');
        if (count($ids) <= 0) {
            return redirect()->back()->with(['error_message_alert' => __('view.demo_mode')]);
        }

        // $ids = $request->ids;

        Shipment::destroy($ids);
        $pids = PackageShipment::whereIn('shipment_id',$ids)->pluck('id');
        PackageShipment::destroy($pids);
        ClientPackage::whereIn('package_id',$pids)->delete();
        return redirect()->back()->with(['message_alert' => __('cargo::messages.multi_deleted')]);
    }




}
