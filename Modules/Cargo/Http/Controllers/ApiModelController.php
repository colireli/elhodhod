<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Cargo\Entities\ApiModel;
use Illuminate\Routing\Controller;
use DB;
use Auth;
class ApiModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $apiModels = ApiModel::where('is_archived',0)->orderBy('name')->paginate(30);
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.apimodel.index', compact('apiModels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (auth()->check() && auth()->user()->role != 1) {
            abort(401);
        }
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.apimodel.create');
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
            'apiModel.activity' => 'required|array|min:12',
        ]);
        try{
			DB::beginTransaction();
			$model = new ApiModel();


			$model->fill($_POST['apiModel']);
			$model->code = -1;
            $model->activity = json_encode($model->activity);
            // $model->img = $_POST['img'];

            // SAVED_STATUS
            // REQUESTED_STATUS
            // APPROVED_STATUS
            // CLOSED_STATUS
            // CAPTAIN_ASSIGNED_STATUS
            // RECIVED_STATUS
            // DELIVERED_STATUS
            // PENDING_STATUS
            // SUPPLIED_STATUS
            // RETURNED_STATUS
            // RETURNED_ON_RECEIVER
            // ALERT_STATUS

			if (!$model->save()){
				throw new \Exception();
			}
			$model->code = $model->id;
			if (!$model->save()){
				throw new \Exception();
			}

			DB::commit();
            flash(__("Api Model added successfully"))->success();
            $route = 'admin.apimodel.index';
            return redirect()->route($route)->with(['message_alert' => __('cargo::messages.created')]);
		}catch(\Exception $e){
			DB::rollback();
			print_r($e->getMessage());
			exit;

			flash(__("Error"))->error();
            return back();
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
        $apiModel = ApiModel::where('id', $id)->first();
        if($apiModel != null){
            $adminTheme = env('ADMIN_THEME', 'adminLte');
            return view('cargo::'.$adminTheme.'.pages.apimodel.show',compact('apiModel'));
        }
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (auth()->check() && auth()->user()->role != 1) {
            abort(401);
        }

        if (env('DEMO_MODE') == 'On') {
            flash(__('This action is disabled in demo mode'))->error();
            return back();
        }
        $apiModel = ApiModel::where('id', $id)->first();
        if($apiModel != null){
            $apiModel->activity = json_decode($apiModel->activity);
            $adminTheme = env('ADMIN_THEME', 'adminLte');
            return view('cargo::'.$adminTheme.'.pages.apimodel.edit',compact('apiModel'));
        }
        abort(404);
    }

    public function updateApiModelStatus(Request $request)
    {
        // dd($request->all());

         try{
			DB::beginTransaction();
            $model= ApiModel::where('id', $request->id)->update(['is_archived'=> $request->status]);
			if (!$model){
				throw new \Exception();
			}
			DB::commit();
            return response('true');

		}catch(\Exception $e){
			DB::rollback();
			print_r($e->getMessage());
			exit;

			flash(__("Error"))->error();
            return back();
		}
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
        $request->validate([
            'apiModel.activity' => 'required|array|min:12',
        ]);
        try{
			DB::beginTransaction();
			$model = ApiModel::find($request->id);


			$model->fill($_POST['apiModel']);
			$model->code = -1;
            $model->activity = json_encode($model->activity);
            // $model->img = $_POST['img'];

			if (!$model->save()){
				throw new \Exception();
			}
			$model->code = $model->id;
			if (!$model->save()){
				throw new \Exception();
			}

			DB::commit();
            $route = 'admin.apimodel.index';
            return redirect()->route($route)->with(['message_alert' => __('Api Model updated successfully')]);
		}catch(\Exception $e){
			DB::rollback();
			print_r($e->getMessage());
			exit;

			flash(__("Error"))->error();
            return back();
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
        if (auth()->check() && !(auth()->user()->role == 1)) {
            abort(403);
        }

        $model = apiModel::findOrFail($request->id);
        $model->is_archived = 1;
        if($model->save()){
            $route = 'admin.apimodel.index';
            return redirect()->route($route)->with(['message_alert' => __('Api model has been deleted successfully')]);
        }
        return back();
    }

}
