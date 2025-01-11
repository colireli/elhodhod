<?php

namespace Modules\Cargo\Http\Controllers;

use Modules\Cargo\Entities\Plan;
use Modules\Cargo\Entities\Branch;
use Modules\Cargo\Entities\PlanFee;
use Modules\Cargo\Entities\State;
// add_update_v1
//  START_CODE
use Modules\Cargo\Entities\Area;
use Modules\Cargo\Entities\StopDesk;

use Modules\Cargo\Entities\Company;
use Modules\Cargo\Entities\PlanAreaFee;
use Modules\Cargo\Entities\PlanStopDeskFee;

// END_CODE
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->check() && auth()->user()->role == 1) {
            $plans = Plan::all();
        } elseif (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            $plans = Plan::where('branch_id', $branch)->get();
        } else {
            abort(401);
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $branchs = [];
        if(auth()->check() && auth()->user()->role == 1){
            $branchs = Branch::where('is_archived', 0)->get();
        }elseif(auth()->check() && auth()->user()->role != 3) {
            abort(401);
        }
        $plans = [];
        $companies = [];
        if(auth()->check() && auth()->user()->role == 3){
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            $plans = Plan::where('branch_id', $branch)->get();
            // edit_update_v1
            //  START_CODE
            $companies = Company::where('is_archived',0)->where('branch_id', $branch )->get();
        }

        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.create', compact('plans','companies','branchs'));
        // END_CODE
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($branch);
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
        }elseif(auth()->check() && auth()->user()->role == 1){
            $branch = $request->branch_id ;
        }else{
            abort(401);
        }
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'copy' => ['nullable', 'exists:plans,id'],
        ]);
        $plan = Plan::create([
            'title'     => $request->name,
            'branch_id' => $branch,
        ]);

        @ini_set('max_execution_time', 180);

        if (isset($request->copy) && !empty($request->copy)) {
            $fees = PlanFee::where('plan_id', $request->copy)->get();
            foreach ($fees as $fee) {
                $new_fee = $fee->replicate()->fill([
                    'plan_id' => $plan->id,
                ]);
                $new_fee->save();
                // add_update_v1
                //  START_CODE
                $areafees = PlanAreaFee::where('plan_fee_id', $fee->id)->get();
                foreach ($areafees as $areafee) {
                    $new_area_fee = $areafee->replicate()->fill([
                        'plan_fee_id' => $new_fee->id,
                    ]);
                    $new_area_fee->save();
                }

                $stopdeskfees = PlanStopDeskFee::where('plan_fee_id', $fee->id)->get();
                foreach ($stopdeskfees as $stopdeskfee) {
                    $new_stopdesk_fee = $stopdeskfee->replicate()->fill([
                        'plan_fee_id' => $new_fee->id,
                    ]);
                    $new_stopdesk_fee->save();
                }
                // END_CODE
            }

            return redirect()->route('admin.plan.show', $plan)->with(['message_alert' => __('Plan Created Successefully')]);
        }

        // edit_update_v1
        //  START_CODE
        $states = State::where('country_code','DZ')->pluck('id');


        foreach ($states as $state) {
            $planfee = PlanFee::create([
                'state_id'      => $state,
                'plan_id'       => $plan->id,
                'home_fee'      => $request->home ?? 0,
                'desk_fee'      => $request->desk ?? 0,
                'return_fee'    => $request->return ?? 0,
                'recovery_rate' => $request->rate ?? 0,
                'company' => $request->company ?? 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $areas = Area::where('state_id',$state)->pluck('id');
            $areaFees = [];
            foreach ($areas as $area) {
                $areaFees[] = [
                    'area_id'      => $area,
                    'plan_id'       => $plan->id,
                    'plan_fee_id'    => $planfee->id,
                    'home_fee'      => $request->home ?? 0,
                    'desk_fee'      => $request->desk ?? 0,
                    'return_fee'    => $request->return ?? 0,
                    'recovery_rate' => $request->rate ?? 0,
                    'company' => $request->company ?? 0,
                    'active' => 1,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ];
            }
            PlanAreaFee::insert($areaFees);

            $stopdesks = StopDesk::where('state_id',$state)->pluck('id');
            $stopdeskFees = [];
            foreach ($stopdesks as $stopdesk) {
                $stopdeskFees[] = [
                    'stopdesk_id'      => $stopdesk,
                    'plan_id'       => $plan->id,
                    'plan_fee_id'    => $planfee->id,
                    'home_fee'      => $request->home ?? 0,
                    'desk_fee'      => $request->desk ?? 0,
                    'return_fee'    => $request->return ?? 0,
                    'recovery_rate' => $request->rate ?? 0,
                    'company' => $request->company ?? 0,
                    'active' => 1,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ];
            }
            PlanStopDeskFee::insert($stopdeskFees);
        }
        // END_CODE

        return redirect()->route('admin.plan.show', $plan)->with(['message_alert' => __('Plan Created Successefully')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Modules\Cargo\Entities\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function show(Plan $plan)
    {
        $plan = Plan::with(['fees' => function ($query) {
            $query->orderBy('state_id');
        }])->find($plan->id);
        // edit_update_v1
        //  START_CODE
        $companies = Company::where('is_archived',0)->where('branch_id', $plan->branch_id )->get();
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            if ($branch != $plan->branch_id) {
                abort(401);
            }
        }
        $branchs = [];
        if(auth()->check() && auth()->user()->role == 1){
            $branchs = $branchs = Branch::where('is_archived', 0)->get();
        }

        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.show', compact('plan','companies','branchs'));
        // END_CODE
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  Modules\Cargo\Entities\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function edit(Plan $plan)
    {
        //
    }

    // add_update_v1
    //  START_CODE
    public function getBranchPlan(Request $request){
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            $plans = Plan::where('branch_id', $branch)->get();
        }else{

            $plans = Plan::where('branch_id', $request->id)->get();
        }
        return response()->json($plans);
    }

    public function getBranchCompany(Request $request){
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            $companies = Company::where('is_archived',0)->where('branch_id',$branch)->get();
        }else{
            $companies = Company::where('is_archived',0)->where('branch_id', $request->id)->get();
        }
        return response()->json($companies);
    }
    // END_CODE

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Modules\Cargo\Entities\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Plan $plan)
    {
        // dd($request->all(), $plan);
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            if ($branch != $plan->branch_id) {
                abort(401);
            }
        } elseif(auth()->check() && auth()->user()->role != 1) {
            abort(401);
        }
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $home = $request->home;
        $desk = $request->desk;
        $return = $request->return;
        $recovery_rate = $request->recovery_rate;
        // add_update_v1
        //  START_CODE
        $company = $request->company;
        // END_CODE
        $active = $request->active;
        // if(auth()->check() && auth()->user()->role == 1){
        //     $plan->branch_id = $request->branch_id ;
        // }
        $plan->title = $request->name;
        $plan->save();
        foreach ($home as $key => $home_fee) {
            PlanFee::where('id', $key)
                ->where('plan_id', $plan->id)
                ->update([
                    'home_fee' => $home_fee,
                    'desk_fee' => $desk[$key],
                    'return_fee' => $return[$key],
                    'recovery_rate' => $recovery_rate[$key],
                    // add_update_v1
                    //  START_CODE
                    'company' => $company[$key],
                    // END_CODE
                    'active' => isset($active[$key]) ? 1 : 0,
                ]);
        }
        return redirect()->back()->with(['message_alert' => __('plan updated')]);
    }

    // add_update_v1
    //  START_CODE

    /**
     * Display the specified resource.
     *
     * @param  Modules\Cargo\Entities\PlanFee  $planFee
     * @return \Illuminate\Http\Response
     */
    public function showArea($id)
    {
        $planFee = PlanFee::with(['areas' => function ($query) {
            $query->orderBy('area_id');
        }])->find($id);
        $plan = Plan::find($planFee->plan_id);
        $companies = Company::where('is_archived',0)->where('branch_id', $plan->branch_id )->get();
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            if ($branch != $plan->branch_id) {
                abort(401);
            }
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.showarea', compact('planFee','companies','plan'));

    }

    /**
     * updateArea the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Modules\Cargo\Entities\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function updateArea(Request $request)
    {
        // dd($request->all(), $plan);
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            if ($branch != $request->branch_id) {
                abort(401);
            }
        } elseif(auth()->check() && auth()->user()->role != 1) {
            abort(401);
        }

        if($request->defult && $request->defult == 'defult'){
            $planFee = PlanFee::where('id', $request->plan_fee_id)->first();
            PlanAreaFee::where('plan_fee_id', $request->plan_fee_id)
                    ->update([
                        'home_fee' => $planFee->home_fee,
                        'desk_fee' => $planFee->desk_fee,
                        'return_fee' => $planFee->return_fee,
                        'recovery_rate' => $planFee->recovery_rate,
                        'company' => $planFee->company,
                        'active' => $planFee->active,
                    ]);
        }else{

            $home = $request->home;
            $desk = $request->desk;
            $return = $request->return;
            $recovery_rate = $request->recovery_rate;
            $company = $request->company;
            $active = $request->active;

            foreach ($home as $key => $home_fee) {
                PlanAreaFee::where('id', $key)
                    ->where('plan_fee_id', $request->plan_fee_id)
                    ->update([
                        'home_fee' => $home_fee,
                        'desk_fee' => $desk[$key],
                        'return_fee' => $return[$key],
                        'recovery_rate' => $recovery_rate[$key],
                        'company' => $company[$key],
                        'active' => isset($active[$key]) ? 1 : 0,
                    ]);
            }
        }

        return redirect()->back()->with(['message_alert' => __('Fees updated')]);
    }


    // ----------------

    /**
     * Display the specified resource.
     *
     * @param  Modules\Cargo\Entities\PlanFee  $planFee
     * @return \Illuminate\Http\Response
     */
    public function showStopDesk($id)
    {
        $planFee = PlanFee::with(['stopdesks' => function ($query) {
            $query->orderBy('stopdesk_id');
        }])->find($id);
        $plan = Plan::find($planFee->plan_id);
        $companies = Company::where('is_archived',0)->where('branch_id', $plan->branch_id )->get();
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            if ($branch != $plan->branch_id) {
                abort(401);
            }
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.showstopdesk', compact('planFee','companies','plan'));

    }

    /**
     * updateStopDesk the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Modules\Cargo\Entities\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function updateStopDesk(Request $request)
    {
        // dd($request->all(), $plan);
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            if ($branch != $request->branch_id) {
                abort(401);
            }
        } elseif(auth()->check() && auth()->user()->role != 1) {
            abort(401);
        }

        if($request->defult && $request->defult == 'defult'){
            $planFee = PlanFee::where('id', $request->plan_fee_id)->first();
            PlanStopDeskFee::where('plan_fee_id', $request->plan_fee_id)
                    ->update([
                        'home_fee' => $planFee->home_fee,
                        'desk_fee' => $planFee->desk_fee,
                        'return_fee' => $planFee->return_fee,
                        'recovery_rate' => $planFee->recovery_rate,
                        'company' => $planFee->company,
                        'active' => $planFee->active,
                    ]);
        }else{

            $home = $request->home;
            $desk = $request->desk;
            $return = $request->return;
            $recovery_rate = $request->recovery_rate;
            $company = $request->company;
            $active = $request->active;

            foreach ($home as $key => $home_fee) {
                PlanStopDeskFee::where('id', $key)
                    ->where('plan_fee_id', $request->plan_fee_id)
                    ->update([
                        'home_fee' => $home_fee,
                        'desk_fee' => $desk[$key],
                        'return_fee' => $return[$key],
                        'recovery_rate' => $recovery_rate[$key],
                        'company' => $company[$key],
                        'active' => isset($active[$key]) ? 1 : 0,
                    ]);
            }
        }

        return redirect()->back()->with(['message_alert' => __('Fees updated')]);
    }
    // END_CODE

    /**
     * Remove the specified resource from storage.
     *
     * @param  Modules\Cargo\Entities\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $plan = Plan::find($request->id);
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            if ($branch != $plan->branch_id) {
                abort(401);
            }
        } elseif(auth()->check() && auth()->user()->role != 1) {
            abort(401);
        }
        try{
            // add_update_v1
            //  START_CODE
            $plan->areas()->delete();
            $plan->stopdesks()->delete();
            // END_CODE
            $plan->fees()->delete();
            $plan->delete();
        } catch (\Exception $ex) {
            return redirect()->route('admin.plan.index')->with(['error_message_alert' => __('cargo::messages.something_wrong')]);
        }
        return redirect()->route('admin.plan.index')->with(['message_alert' => __('Plan Deleted Successefully')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function createArea(Request $request)
    {
        if(auth()->check() && (auth()->user()->role != 1 && auth()->user()->role != 3)) {
            abort(401);
        }
        try{
           
            $planfee = PlanFee::find($request->id);
               if($planfee){
                $area_ =  Area::create([
                    'country_id' => 4,
                    'state_id'=> $planfee->state_id,
                    'name' => $request->area,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
                
                if($area_){
                    PlanAreaFee::insert([
                        'area_id'      => $area_->id,
                        'plan_id'       => $planfee->plan_id,
                        'plan_fee_id'    => $planfee->id,
                        'home_fee' => $planfee->home_fee,
                        'desk_fee' => $planfee->desk_fee,
                        'return_fee' => $planfee->return_fee,
                        'recovery_rate' => $planfee->recovery_rate,
                        'company' => $planfee->company,
                        'active' => $planfee->active,
                        'created_at'    => now(),
                        'updated_at'    => now()
                        ]);
                }
                
               }        
           
        } catch (\Exception $ex) {
            echo $ex->getMessage() ;
            dd($ex->getMessage());
            Log::debug($e->getMessage());
            return false ;
        }
        
        return true ;
    } 

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStopDesk(Request $request)
    {
        if(auth()->check() && (auth()->user()->role != 1 && auth()->user()->role != 3)) {
            abort(401);
        }
        try{
           
            $planfee = PlanFee::find($request->id);
               if($planfee){
                $stopdesk_ =  StopDesk::create([
                    'country_id' => 4,
                    'state_id'=> $planfee->state_id,
                    'name' => $request->stopdesk,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
                
                if($stopdesk_){
                    PlanStopDeskFee::insert([
                        'stopdesk_id'      => $stopdesk_->id,
                        'plan_id'       => $planfee->plan_id,
                        'plan_fee_id'    => $planfee->id,
                        'home_fee' => $planfee->home_fee,
                        'desk_fee' => $planfee->desk_fee,
                        'return_fee' => $planfee->return_fee,
                        'recovery_rate' => $planfee->recovery_rate,
                        'company' => $planfee->company,
                        'active' => $planfee->active,
                        'created_at'    => now(),
                        'updated_at'    => now()
                        ]);
                }
                
               }        
           
        } catch (\Exception $ex) {
            echo $ex->getMessage() ;
            dd($ex->getMessage());
            Log::debug($e->getMessage());
            return false ;
        }
        
        return true ;
    } 
}
