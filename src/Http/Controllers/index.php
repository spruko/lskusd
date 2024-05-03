<?php

namespace laravelLara\lskusd\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\usersettings;
use App\Models\Setting;
use Hash;
use Session;
use GeoIP;

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\Seosetting;
use App\Models\Pages;
use Auth;
use Ifsnop\Mysqldump\Mysqldump;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;
use laravelLara\lskusd\utils\FinalManager;
use laravelLara\lskusd\utils\InstallFileCreate;
use laravelLara\lskusd\utils\trait\ApichecktraitHelper;

class index extends Controller
{
    use ApichecktraitHelper;
    public function logindetails()
    {
        $olduser = User::where('id', '1')->exists();
        if (!$olduser) {
            return view('installer.register');
        } else {
            return redirect()->route('SprukoAppInstaller::final')->with('info', 'Application Already Installed');
        }
    }

    public function logindetailsstore(Request $request)
    {
        $this->validate($request, [
            'app_firstname' => 'required',
            'app_lastname' => 'required',
            'app_email' => 'required',
            'app_password' => 'required',
            'envato_purchasecode' => 'required'
        ]);

        // Create User
        $customerdata = $this->verifysettingcreate($request->envato_purchasecode, $request->app_firstname, $request->app_lastname, $request->app_email);


        if ($customerdata) {
            if ($customerdata->App == 'invalid') {

                $this->validate($request, [
                    'envato_purchasecode' => 'required'
                ]);
                if ($customerdata->message != '') {
                    $messages = ['purchase_code_valid.required' => 'The :attribute field is required. ERROR: <strong>' . $customerdata->message . '</strong>'];
                }
                return redirect()->back()->with('error', $customerdata->message);
            }


            if ($customerdata->App == 'old') {
                // return redirect()->back()->with('error', $customerdata->message);
                $data['envato_purchasecode'] = $request->envato_purchasecode;
                request()->session()->put('app_firstname', request()->app_firstname);
                request()->session()->put('app_lastname', request()->app_lastname);
                request()->session()->put('app_email', request()->app_email);
                request()->session()->put('envato_purchasecode', request()->envato_purchasecode);
                request()->session()->put('app_password', request()->app_password);

                return view('installer.token')->with($data);
            }
            if ($customerdata->App == 'New') {

                // $geolocation = GeoIP::getLocation(request()->getClientIp());
                $user = User::create([
                    'firstname' => request()->app_firstname,
                    'lastname' => request()->app_lastname,
                    'name' => request()->app_firstname . ' ' . request()->app_lastname,
                    'email' => request()->app_email,
                    'verified' => '1',
                    'status' => '1',
                    'image' => null,
                    'password' => Hash::make(request()->app_password),
                    'timezone' => null,
                    'country' => null,
                    // 'timezone' => $geolocation->timezone,
                    // 'country' => $geolocation->country,
                    'dashboard' => 'Admin',
                    'remember_token' => '',
                ]);

                $usersetting = new usersettings();
                $usersetting->users_id = $user->id;
                $usersetting->emailnotifyon = '1';
                $usersetting->save();

                $uset = new Setting();
                $uset->key = 'newupdate';
                $uset->value = 'V4.0';
                $uset->save();

                $user->assignRole('superadmin');
                if ($request->envato_purchasecode) {
                    $data['update_setting'] = $request->envato_purchasecode;
                    $this->updateSettings($data);
                }

                // $usermailkey = new Setting();
                // $usermailkey->key = 'mail_key_set';
                // $usermailkey->value = '12345678';
                // $usermailkey->save();

                Setting::updateOrCreate(
                    ['key' => 'mail_key_set'],
                    ['value' => $customerdata->mail_key]
                );
                Setting::updateOrCreate(
                    ['key' => 'isToken'],
                    ['value' => $customerdata->itemdetails->tokenGenerate ? true : false]
                );

                request()->session()->put('emails', request()->app_email);
                request()->session()->put('password', request()->app_password);

                return redirect()->route('SprukoAppInstaller::final')->with('success', 'Application Installed Succesfully');
            }
            if ($customerdata->App == 'update') {

                return redirect()->back()->with('success', $customerdata->message);
            }
        } else {
            return redirect()->back()->with('error', "Invalid purchase code");
        }
    }

