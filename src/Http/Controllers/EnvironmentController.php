<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Helper\Installer\EnvironmentManager;
use App\Events\Install\EnivornmentManagerEvents;
use App\Helper\Installer\trait\ApichecktraitHelper;
use App\Models\User;

class EnvironmentController extends Controller
{
    use ApichecktraitHelper;

    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->EnvironmentManager = $environmentManager;
    }

    public function index(){
        return view('installer.environment');
    }

    public function processing()
    {

        return view('installer.seeding');
    }

    public function importsql()
    {
        return view('installer.importsql');
    }

    public function sqlimport(Request $request)
    {
        // Validate the form inputs
        $request->validate([
            'database_name' => 'required',
            'database_username' => 'required',
            'sql_file' => 'required',
        ]);

        $connection = 'mysql';

        $settings = config("database.connections.$connection");

        config([
            'database' => [
                'default' => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);


        // Set the new connection as default
        DB::purge();
        try {
            DB::connection()->getPdo();


            // Get the uploaded SQL file
            $sqlFile = $request->file('sql_file');

            // Read the content of the file
            $sqlContent = file_get_contents($sqlFile->getRealPath());

            // Execute the SQL queries
            DB::unprepared($sqlContent);


            $customerdata = $this->checkPurch(setting('update_setting'));
            if ($customerdata->env) {
                $appkey = stripslashes($customerdata->env);
            } else {
                $appkey = 'base64:' . base64_encode(Str::random(32));
            }
            $envPath = base_path('.env');
            $envFileData =
                'APP_NAME=' . setting('site_title') . "\n" .
                'APP_ENV=' . 'SPRUKO' . "\n" .
                'APP_KEY=' . $appkey . "\n" .
                'APP_DEBUG=' . 'false' . "\n" .
                'APP_LOG_LEVEL=' . 'log' . "\n" .
                'APP_URL=' . $request->app_url . "\n\n" .
                'DB_CONNECTION=' . 'mysql' . "\n" .
                'DB_HOST=' . $request->database_hostname . "\n" .
                'DB_PORT=' . $request->database_port . "\n" .
                'DB_DATABASE=' . $request->database_name . "\n" .
                'DB_USERNAME=' . $request->database_username . "\n" .
                'DB_PASSWORD="' . $request->database_password . '"' . "\n\n" .
                'BROADCAST_DRIVER=' . 'log' . "\n" .
                'CACHE_DRIVER=' . 'file' . "\n" .
                'SESSION_DRIVER=' . 'file' . "\n" .
                'QUEUE_DRIVER=' . 'sync' . "\n\n" .
                'REDIS_HOST=' . $request->redis_hostname . "\n" .
                'REDIS_PASSWORD=' . $request->redis_password . "\n" .
                'REDIS_PORT=' . $request->redis_port . "\n\n" .
                'PUSHER_APP_ID=' . $request->pusher_app_id . "\n" .
                'PUSHER_APP_KEY=' . $request->pusher_app_key . "\n" .
                'PUSHER_APP_SECRET=' . $request->pusher_app_secret;

            file_put_contents($envPath, $envFileData);

            if ($customerdata) {
                $user = User::first();
                request()->session()->put('app_firstname', $user->firstname);
                request()->session()->put('app_lastname', $user->lastname);
                request()->session()->put('app_email', $user->app_email);
                request()->session()->put('envato_purchasecode', setting('update_setting'));
                if ($customerdata->url != url('/')) {
                    return redirect()->route('SprukoAppInstaller::verifyUpdatetokenindex');
                } else {
                    return redirect()->route('SprukoAppInstaller::updatefinal');
                }
            } else {
                redirect()->back()->with('error', 'Try Fresh Installation.');
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function chooseenvironment()
    {
        return view('installer.chooseenvironment');
    }

    public function installapp(Request $request)
    {
        $rules = config('installer.requirements.environment.form.rules');



        $messages = [
            'environment_custom.required_if' => trans('installer_messages.environment.wizard.form.name_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->route('SprukoAppInstaller::environment')->withInput()->withErrors($validator->errors());
        }

        if (! $this->checkDatabaseConnection($request)) {
            return $redirect->route('SprukoAppInstaller::environment')->withInput()->withErrors([
                'database_connection' => trans('installer_messages.environment.wizard.form.db_connection_failed'),
            ]);
        }

        $results = $this->EnvironmentManager->saveFileWizard($request);

        event(new EnivornmentManagerEvents($request));

        return redirect()->route('SprukoAppInstaller::database')
                        ->with(['results' => $results]);

    }


        /**
     * TODO: We can remove this code if PR will be merged: https://github.com/RachidLaasri/LaravelInstaller/pull/162
     * Validate database connection with user credentials (Form Wizard).
     *
     * @param Request $request
     * @return bool
     */
    private function checkDatabaseConnection(Request $request)
    {
        $connection = 'mysql';

        $settings = config("database.connections.$connection");

        config([
            'database' => [
                'default' => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        DB::purge();

        try {
            DB::connection()->getPdo();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
