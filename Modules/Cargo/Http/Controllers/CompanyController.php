<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cargo\Entities\Company;
use Modules\Cargo\Entities\ApiModel;
use Modules\Cargo\Entities\PlanFee;
use Modules\Cargo\Entities\Branch;
use Modules\Cargo\Entities\CompanyPlan;
use DB;
use Auth;
class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            $companies = Company::where(['is_archived'=>0,'branch_id' => $branch])->orderBy('name')->paginate(30);
        }elseif(auth()->check() && auth()->user()->role == 1){
            $companies = Company::where('is_archived',0)->orderBy('name')->paginate(30);
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.company.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $apiModels = ApiModel::where('is_archived', 0)->get();
        $branchs = Branch::where('is_archived', 0)->get();
        if (auth()->check() && auth()->user()->role == 3) {
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            $plans = CompanyPlan::where('branch_id', $branch)->get();
        }elseif(auth()->check() && auth()->user()->role == 1){
            $plans = CompanyPlan::all();
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.company.create', compact('apiModels','branchs','plans'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(__('This action is disabled in demo mode'))->error();
            return back();
        }
        $request->validate([
            'company.name' => 'required|unique:companies,name',
            'company.api_token' => 'required',
            'company.model_id' => 'required',
            'company.branch_id' => 'required',
            'company.plan_id' => 'required',
        ]);
        try{
			DB::beginTransaction();
			$model = new Company();

			$model->fill($_POST['company']);
			$model->code = -1;
           // $model->img = $_POST['img'];

            if (auth()->check() && auth()->user()->role == 3) {
                $model->branch_id =  Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
            }

			if (!$model->save()){
				throw new \Exception();
			}
			$model->code = $model->id;
			if (!$model->save()){
				throw new \Exception();
			}

			DB::commit();
            return redirect()->route('admin.company.index')->with(['message_alert' => __('Company Created Successefully')]);
		}catch(\Exception $e){
			DB::rollback();
			print_r($e->getMessage());
			exit;

			flash(__("Error"))->error();
            return redirect()->route('admin.company.index')->with(['error_message_alert' => __('Error')]);
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
        if (env('DEMO_MODE') == 'On') {
            flash(__('This action is disabled in demo mode'))->error();
            return back();
        }
        $company = Company::where('id', $id)->first();
        $apiModel = ApiModel::where('id', $company->model_id)->first();
        $branch = Branch::where('id', $company->branch_id)->first();
        $plan = CompanyPlan::where('id', $company->plan_id)->first();
        if($company != null){
            $adminTheme = env('ADMIN_THEME', 'adminLte');
            return view('cargo::'.$adminTheme.'.pages.company.show',compact('company','apiModel','branch','plan'));
        }
        abort(404);
    }

    public function showFees($id)
    {
        $fees = PlanFee::with('plan')->where(['company' => $id, 'active' => 1 ])->orderBy('state_id')->get();
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.company.showfees', compact('fees'));
    }

    // add_update_v1
    //  START_CODE
    public function getCompanyPlan(Request $request){
        $plans = CompanyPlan::where('branch_id', $request->id)->get();
        return response()->json($plans);
    }
    // END_CODE

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(__('This action is disabled in demo mode'))->error();
            return back();
        }
        $company = Company::where('id', $id)->first();
        if($company != null){
            $apiModels = ApiModel::where('is_archived', 0)->get();
            $branchs = Branch::where('is_archived', 0)->get();
            if (auth()->check() && auth()->user()->role == 3) {
                $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
                $plans = CompanyPlan::where('branch_id', $branch)->get();
            }elseif(auth()->check() && auth()->user()->role == 1){
                $plans = CompanyPlan::all();
            }
            $adminTheme = env('ADMIN_THEME', 'adminLte');
            return view('cargo::'.$adminTheme.'.pages.company.edit',compact('company', 'apiModels','branchs','plans'));
        }
        abort(404);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(__('This action is disabled in demo mode'))->error();
            return back();
        }
        try{
			DB::beginTransaction();
			$model = Company::find($request->id);


			$model->fill($_POST['company']);
			$model->code = -1;
           // $model->img = $_POST['img'];

			if (!$model->save()){
				throw new \Exception();
			}
			$model->code = $model->id;
			if (!$model->save()){
				throw new \Exception();
			}

			DB::commit();
            return redirect()->route('admin.company.index')->with(['message_alert' => __('Company Updated Successefully')]);
		}catch(\Exception $e){
			DB::rollback();
			print_r($e->getMessage());
			exit;

            return redirect()->route('admin.company.index')->with(['error_message_alert' => __('Error')]);
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(__('This action is disabled in demo mode'))->error();
            return back();
        }
        if (auth()->check() && !(auth()->user()->role == 1 || auth()->user()->role == 3)) {
            abort(403);
        }
        $model = company::findOrFail($request->id);
        $model->is_archived = 1;
        $model->name = $model->name.'_is_archived';
        if($model->save()){
            return redirect()->route('admin.company.index')->with(['message_alert' => __('Company has been deleted successfully')]);
        }
        return redirect()->route('admin.company.index')->with(['error_message_alert' => __('Error')]);
    }

}
