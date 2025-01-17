<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cargo\Http\DataTables\StopDesksDataTable;
use Modules\Cargo\Http\Requests\PackageRequest;
use Modules\Cargo\Entities\Package;
use Modules\Cargo\Entities\Country;
use Modules\Cargo\Entities\State;
use Modules\Cargo\Entities\Company;
use Modules\Cargo\Entities\StopDesk;
use Modules\Cargo\Entities\PlanStopDeskFee;
use Modules\Cargo\Http\Requests\StopDeskRequest;
use Modules\Acl\Repositories\AclRepository;

class StopDeskController extends Controller
{

    private $aclRepo;

    public function __construct(AclRepository $aclRepository)
    {
        $this->aclRepo = $aclRepository;
        // check on permissions
        // $this->middleware('can:manage-stopdesks');
        // $this->middleware('can:view-stopdesks')->only('index');
        // $this->middleware('can:view-stopdesks')->only('show');
        // $this->middleware('can:create-stopdesks')->only('create', 'store');
        // $this->middleware('can:edit-stopdesks')->only('edit', 'update');
        // $this->middleware('can:delete-stopdesks')->only('delete', 'multiDestroy');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(StopDesksDataTable $dataTable)
    {

        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('stopdesks management'),
            ],
        ]);
        $data_with = [];
        $share_data = array_merge(get_class_vars(StopDesksDataTable::class), $data_with);
        // dd('end');
 
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return $dataTable->render('cargo::'.$adminTheme.'.pages.stopdesks.index', $share_data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('stopdesks management'),
                'path' => fr_route('stopdesks.index')
            ],
            [
                'name' => __('add stopdesk'),
            ],
        ]);
        $countries = Country::where('covered',1)->get();
        $companies = Company::where('is_archived', 0)->get();
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.stopdesks.create', compact('countries','companies'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StopDeskRequest $request)
    {
        $data = $request->only(['state_id','country_id','company_id','name','phone','address','reference']);
        // $data['name'] = json_encode($request->name);
        $stopdesk = StopDesk::create($data);
        return redirect()->route('stopdesks.index')->with(['message_alert' => __('cargo::messages.created')]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('stopdesks management'),
                'path' => fr_route('stopdesks.index')
            ],
            [
                'name' => __('edit stopdesk'),
            ],
        ]);
        $countries = Country::where('covered',1)->get();
        $stopdesk   = StopDesk::findOrFail($id);
        $states = State::where('country_id',$stopdesk->state->country_id)->where('covered',1)->get();
        $companies = Company::where('is_archived', 0)->get();
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.stopdesks.edit')->with(['model' => $stopdesk, 'countries' => $countries , 'states' => $states,'companies' => $companies]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(StopDeskRequest $request, $id)
    {
        if (env('DEMO_MODE') == 'On') {
            return redirect()->back()->with(['error_message_alert' => __('view.demo_mode')]);
        }

        $stopdesk = StopDesk::findOrFail($id);
        $data = $request->only(['state_id','country_id','company_id','name','phone','address','reference']);
        $data['name'] = json_encode($request->name);
        $stopdesk->update($data);
        return redirect()->route('stopdesks.index')->with(['message_alert' => __('cargo::messages.saved')]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (env('DEMO_MODE') == 'On') {
            return redirect()->back()->with(['error_message_alert' => __('view.demo_mode')]);
        }
        $plan_ids = PlanStopDeskFee::where('stopdesk_id',$id)->get();
        StopDesk::destroy($id);
        PlanStopDeskFee::destroy($plan_ids);
        return response()->json(['message' => __('cargo::messages.deleted')]);
    }

    /**
     * Remove multi user from database.
     * @param Request $request
     * @return Renderable
     */
    public function multiDestroy(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            return redirect()->back()->with(['error_message_alert' => __('view.demo_mode')]);
        }

        $ids = $request->ids;
        $plan_ids = PlanStopDeskFee::whereIn('stopdesk_id', $ids)->pluck('id')->get();
        StopDesk::destroy($ids);
        PlanStopDeskFee::destroy($plan_ids);
        dd($reques);
        return response()->json(['message' => __('cargo::messages.multi_deleted')]);
    }
}
