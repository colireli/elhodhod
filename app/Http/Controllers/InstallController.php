<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use URL;
use DB;
use Hash;
use App\BusinessSetting;
use App\User;
use App\Product;
use SpotlayerCheck;
use App\Http\Helpers\SpotConfigHelper;
use Artisan;
class InstallController extends Controller
{
    public function step0()
    {
        return view('installation.step0');
    }

    public function step1()
    {
        $permission['curl_enabled']           = function_exists('curl_version');
        $permission['db_file_write_perm']     = is_writable(base_path('.env'));
        $permission['routes_file_write_perm'] = is_writable(base_path('app/Providers/RouteServiceProvider.php'));
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('installation.step1', compact('permission'));
    }

    public function step2()
    {
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('installation.step2');
    }

    public function step3($error = "")
    {
        $permission = [
            'curl_enabled' => function_exists('curl_version'),
            'routes_file_write_perm' => is_writable(
                base_path('app/Providers/RouteServiceProvider.php')
            ),
            'db_file_write_perm' => is_writable(
                base_path('.env')
            ),
        ];

        $values = [
            'app_url' => get_base_url(),
            'db_host' => '127.0.0.1',
            'db_name' => 'cargo',
            'db_user' => 'root',
        ];

        return view('installation.step3', compact('error', 'permission', 'values'));
    }

    public function step4()
    {
        return view('installation.step4');
    }

    public function step5()
    {
        return view('installation.step5');
    }

    public function step6()
    {
        update_env_value('INSTALLATION', 'true');
        update_env_value('APP_ENV', 'production');

        return view('installation.step6');
    }

    public function purchase_code(Request $request)
    {
        return view('installation.step3');
    }

    public function database_installation(Request $request)
    {
        if(self::check_database_connection($request->DB_HOST, $request->DB_DATABASE, $request->DB_USERNAME, $request->DB_PASSWORD)) {
            $path = base_path('.env');
            if (file_exists($path)) {
                foreach ($request->types as $type) {
                    update_env_value($type, $request[$type]);
                }
                return redirect()->route('installation.step4');
            }else {
                return redirect()->route('installation.step3');
            }
        }else {
            return redirect()->route('installation.step3', "database_error");
        }
    }

    public function import_sql()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        define('STDIN', fopen("php://stdin", "r"));

        update_env_value('APP_ENV', 'local');
        update_env_value('INSTALLATION', 'false');

        Artisan::call('app:install');

        return redirect()->route('installation.step6');
    }

    public function check_database_connection($db_host = "", $db_name = "", $db_user = "", $db_pass = "")
    {
        $connected = false;

        try {
            $connected = (bool) @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        } catch(\Exception $e) {}

        return $connected;
    }
}
