<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Cargo\Entities\Company;
use Modules\Cargo\Entities\Branch;
use Modules\Cargo\Entities\CompanyPlan;
use Modules\Cargo\Entities\CompanyPlanFee;
use Modules\Cargo\Entities\State;
use Illuminate\Http\Request;
use Auth;

class CompanyPlanController extends Controller
{
    public function __construct()
    {
        // $this->middleware('user_role:admin');
        // $this->middleware('log')->only('index');
        // $this->middleware('subscribed')->except('store');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->check() && auth()->user()->role != 1) {
            if (auth()->check() && auth()->user()->role == 3) {
                $branch_id = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
                $plans = CompanyPlan::where('branch_id', $branch_id)->get();
            }else{
                abort(403);
            }
        }else{
            $plans = CompanyPlan::all();
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.company_index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (auth()->check() && auth()->user()->role != 1) {
            if (auth()->check() && auth()->user()->role == 3) {
                $branchs = Branch::where('user_id',auth()->user()->id)->first();
                $plans = CompanyPlan::where('branch_id', $branchs->id)->get();
            }else{
                abort(403);
            }
        }else{
            $plans = CompanyPlan::all();
            $branchs = Branch::where('is_archived', 0)->get();
        }
        // if (auth()->check() && auth()->user()->role != 1) {
        //     abort(403);
        // }
        // $plans = CompanyPlan::all();
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.company_create', compact('plans','branchs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (auth()->check() && !(auth()->user()->role == 1 || auth()->user()->role == 3)) {
            abort(403);
        }

        @ini_set('max_execution_time', 180);
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'copy' => ['nullable','exists:company_plans,id'],
        ]);
        if (auth()->check() && auth()->user()->role == 3) {
            $branch_id =  Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
        }else{
            $branch_id =  $request->branch;
        }
        $companyPlan = CompanyPlan::create([
            'title' => $request->name,
            'branch_id' => $branch_id,
        ]);
        if (isset($request->copy) && !empty($request->copy)) {
            $fees = CompanyPlanFee::where('plan_id', $request->copy)->get();
            foreach ($fees as $fee) {
                $new_fee = $fee->replicate()->fill([
                    'plan_id' => $companyPlan->id,
                ]);
                $new_fee->save();
            }
        } else {
            $fees = [];

            $states = State::where('country_code','DZ')->pluck('id');


            foreach ($states as $state) {
                $fees[] = [
                    'state_id'      => $state,
                    'plan_id'       => $companyPlan->id,
                    'home_fee'      => $request->home ?? 0,
                    'desk_fee'      => $request->desk ?? 0,
                    'return_fee'    => $request->return ?? 0,
                    'recovery_rate' => $request->rate ?? 0,
                    'active'        => 1,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ];
            }

            CompanyPlanFee::insert($fees);
        }

        flash(__('Plan Created Successefully'))->success();
        return redirect()->route('admin.company_plan.show', $companyPlan);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompanyPlan  $companyPlan
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyPlan $companyPlan)
    {
        if (auth()->check() && !(auth()->user()->role == 1 || auth()->user()->role == 3)) {

            abort(403);
        }elseif(auth()->user()->role == 3 && $companyPlan->branch_id != Branch::where('user_id',auth()->user()->id)->pluck('id')->first()){
           abort(403);
        }
        $plan = CompanyPlan::with(['fees' => function ($query) {
            $query->orderBy('state_id');
        }])->findOrFail($companyPlan->id);
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.company_show', compact('plan'));
    }

    public function plan_for_company($id)
    {
        $user = Auth::user();
        if (!(auth()->user()->role == 3 || auth()->user()->role == 1)) {
            abort(403);
        }
        $companyPlan = Company::findOrFail($id)->plan_id;
        $plan = CompanyPlan::with(['fees' => function ($query) {
            $query->orderBy('state_id');
        }])->findOrFail($companyPlan);

        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.plans.plan_for_company', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompanyPlan  $companyPlan
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyPlan $companyPlan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanyPlan  $companyPlan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CompanyPlan $companyPlan)
    {
        if (auth()->check() && !(auth()->user()->role == 1 || auth()->user()->role == 3)) {
            abort(403);
        }
        // dd($request->all(), $companyPlan);
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);
        $home = $request->home;
        $desk = $request->desk;
        $return = $request->return;
        $recovery_rate = $request->recovery_rate;
        $active = $request->active;
        $companyPlan->title = $request->name;
        $companyPlan->save();
        foreach ($home as $key => $home_fee) {
            CompanyPlanFee::where('id', $key)
                ->where('plan_id', $companyPlan->id)
                ->update([
                    'home_fee'      => $home_fee,
                    'desk_fee'      => $desk[$key],
                    'return_fee'    => $return[$key],
                    'recovery_rate' => $recovery_rate[$key],
                    'active'        => isset($active[$key]) ? 1 : 0,
                ]);
        }
        flash(__('Fees updated for ') . $companyPlan->title)->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompanyPlan  $companyPlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (auth()->check() && !(auth()->user()->role == 1 || auth()->user()->role == 3)) {
            abort(403);
        }
        $companyPlan = CompanyPlan::findOrFail($request->id);
        $companyPlan->fees()->delete();
        $companyPlan->delete();
        return redirect()->route('admin.company_plan.index')->with(['message_alert' => __('Plan Deleted Successefully')]);
    }
}
