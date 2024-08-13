<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Acl\Repositories\AclRepository;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Storage;
use ZipArchive;
use DB;
use App\Models\Addon;
use App\Models\AppRequest;
use Image;
use Mail;
use App\Mail\EmailManager;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Log;
use App\Models\User;

use App\Http\DataTables\AppRequestsDataTable;

class AddonsController extends Controller
{

    private $aclRepo;

    public function __construct(AclRepository $aclRepository)
    {
        $this->aclRepo = $aclRepository;
        // check on permissions
        $this->middleware('user_role:1');
    }

    public function addons()
    {
        breadcrumb([
            [
                'name' => __('view.addons')
            ]
        ]);
        $all_addons = Addon::all();
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view($adminTheme.'.pages.addons' , compact('all_addons') );
    }

    public function app_requests(AppRequestsDataTable $dataTable)
    {
        breadcrumb([
            [
                'name' => __('view.app_requests')
            ]
        ]);
        $requests = AppRequest::all();
        $data_with = [];
        $share_data = array_merge(get_class_vars(AppRequestsDataTable::class), $data_with);
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return $dataTable->render($adminTheme.'.pages.app-requests' , $share_data);
    }

    public function getAddonUpload()
    {
        breadcrumb([
            [
                'name' => __('view.upload_addon')
            ]
            // 'path' => RouteServiceProvider::HOME,
        ]); // See App/Helpers/functions/helpers.php -> breadcrumb function
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view($adminTheme.'.pages.addon-upload');
    }


    public function postAddonUpload(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            return redirect()->back()->with(['error_message_alert' => __('view.demo_mode')]);
        }
        $request->validate([
            'zip_file' => 'required|mimes:zip',
            // 'image' => 'required|mimes:jpg,png,jpeg,gif,svg',
        ]);

        set_time_limit(0);

        $originFileName = basename($request->file('zip_file')->getClientOriginalName(), '.'.$request->file('zip_file')->getClientOriginalExtension());
        $originFileName = $originFileName . 'z';

            $dir = 'Modules';
            if (!is_dir($dir))
                mkdir($dir, 0777, true);

            $zipped_file_name = $request->zip_file->getClientOriginalName();

            $zipped_withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $zipped_file_name);

            if(file_exists(base_path('Modules/'.$zipped_withoutExt))) {
                return redirect()->back()->with(['message_alert' => __('view.this_addon_is_already_exist')]);
            }

            $path = Storage::disk('addon')->put('Modules', $request->zip_file);


            //Unzip uploaded update file and remove zip file.
            $zip = new ZipArchive;
            $res = $zip->open(base_path($path));

            if ($res === true) {
                $res = $zip->extractTo(base_path('Modules'));
                $zip->close();

                $addon = new Addon();
                $addon->name = $zipped_withoutExt;
                $addon->slug = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $zipped_withoutExt)));
                $duplicated_slug = Addon::where('slug',$addon->slug)->count();

                if($duplicated_slug > 0){
                    $addon->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $zipped_withoutExt)) . '-' . uniqid();
                }

                $file_name = $zipped_withoutExt.'-addon-image.png';

                if(!file_exists(base_path('public/storage/addons/') .$file_name) && file_exists(base_path('Modules/'.$zipped_withoutExt.'/addon-image.png'))) {
                    File::copy(base_path('Modules/'.$zipped_withoutExt.'/addon-image.png'), base_path('public/storage/addons/') .$file_name);
                }

                $addon->image = $file_name;

                $addon->save();

            } else {
                dd('could not open');
            }


            if(File::exists(base_path($path))){
                File::delete(base_path($path));
            }else{
                dd('File does not exists.');
            }

            // Create the symbolic link
            Artisan::call('storage:link');

            return redirect()->route('addons')->with(['message_alert' => __('view.addon_uploaded_successfully')])->with('loader',true);
    }

    public function changingStatus(Request $request)
    {
        $addon = Addon::where('id', $request->id)->first();
        $addonStatus = $addon->status ;
        if($request->status == 1) {
            $addon->update(['status'=> 1 ]);
            return 1 ;
        } else {
            $addon->update(['status'=> 0 ]);
            return 1 ;
        }
        return 0 ;
    }


    public function deleteAddons($id){
        // Delete Folder
        $addon = Addon::where('id',$id)->first();

         // Delete Card
        if(isset($addon)){

            $ch = curl_init();
            $headers = array("Content-Type => application/json", "Accept: */*");

            $params = ['addon' => $addon->slug, 'user_email' => Auth()->user()->email];
            $url = rtrim('https://addons.bdaia.com/api/delete-app-request',"?") . "?" . http_build_query($params);

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

            $server_output = curl_exec($ch);

            // print_r($server_output);
            // exit();
            curl_close($ch);

            $file_name = $addon->name;

            $path = base_path('Modules').'/'.$file_name;

            if(file_exists($path)){
                File::deleteDirectory($path);
            }

            $addon->delete();
        }else{
            return redirect()->back()->with(['error_message_alert' => __('view.somthing_wrong')]);
        }

        return redirect()->back()->with(['message_alert' => __('view.addon_deleted_successfully')]);
    }

}
