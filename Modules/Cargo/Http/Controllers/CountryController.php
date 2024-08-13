<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cargo\Entities\Country;
use Modules\Cargo\Entities\State;
use Modules\Cargo\Entities\Cost;
use Modules\Cargo\Entities\ShipmentSetting;
use Modules\Cargo\Entities\Area;
use Modules\Acl\Repositories\AclRepository;
use Modules\Cargo\Entities\Client;
use Modules\Cargo\Entities\PlanAreaFee;
use Modules\Cargo\Entities\PlanFee;

class CountryController extends Controller
{
    private $aclRepo;

    public function __construct(AclRepository $aclRepository)
    {
        $this->aclRepo = $aclRepository;
        // check on permissions
        $this->middleware('user_role:1|0')->only('index', 'store', 'covered_states', 'post_covered_states', 'countries_config_costs', 'ajax_countries_costs_repeter', 'post_countries_config_costs');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.covered_places'),
            ]
        ]);

        $items  = Country::all();
        $form_title = __('cargo::view.all_countries');
        $adminTheme = env('ADMIN_THEME', 'adminLte');

        return view(
            'cargo::'.$adminTheme.'.pages.countries.index',
            compact('items', 'form_title')
        );
    }

    public function store(Request $request)
    {
        $countries = Country::where('id','!=',null)->update(['covered' => 0]);

        if(isset($request->covered_items)){
            foreach ($request->covered_items as $country_id) {
                $c = Country::find($country_id);
                $c->covered = 1;
                $c->save();
            }
        }
        return back()->with(['message_alert' => __('cargo::messages.created')]);
    }

    public function covered_states($country_id)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.covered_places'),
                'path' => fr_route('countries.index')
            ],
            [
                'name' => __('cargo::view.covered_states'),
            ],
        ]);
        $items = State::where('country_id', $country_id)->get();
        $country = Country::find($country_id);
        $form_title = __('cargo::view.all_states');
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return  view('cargo::'.$adminTheme.'.pages.states.index', compact('items', 'country' , 'form_title'));
    }

    public function post_covered_states(Request $request , $country_id)
    {
        $states = State::where('country_id', $country_id)->update(['covered' => 0]);

        if(isset($request->covered_items)){
            foreach ($request->covered_items as $state_id) {
                $s = State::find($state_id);
                $s->covered = 1;
                $s->save();
            }
        }
        return back()->with(['message_alert' => __('cargo::messages.created')]);
    }

    public function ajaxGetStates(Request $request)
    {
        $country_id = $_GET['country_id'];
        $states = State::where('country_id', $country_id)->where('covered',1)->get();
        return response()->json($states);
    }
    public function ajaxGetAreas(Request $request)
    {
        $state_id = $request->state_id;
        $areas = Area::where('state_id', $state_id)->get();
        return response()->json($areas);
    }
    public function ajaxGetAreas_(Request $request)
    {

        $plan = Client::find($request->client_id);
        $areas = PlanFee::with(['areas' => function ($query) {
            $query
            ->where('desk_fee',">","0")
            ->where('active',"1")
            ->orderBy('area_id');
        }])
        ->where('state_id',(string) $request->state_id)
        ->where('plan_id',(string) $plan->plan_id)
        ->where('active',"1")->first();


        return response()->json($areas);
    }

    public function countries_config_costs(Request $request)
    {
        $data = $request->validate([
            'from_country' => 'required',
            'to_country' => 'required',
            'from_region' => 'nullable',
            'to_region' => 'nullable',
        ]);
        $from = Country::find($data['from_country']);
        $to = Country::find($data['to_country']);
        $from_cities = State::where('country_id', $from->id)->where('covered', 1)->get();
        $to_cities = State::where('country_id', $to->id)->where('covered', 1)->get();

        if(isset($data['from_region']) && isset($data['to_region'])){
            $from_region = State::where('country_id', $from->id)->where('id', $data['from_region'])->first();
            $to_region = State::where('country_id', $from->id)->where('id', $data['to_region'])->first();;
            $from_areas = Area::where('country_id', $from->id)->where('state_id',$data['from_region'])->get();
            $to_areas = Area::where('country_id', $to->id)->where('state_id', $data['to_region'])->get();

            $region_cost = Cost::where('from_country_id', $from->id)->where('to_country_id', $to->id)->where('from_state_id', $from_region->id)->where('to_state_id', $to_region->id)->where('from_area_id', 0)->where('to_area_id', 0)->first();
            if(!isset($region_cost))
            {

                $region_cost = new Cost();
                $region_cost->from_country_id = $from->id;
                $region_cost->to_country_id = $to->id;
                $region_cost->from_state_id = $from_region->id;
                $region_cost->to_state_id = $to_region->id;
                $region_cost->save();

            }
            $area_costs = Cost::where('from_country_id', $from->id)->where('to_country_id', $to->id)->where('from_state_id', $from_region->id)->where('to_state_id', $to_region->id)->whereIn('from_area_id', $from_areas->pluck('id')->toArray())->whereIn('to_area_id',$to_areas->pluck('id')->toArray())->get();
            $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.countries.costs-repeter', compact('from', 'to', 'from_cities', 'to_cities','from_region','to_region','from_areas','to_areas','region_cost','area_costs'));
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.countries.costs-repeter', compact('from', 'to', 'from_cities', 'to_cities'));
    }

    public function ajax_countries_costs_repeter(Request $request)
    {
        $costBlocks = array();
        $data = $request->validate([
            'from_country' => 'required',
            'to_country' => 'required',
        ]);
        $from = Country::find($data['from_country']);
        $to = Country::find($data['to_country']);
        $from_cities = State::where('country_id', $from->id)->where('covered', 1)->get();
        $to_cities = State::where('country_id', $to->id)->where('covered', 1)->get();
        $from_areas = Area::where('country_id', $from->id)->get();
        $to_areas = Area::where('country_id', $to->id)->get();
        $counter = 0;
        foreach($from_cities as $city)
        {
            $from_areas = Area::where('country_id', $from->id)->where('state_id', $city->id)->get();
            foreach($from_areas as $from_area){

                foreach ($to_cities as $to_city){
                    $to_areas = Area::where('country_id', $to->id)->where('state_id', $to_city->id)->get();
                    foreach ($to_areas as $to_area){
                        $from_costs = Cost::where('from_country_id', $from->id)->where('to_country_id', $to->id)->where('from_state_id', $city->id)->where('to_state_id', $to_city->id)->where('from_area_id', $from_area->id)->where('to_area_id', $to_area->id)->first();
                        if($from_costs != null){
                            array_push($costBlocks,['from_country'=>$from->name,'from_country_id'=>$from->id,'to_country'=>$to->name,'to_country_id'=>$to->id,'from_state'=>$city->name,'from_state_id'=>$city->id,'to_state'=>$to_city->name,'to_state_id'=>$to_city->id,'from_area'=> $from_area->name ,'from_area_id'=>$from_area->id,'to_area'=>$to_area->name,'to_area_id'=>$to_area->id,'shipping_cost'=>$from_costs->shipping_cost,'mile_cost'=>$from_costs->mile_cost,'tax'=>$from_costs->tax,'return_cost'=>$from_costs->return_cost,'return_mile_cost'=>$from_costs->return_mile_cost,'insurance'=>$from_costs->insurance]);
                        }else
                        {
                            array_push($costBlocks,['from_country'=>$from->name,'from_country_id'=>$from->id,'to_country'=>$to->name,'to_country_id'=>$to->id,'from_state'=>$city->name,'from_state_id'=>$city->id,'to_state'=>$to_city->name,'to_state_id'=>$to_city->id,'from_area'=> $from_area->name, true ,'from_area_id'=>$from_area->id,'to_area'=>$to_area->name,'to_area_id'=>$to_area->id,'shipping_cost'=>0,'tax'=>0,'return_cost'=>0,'insurance'=>0,'return_mile_cost'=>0,'mile_cost'=>0]);
                        }
                    }

                }

            }

        }
        $costStates = array();
        foreach($from_cities as $index => $city){
            $state_cost = Cost::where('from_country_id',$from->id)->where('to_country_id',$to->id)->where('from_state_id',$city->id)->where('to_state_id',$to_cities[$index]->id)->where('from_area_id',0)->where('to_area_id',0)->first();
            if($state_cost){
                array_push($costStates,$state_cost);
            }
        };
        return response()->json([$costBlocks,$costStates]);
    }

    public function post_countries_config_costs(Request $request)
    {

        $counter = 0;
        $from_country = $request->from_country_h[$counter];
        $to_country = $request->to_country_h[$counter];
        $from_state = $request->from_state[$counter];
        $to_state = $request->to_state[$counter];

        $tax = $request->tax[$counter];
        $insurance = $request->insurance[$counter];
        $newCost = Cost::where('from_country_id', $from_country)->where('to_country_id', $to_country)->first();
        $areaNewCost = Cost::where('from_country_id', $from_country)->where('to_country_id', $to_country)->where('from_state_id', $from_state)->where('to_state_id', $to_state)->where('from_area_id', 99)->where('to_area_id', 93)->first();

        if(!isset($newCost))
        {
            $newCost = new Cost();
            $newCost->from_country_id = $from_country;
            $newCost->to_country_id = $to_country;
        }

        if(ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
        {
            $shipping_cost = $request->shipping_cost[$counter];
            $return_cost = $request->return_cost[$counter];

            $newCost->shipping_cost = $shipping_cost;
            $newCost->return_cost = $return_cost;
        }elseif(ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
        {
            $mile_cost = $request->mile_cost[$counter];
            $return_mile_cost = $request->return_mile_cost[$counter];

            $newCost->mile_cost = $mile_cost;
            $newCost->return_mile_cost = $return_mile_cost;
        }
        $newCost->tax = $tax;
        $newCost->insurance = $insurance;
        $newCost->save();

        // START FROM STATE
        foreach ($request->from_state as $key => $state){
            $newCost = Cost::where('from_country_id', $from_country)->where('to_country_id', $to_country)->where('from_state_id', $state)->where('to_state_id', $request->to_state[$key])->where('from_area_id', 0)->where('to_area_id', 0)->first();
            if(!isset($newCost))
            {
                $newCost = new Cost();
                $newCost->from_country_id = $from_country;
                $newCost->to_country_id = $to_country;
                $newCost->from_state_id = $state;
                $newCost->to_state_id   = $request->to_state[$key];
            }

            $tax = $request->state_tax[$counter];
            $insurance = $request->state_insurance[$counter];

            if(ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
            {
                $shipping_cost = $request->state_shipping_cost[$counter];
                $return_cost = $request->state_return_cost[$counter];

                $newCost->shipping_cost = $shipping_cost;
                $newCost->return_cost = $return_cost;
            }elseif(ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
            {
                $mile_cost = $request->state_mile_cost[$counter];
                $return_mile_cost = $request->state_return_mile_cost[$counter];

                $newCost->mile_cost = $mile_cost;
                $newCost->return_mile_cost = $return_mile_cost;
            }
            $newCost->tax = $tax;
            $newCost->insurance = $insurance;
            $newCost->save();
        }
        // START FROM AREAS
        $counter = 1 ;
        foreach ($request->from_area as $key => $cost_data) {
            if ($counter <= (count($request->from_area) )) {

                $from_area;
                $to_area;
                $tax;
                $insurance;

                if(isset($request->from_area[$key])){
                    $from_area = $request->from_area[$key];
                }
                if(isset($request->to_area[$key])){
                    $to_area = $request->to_area[$key];
                }
                if(isset($request->tax[$counter])){
                    $tax = $request->tax[$counter];
                }
                if(isset($request->insurance[$counter])){
                    $insurance = $request->insurance[$counter];
                }

                $areaNewCost = Cost::where('from_country_id', $from_country)->where('to_country_id', $to_country)->where('from_state_id', $from_state)->where('to_state_id', $to_state)->where('from_area_id', $from_area)->where('to_area_id', $to_area)->first();
                if(!isset($areaNewCost))
                {
                    $areaNewCost = new Cost();
                    $areaNewCost->from_country_id = $from_country;
                    $areaNewCost->to_country_id = $to_country;
                    $areaNewCost->from_state_id = $from_state;
                    $areaNewCost->to_state_id = $to_state;
                    $areaNewCost->from_area_id = $from_area;
                    $areaNewCost->to_area_id = $to_area;
                }

                if(ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {
                    $shipping_cost;
                    $return_cost;
                    if(isset($request->shipping_cost[$counter])){
                        $shipping_cost = $request->shipping_cost[$counter];
                    }
                    if(isset($request->return_cost[$counter])){
                        $return_cost = $request->return_cost[$counter];
                    }

                    $areaNewCost->shipping_cost = $shipping_cost;
                    $areaNewCost->return_cost = $return_cost;
                }elseif(ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $mile_cost;
                    $return_mile_cost;
                    if(isset($request->mile_cost[$counter])){
                        $mile_cost = $request->mile_cost[$counter];
                    }
                    if(isset($request->return_mile_cost[$counter])){
                        $return_mile_cost = $request->return_mile_cost[$counter];
                    }

                    $areaNewCost->mile_cost = $mile_cost;
                    $areaNewCost->return_mile_cost = $return_mile_cost;
                }
                $areaNewCost->tax = $tax;
                $areaNewCost->insurance = $insurance;
                $areaNewCost->save();
                $counter++;
            }
        }
        return back()->with(['message_alert' => __('cargo::messages.saved')]);
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

    public function updateStatus(Request $request){

        $country = Country::findOrFail($request->id);
        $country->status = $request->status;
        if($country->save()){
            return 1;
        }
        return 0;
    }
}