    public function index(InstallFileCreate $fileManager, FinalManager $finalInstall)
    {

        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();

        return view('installer.final');
    }
    /**
     *  Settings Save/Update.
     *
     * @return \Illuminate\Http\Response
     */
    private function updateSettings($data)
    {

        foreach ($data as $key => $val) {
            $setting = Setting::where('key', $key);
            if ($setting->exists())
                $setting->first()->update(['value' => $val]);
        }
    }




    public function userInHold(Request $request)
    {
        $item_id = config('installer.requirements.itemId');
        $purchaseCode = setting('update_setting');
        $licenseCode = setting('mail_key_set');
        $userurl = url('/');

        $url = "https://api.envato.com/v3/market/author/sale?code=" . setting('update_setting');
        $curl = curl_init($url);

        $personal_api_token = $request->token;

        /*Correct header for the curl extension*/
        $header = array();
        $header[] = 'Authorization: Bearer ' . $personal_api_token;
        $header[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:41.0) Gecko/20100101 Firefox/41.0';
        $header[] = 'timeout: 20';
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        /*Connect to the API, and get values from there*/
        $envatoCheck = curl_exec($curl);
        curl_close($curl);

        $envatoCheck = json_decode($envatoCheck);

        /*Variable request from the API*/
        $date = new \DateTime(isset($envatoCheck->supported_until) ? $envatoCheck->supported_until : false);
        $support_date = $date->format('Y-m-d H:i:s');
        $sold = new \DateTime(isset($envatoCheck->sold_at) ? $envatoCheck->sold_at : false);
        $sold_at = $sold->format('Y-m-d H:i:s');
        $buyer = (isset($envatoCheck->buyer) ? $envatoCheck->buyer : false);
        $license = (isset($envatoCheck->license) ? $envatoCheck->license : false);
        $count = (isset($envatoCheck->purchase_count) ? $envatoCheck->purchase_count : false);
        $support_amount = (isset($envatoCheck->support_amount) ? $envatoCheck->support_amount : false);
        // $item  = (isset( $envatoCheck->item->previews->icon_with_video_preview->landscape_url ) ? $envatoCheck->item->previews->icon_with_video_preview->landscape_url  : false);
        $item  = (isset($envatoCheck->item->previews->icon_preview->icon_url) ? $envatoCheck->item->previews->icon_preview->icon_url  : false);
        $amount = (isset($envatoCheck->amount) ? $envatoCheck->amount : false);

        $output = "";
        /*If Purchase code exists, But Purchase ended*/
        if (isset($envatoCheck->item->name) && (date('Y-m-d H:i:s') >= $support_date)) {

            $output .=  "
                <table class='table table-striped table-bordered'>
                        <tbody>
                            <tr>
                                <th colspan='2' class='fs-16 fw-semibold'>Client Details</th>
                            </tr>
                            <tr>
                                <td class='w-30'><b>ITEM_ICON :</b></td>
                                <td> <img src='{$item}' class='img-responsive br-7' /></td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>Item Name:</b></td>
                                <td>{$envatoCheck->item->name}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>Purchase Code:</b></td>
                                <td>{$purchaseCode}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>License Code:</b></td>
                                <td>{$licenseCode}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>Url:</b></td>
                                <td>{$userurl}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>Item Id:</b></td>
                                <td>{$item_id}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>CLIENT:</b></td>
                                <td>{$buyer}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>SOLD AT:</b></td>
                                <td> {$sold_at}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>SUPPORT UNTIL:</b></td>
                                <td> {$support_date} <span class='badge bg-danger ms-1'>Support Expired</span></td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>LICENSE:</b></td>
                                <td> {$license}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>COUNT:</b></td>
                                <td> {$count}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>AMOUNT:</b></td>
                                <td> {$amount}</td>
                            </tr>

                        </tbody>
                </table>

            ";
        }

        /*If Purchase code exists, display client information*/
        if (isset($envatoCheck->item->name)  && (date('Y-m-d H:i:s') < $support_date)) {

            $output .=  "
                <table class='table table-striped table-bordered'>
                        <tbody>
                            <tr>
                                <th colspan='2' class='fs-16 fw-semibold'>Client Details</th>
                            </tr>
                            <tr>
                                <td class='w-30'><b>ITEM_ICON :</b></td>
                                <td> <img src='{$item}' class='img-responsive br-7' /></td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>Item Name:</b></td>
                                <td>{$envatoCheck->item->name}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>Purchase Code:</b></td>
                                <td>{$purchaseCode}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>License Code:</b></td>
                                <td>{$licenseCode}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>Url:</b></td>
                                <td>{$userurl}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>Item Id:</b></td>
                                <td>{$item_id}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>CLIENT:</b></td>
                                <td>{$buyer}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>SOLD AT:</b></td>
                                <td> {$sold_at}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>SUPPORT UNTIL:</b></td>
                                <td> {$support_date}  <span class='badge bg-success ms-1'>Supported</span></td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>LICENSE:</b></td>
                                <td> {$license}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>COUNT:</b></td>
                                <td> {$count}</td>
                            </tr>
                            <tr>
                                <td class='w-30'><b>AMOUNT:</b></td>
                                <td> {$amount}</td>
                            </tr>
                        </tbody>
                </table>

            ";
        }

        /*If Purchase Code doesn't exist, no information will be displayed*/
        if (!isset($envatoCheck->item->name)) {

            $output .=  "

                <h4>Invalid Purchase Code</h4>
            ";
        }

        return  response()->json(['output' => $output]);
    }

