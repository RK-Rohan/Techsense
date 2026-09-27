<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Module;
use ZipArchive;
use Illuminate\Support\Facades\Artisan;

class ModulesController extends Controller
{
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ModuleUtil  $moduleUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        //Get list of all modules.
        $modules = Module::toCollection()->toArray();

        foreach ($modules as $module => $details) {
            $modules[$module]['is_installed'] = $this->moduleUtil->isModuleInstalled($details['name']) ? true : false;

            //Get version information.
            if ($modules[$module]['is_installed']) {
                $modules[$module]['version'] = $this->moduleUtil->getModuleVersionInfo($details['name']);
            }

            // Always point at a registered application route. A newly uploaded or
            // disabled module may not have registered its own routes yet, which
            // previously caused action() to fail and left the button pointing to #.
            $modules[$module]['install_link'] = action(
                [self::class, 'install'],
                ['module_name' => $details['name']]
            );

            //Update Link.
            try {
                $modules[$module]['update_link'] = action('\Modules\\'.$details['name'].'\Http\Controllers\InstallController@update');
            } catch (\Exception $e) {
                $modules[$module]['update_link'] = '#';
            }

            //Uninstall Link.
            try {
                $modules[$module]['uninstall_link'] = action('\Modules\\'.$details['name'].'\Http\Controllers\InstallController@uninstall');
            } catch (\Exception $e) {
                $modules[$module]['uninstall_link'] = '#';
            }
        }

        $is_demo = (config('app.env') == 'demo');
        $mods = $this->__available_modules();
        
        return view('install.modules.index')
            ->with(compact('modules', 'is_demo', 'mods'));

        //Option to uninstall

        //Option to activate/deactivate

