<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Acl\Repositories\AclRepository;
use Storage;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SupportController extends Controller
{

    public function __construct()
    {
        // check on permissions
        $this->middleware('user_role:1');
    }

    public function getSystemSupport()
    {
        breadcrumb([
            [
                'name' => __('view.support')
            ]
        ]); 
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view($adminTheme.'.pages.support');
    }

}