    public function validatelog()
    {
        $sessionPath = storage_path('framework/sessions');
        $sessionFiles = glob($sessionPath . '/*');
        foreach ($sessionFiles as $sessionFile) {
            unlink($sessionFile);
        }
        Session::flush();
        return  response()->json(['output' => 'All the users Logged Out successfully']);
    }

    public function verifytoken(Request $request)
    {
        $this->validate($request, [
            'app_token' => 'required',
            'envato_purchasecode' => 'required',
        ]);

        // Create User
        $customerdata = $this->verifysettingtoken($request->envato_purchasecode, $request->app_token);

        if ($customerdata) {
            if ($customerdata->App == 'invalid') {
                $this->validate($request, [
                    'app_token' => 'required',
                    'envato_purchasecode' => 'required'
                ]);
                if ($customerdata->message != '') {
                    $messages = ['purchase_code_valid.required' => 'The :attribute field is required. ERROR: <strong>' . $customerdata->message . '</strong>'];
                }
                return redirect()->back()->with('error', $customerdata->message);
            }
            if ($customerdata->App == 'Approved') {
                $app_firstname = request()->session()->get('app_firstname');
                $app_lastname = request()->session()->get('app_lastname');
                $app_email = request()->session()->get('app_email');
                $app_password = request()->session()->get('app_password');
                $user = User::create([
                    'firstname' => $app_firstname,
                    'lastname' =>  $app_lastname,
                    'name' => $app_firstname . ' ' . $app_lastname,
                    'email' => $app_email,
                    'verified' => '1',
                    'status' => '1',
                    'image' => null,
                    'password' => Hash::make($app_password),
                    'timezone' => null,
                    'country' => null,
                    'dashboard' => 'Admin',
                    'remember_token' => '',
                ]);

                $usersetting = new usersettings();
                $usersetting->users_id = $user->id;
                $usersetting->emailnotifyon = '1';
                $usersetting->save();

                $uset = new Setting();
                $uset->key = 'newupdate';
                $uset->value =  'V4.0';
                $uset->save();

                $user->assignRole('superadmin');
                if ($request->envato_purchasecode) {
                    $data['update_setting'] = $request->envato_purchasecode;
                    $this->updateSettings($data);
                }
                Setting::updateOrCreate(
                    ['key' => 'mail_key_set'],
                    ['value' => $customerdata->mail_key]
                );
                Setting::updateOrCreate(
                    ['key' => 'isToken'],
                    ['value' => true]
                );

                request()->session()->put('emails', $app_email);
                request()->session()->put('password', $app_password);


                // Create a new process for queue:work
                $process = new Process(['php', 'artisan', 'queue:work']);

                // Run the process in the background
                $process->start();

                return redirect()->route('SprukoAppInstaller::final')->with('success', 'Application Installed Succesfully');
            }
        } else {
            return redirect()->back()->with('error', "Invalid Token key");
        }
    }