        //Upload module.
    }

    public function regenerate()
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            Artisan::call('module:publish');
            Artisan::call('passport:install --force');
            // Artisan::call('scribe:generate');

            $output = ['success' => 1,
                'msg' => __('lang_v1.success'),
            ];
        } catch (Exception $e) {
            $output = ['success' => 1,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    /**
     * Run a module installer even when the module has not registered its routes yet.
     */
    public function install($module_name)
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $module = Module::findOrFail($module_name);
            $controller = '\\Modules\\'.$module->getName().'\\Http\\Controllers\\InstallController';

            if (! class_exists($controller) || ! method_exists($controller, 'index')) {
                throw new \RuntimeException("The {$module->getName()} module does not provide an installer.");
            }

            // Enable the module so its config, migrations and other services are
            // available to the installer.
            if (! $module->isEnabled()) {
                $module->enable();

                return redirect()->route('manage-modules.install', [
                    'module_name' => $module->getName(),
                ]);
            }

            // Render the shared installer from an application-owned route. Some
            // legacy modules do not register their RouteServiceProvider on newer
            // Laravel versions, so their index action cannot generate its POST URL.
            return view('install.install-module', [
                'action_url' => route('manage-modules.run-installer', [
                    'module_name' => $module->getName(),
                ]),
                'intruction_type' => 'uf',
                'action_type' => 'install',
                'module_display_name' => $module->getName(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('status', [
                'success' => false,
                'msg' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Submit to a module installer without relying on module-owned routes.
     */
    public function runInstaller($module_name)
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $module = Module::findOrFail($module_name);
            $controller = '\\Modules\\'.$module->getName().'\\Http\\Controllers\\InstallController';

            if (! class_exists($controller) || ! method_exists($controller, 'install')) {
                throw new \RuntimeException("The {$module->getName()} module does not provide an installer.");
            }

            return app()->call([app($controller), 'install']);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->with('status', [
                'success' => false,
                'msg' => $e->getMessage(),
            ]);
        }
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
     * Activate/Deaactivate the specified module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $module_name)
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $module = Module::find($module_name);

            //php artisan module:disable Blog
            if ($request->action_type == 'activate') {
                $module->enable();
            } elseif ($request->action_type == 'deactivate') {
                $module->disable();
            }

            $output = ['success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Deletes the module.
     *
     * @param  string  $module_name
     * @return \Illuminate\Http\Response
     */
    public function destroy($module_name)
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $module = Module::find($module_name);
            // $module->delete();

            $path = $module->getPath();

            die("To delete the module delete this folder <br/>" . $path . '<br/> Go back after deleting');

            $output = ['success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Upload the module.
     */
    public function uploadModule(Request $request)
    {
        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        try {

            //get zipped file
            $module = $request->file('module');
            $module_name = $module->getClientOriginalName();
            $module_name = str_replace('.zip', '', $module_name);

            //check if uploaded file is valid or not and and if not redirect back
            if ($module->getMimeType() != 'application/zip') {
                $output = ['success' => false,
                    'msg' => __('lang_v1.pls_upload_valid_zip_file'),
                ];

                return redirect()->back()->with(['status' => $output]);
            }

            //check if 'Modules' folder exist or not, if not exist create
            $path = '../Modules';
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }

            //extract the zipped file in given path
            $zip = new ZipArchive();
            if ($zip->open($module) === true) {
                $zip->extractTo($path.'/');
                $zip->close();

                //Needs improvement

                // if(!(file_exists($path . '/' . $module_name . '/composer.json')
                //     && file_exists($path . '/' . $module_name . '/module.json')
                //     && file_exists($path . '/' . $module_name . '/Config/config.php'))){
                //         \File::deleteDirectory($path . '/' . $module_name);
                // }
            }

            $output = ['success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (Exception $e) {
            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    private function __available_modules()
    {
        return 'a:14:{i:0;a:4:{s:1:"n";s:10:"Essentials";s:2:"dn";s:17:"Essentials Module";s:1:"u";s:53:"https://ultimatefosters.com/recommends/essential-app/";s:1:"d";s:49:"Essentials features for every growing businesses.";}i:1;a:4:{s:1:"n";s:10:"Superadmin";s:2:"dn";s:17:"Superadmin Module";s:1:"u";s:54:"https://ultimatefosters.com/recommends/superadmin-app/";s:1:"d";s:76:"Turn your POS to SaaS application and start earning by selling subscriptions";}i:2;a:4:{s:1:"n";s:11:"Woocommerce";s:2:"dn";s:18:"Woocommerce Module";s:1:"u";s:55:"https://ultimatefosters.com/recommends/woocommerce-app/";s:1:"d";s:36:"Sync your Woocommerce store with POS";}i:3;a:4:{s:1:"n";s:13:"Manufacturing";s:2:"dn";s:20:"Manufacturing Module";s:1:"u";s:57:"https://ultimatefosters.com/recommends/manufacturing-app/";s:1:"d";s:70:"Manufacture products from raw materials, organise recipe & ingredients";}i:4;a:4:{s:1:"n";s:7:"Project";s:2:"dn";s:14:"Project Module";s:1:"u";s:51:"https://ultimatefosters.com/recommends/project-app/";s:1:"d";s:66:"Manage Projects, tasks, tasks time logs, activities and much more.";}i:5;a:4:{s:1:"n";s:6:"Repair";s:2:"dn";s:13:"Repair Module";s:1:"u";s:50:"https://ultimatefosters.com/recommends/repair-app/";s:1:"d";s:248:"Repair module helps with complete repair service management of electronic goods like Cellphone, Computers, Desktops, Tablets, Television, Watch, Wireless devices, Printers, Electronic instruments and many more similar devices which you can imagine!";}i:6;a:4:{s:1:"n";s:3:"Crm";s:2:"dn";s:10:"CRM Module";s:1:"u";s:63:"https://ultimatefosters.com/product/crm-module-for-ultimatepos/";s:1:"d";s:39:"Customer relationship management module";}i:7;a:4:{s:1:"n";s:16:"ProductCatalogue";s:2:"dn";s:16:"ProductCatalogue";s:1:"u";s:90:"https://codecanyon.net/item/digital-product-catalogue-menu-module-for-ultimatepos/28825346";s:1:"d";s:32:"Digital Product catalogue Module";}i:8;a:4:{s:1:"n";s:10:"Accounting";s:2:"dn";s:17:"Accounting Module";s:1:"u";s:82:"https://ultimatefosters.com/product/accounting-bookkeeping-module-for-ultimatepos/";s:1:"d";s:48:"Accounting & Book keeping module for UltimatePOS";}i:9;a:4:{s:1:"n";s:12:"AiAssistance";s:2:"dn";s:19:"AiAssistance Module";s:1:"u";s:73:"https://ultimatefosters.com/product/ai-assistance-module-for-ultimatepos/";s:1:"d";s:104:"AI Assistant module for UltimatePOS. This module used openAI API to help with in copywriting & reporting";}i:10;a:4:{s:1:"n";s:15:"AssetManagement";s:2:"dn";s:22:"AssetManagement Module";s:1:"u";s:76:"https://ultimatefosters.com/product/asset-management-module-for-ultimatepos/";s:1:"d";s:40:"Useful for managing all kinds of assets.";}i:11;a:4:{s:1:"n";s:3:"Cms";s:2:"dn";s:10:"Cms Module";s:1:"u";s:59:"https://ultimatefosters.com/product/ultimatepos-cms-module/";s:1:"d";s:153:"Mini CMS (content management system) Module for UltimatePOS to help manage all frontend contents like Landing page, Blogs, Contact us & many other pages.";}i:12;a:4:{s:1:"n";s:9:"Connector";s:2:"dn";s:20:"Connector/API Module";s:1:"u";s:68:"https://ultimatefosters.com/product/rest-api-module-for-ultimatepos/";s:1:"d";s:24:"Provide the API for POS.";}i:13;a:4:{s:1:"n";s:11:"Spreadsheet";s:2:"dn";s:18:"Spreadsheet Module";s:1:"u";s:71:"https://ultimatefosters.com/product/spreadsheet-module-for-ultimatepos/";s:1:"d";s:72:"Allows you to create spreadsheet and share with employees, roles & todos";}}';
    }
}
