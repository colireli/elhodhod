<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Modules\Cargo\Http\DataTables\ReceiversDataTable;
use Modules\Users\Events\UserCreatedEvent;
use Modules\Users\Events\UserUpdatedEvent;
use Modules\Users\Events\UserAssignedPermissionEvent;
use Modules\Acl\Repositories\AclRepository;
use Modules\Cargo\Entities\Branch;

use Modules\Users\Http\Requests\UserRequest;
use Modules\Users\Http\Requests\AssignPermissionToUserRequest;
use App\Models\User;

use Modules\Cargo\Entities\Receiver;


class ReceiversController extends Controller
{
  
    private $aclRepo;

    public function __construct(AclRepository $aclRepository)
    {
        $this->aclRepo = $aclRepository;
        // check on permissions
        $this->middleware('user_role:1|0|3')->only('index','clientsReport');
        $this->middleware('user_role:1|0|3|4')->only('show');
        $this->middleware('user_role:1|0|3')->only('create', 'store');
        $this->middleware('user_role:1|0|3')->only('edit');
        $this->middleware('user_role:1|0|3|4')->only('update');
        $this->middleware('user_role:1|0|3')->only('delete', 'multiDestroy');
        $this->middleware('user_role:4')->only('profile');
    }


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(ReceiversDataTable $dataTable)
    {
     
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('cargo::view.receivers')
            ]
        ]);
        $data_with = [];
        $share_data = array_merge(get_class_vars(ReceiversDataTable::class), $data_with);
 
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return $dataTable->render('cargo::'.$adminTheme.'.pages.receicers.index', $share_data);
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        breadcrumb([
            [
                'name' => __('cargo::view.receivers'),
                'path' => fr_route('receivers.index')
            ],
            [
                'name' => __('cargo::view.receivers')
            ]
        ]);

        if(auth()->user()->role == 3){
            $branches = Branch::where('is_archived',0)->where('user_id',auth()->user()->id)->get();
        }else{
            $branches = Branch::where('is_archived',0)->get();
        }

        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.receicers.create')->with(['branches' => $branches ]);
    }
 
    
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request){
        $request->validate([
            'user_id'  => 'required',
            'branch_id'  => 'required',
            'name' => 'required',
            'receiver_mobile' => 'required',
            'country_code' => 'required',
            'reciver_address' => 'required',
        ]);
        $data = $request->only([ 'branch_id' , 'user_id', 'name', 'receiver_mobile', 'country_code', 'reciver_address']);
        $receiver = new Receiver();
        $receiver->fill($data);
        if (!$receiver->save()){
            throw new \Exception();
        }
        
        return redirect()->route('receivers.index')->with(['message_alert' => __('cargo::messages.created')]);
    }

    
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => __('view.profile_details')
            ],
        ]);
        $user = Receiver::findOrFail($id);
        $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.receicers.show')->with(['model' => $user]);
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
                'name' => __('cargo::view.receivers'),
                'path' => fr_route('receivers.index')
            ],
            [
                'name' => __('cargo::view.receivers')
            ]
        ]);

        if(auth()->user()->role == 3){
            $branches = Branch::where('is_archived',0)->where('user_id',auth()->user()->id)->get();
        }else{
            $branches = Branch::where('is_archived',0)->get();
        }

        $Receiver = Receiver::findOrFail($id);
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.receicers.edit')->with(['model' => $Receiver ,  'branches' => $branches ]);
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
     
        $request->validate([
            'name' => 'required',
            'receiver_mobile' => 'required',
            'country_code' => 'required',
            'reciver_address' => 'required',
        ]);


        if (env('DEMO_MODE') == 'On') {
            return redirect()->back()->with(['error_message_alert' => __('view.demo_mode')]);
        }

        $Receiver = Receiver::findOrFail($id);

        $data = $request->only(['name', 'receiver_mobile', 'country_code', 'reciver_address']);
        
        $Receiver->fill($data);
        if (!$Receiver->save()){
            throw new \Exception();
        }

        return redirect()->back()->with(['message_alert' => __('cargo::messages.saved')]);
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
    
        $Receiver = Receiver::findOrFail($id);
        Receiver::destroy($id);
        return response()->json(['message' => __('cargo::messages.deleted')]);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function storeInShipmint(Request $request){
        $request->validate([
            'user_id'  => 'required',
            'branch_id'  => 'required',
            'name' => 'required',
            'receiver_mobile' => 'required',
            'country_code' => 'required',
            'reciver_address' => 'required',
        ]);
        $data = $request->only([ 'branch_id' , 'user_id', 'name', 'receiver_mobile', 'country_code', 'reciver_address']);
        $receiver = new Receiver();
        $receiver->fill($data);
        if (!$receiver->save()){
            throw new \Exception();
        }
        

        return response()->json([
            'success' => true,
            'message' => __('cargo::messages.created'),
            'error' => __('view.somthing_wrong') ,
            'receiver' => [
                'user_id' => $data['user_id'], 
                'name' => $data['name'],
                'receiver_mobile' => $data['receiver_mobile'],
                'country_code' => $data['country_code'],
                'reciver_address' => $data['reciver_address'],
            ],
        ]);
    }

}