    public function userShifted(Request $request)
    {

        $sessionPath = storage_path('framework/sessions');
        $storage = storage_path('installed');
        if (file_exists($sessionPath)) {
            $sessionFiles = glob($sessionPath . '/*');
            foreach ($sessionFiles as $sessionFile) {
                unlink($sessionFile);
            }
            Session::flush();
        }
        if (file_exists($storage)) {
            unlink($storage);
        }

        $envFilePath = base_path('.env');

        // Read the existing .env file
        $envContent = File::get($envFilePath);

        // Update or remove environment variables
        $newEnvContent = preg_replace("/^DB_USERNAME=.*/m", "DB_USERNAME=username", $envContent);
        $newEnvContent = preg_replace("/^DB_DATABASE=.*/m", "DB_DATABASE=database", $newEnvContent);
        $newEnvContent = preg_replace("/^DB_PASSWORD=.*/m", "DB_PASSWORD=password", $newEnvContent);
        $newEnvContent = preg_replace("/^APP_NAME=.*/m", "", $newEnvContent);

        // Write the updated content back to the .env file
        File::put($envFilePath, $newEnvContent);

        return  response()->json(['output' => '$deleted']);
    }

    public function tokenGenerateview()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('installer.tokengenerateview')->with($data);
    }

    public function tokenGenerate()
    {
        if (Auth::check() && Auth::user()->id == 1) {
            // $pc = '20ae476d-c32e-4c71-b13d-627fa88084f7';
            $pc = setting("update_setting");
            $env = env('APP_KEY');

            $data = array(
                'pc' => $pc,
                'env' => $env
            );
            $payload = json_encode($data);
            // update cURL resource
            $ch = curl_init('https://panel.spruko.com/api/api/laravelTokenGenerate');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // Set HTTP Header for POST request
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                )
            );

            // Submit the POST request
            $result = curl_exec($ch);


            // Close cURL session handle
            curl_close($ch);

            $val = json_decode($result);
            if (isset($val->token) && $val->token) {
                $user = User::find(1);
                $email = $user->email;
                Mail::send('admin.email.template', ['emailBody' => "<p>This is a unique system-generated token for the installation of the application on a new domain. It's crucial to securely store this token, as it will be required during the installation process. Once entered, the application will take care of the rest, ensuring a smooth transition to the new domain.</p>

                <p>Please be aware that upon successful installation on the new domain, the previous installation on the old domain will no longer be operational. This ensures the integrity and security of our application across domains.</p>

                <b>Token:</b>" . $val->token], function ($message) use ($email) {
                    $message->to($email)->subject('Unique System Generated Token');
                });
                Setting::updateOrCreate(
                    ['key' => 'isToken'],
                    ['value' => true]
                );
            }
            return response()->json($val->token);
        }
    }

    public function requesttoken()
    {
        if (Auth::check() && Auth::user()->id == 1) {
            // $pc = '20ae476d-c32e-4c71-b13d-627fa88084f7';
            $pc = setting("update_setting");
            $ch = curl_init();
            $header = array();
            $header[] = 'lcode:' . setting("mail_key_set");
            // $header[] = 'lcode:' . 'SPK-50I2wleV-65f031ea8fd1a';
            curl_setopt($ch, CURLOPT_URL, "https://panel.spruko.com/api/api/larvelrequesttoken?pc=$pc");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $val = json_decode($result);

            return response()->json($val);
        }
    }


    public function exportDatabase()
    {
        $filePath = storage_path('app/database_backup.sql');

        $host = env('DB_HOST');
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        try {
            $dump = new Mysqldump("mysql:host=$host;dbname=$database", $username, $password);

            $dump->start($filePath);

            // response()->download($filePath)->deleteFileAfterSend(true);
            // return redirect()->back()->with('success',lang('Download Completed successfully.'));
            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error',$e->getMessage());
            // return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadProject()
    {
        // Create a temporary file for the zip
        $zipFile = env('APP_NAME') . '.zip';
        $zip = new ZipArchive();
        // Open the zip file for writing
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Get the base path of your Laravel project
            $basePath = base_path();
            // Get all files and directories in your Laravel project
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($basePath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            // Loop through each file and directory
            foreach ($files as $name => $file) {
                // Skip directories (they will be added automatically when adding files)
                if (!$file->isDir()) {
                    // Add file to the zip archive
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($basePath) + 1);
                    if (strpos($relativePath, 'storage') !== false && basename($relativePath) === 'installed') {
                        continue; // Skip this file
                    }
                    $zip->addFile($filePath, $relativePath);
                } else {
                    // Add empty directory to the zip archive
                    $relativePath = substr($file->getRealPath(), strlen($basePath) + 1);
                    $zip->addEmptyDir($relativePath);
                }
            }
            // Close the zip archive
            $zip->close();

            // Download the zip file
            return response()->download($zipFile)->deleteFileAfterSend(true);
            // response()->download($zipFile)->deleteFileAfterSend(true);

            // return redirect()->back()->with('success',lang('Download Completed successfully.'));
        } else {
            return redirect()->back()->with('error','Failed to create zip file');
        }
    }

    public function verifyUpdatetokenindex()
    {
        $data['envato_purchasecode'] = setting('update_setting');
        return view('installer.updatetoken')->with($data);
    }

    public function verifyUpdatetoken(Request $request)
    {
        $this->validate($request, [
            'app_token' => 'required',
            'envato_purchasecode' => 'required',
        ]);

        // Create User
        $customerdata = $this->verifysettingtoken($request->envato_purchasecode, $request->app_token);
        if ($customerdata->App == 'Approved') {
            $app_firstname = request()->session()->get('app_firstname');
            $app_lastname = request()->session()->get('app_lastname');
            $app_email = request()->session()->get('app_email');

            $uset = new Setting();
            $uset->key = 'newupdate';
            $uset->value =  config('installer.requirements.version');
            $uset->save();

            if ($request->envato_purchasecode) {
                $data['update_setting'] = $request->envato_purchasecode;
                $this->updateSettings($data);
            }
            Setting::updateOrCreate(
                ['key' => 'mail_key_set'],
                ['value' => $customerdata->mail_key]
            );
            Setting::updateOrCreate(
                ['key' => 'isToken'],
                ['value' => true]
            );

            $envFilePath = base_path('.env');

            // Read the existing .env file
            $envContent = File::get($envFilePath);
            $newEnvContent = preg_replace("/^APP_KEY=.*/m", "APP_KEY=" . stripslashes($customerdata->key), $envContent);
            // Write the updated content back to the .env file
            File::put($envFilePath, $newEnvContent);

            request()->session()->put('emails', $app_email);


            // Create a new process for queue:work
            $process = new Process(['php', 'artisan', 'queue:work']);

            // Run the process in the background
            $process->start();

            return redirect()->route('SprukoAppInstaller::updatefinal')->with('success', 'Application Installed Succesfully');
        } else {
            return redirect()->back()->with('error', "Invalid Token key");
        }
    }

    public function updatefinal(InstallFileCreate $fileManager, FinalManager $finalInstall)
    {

        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();

        return view('installer.updatefinal');
    }
}
