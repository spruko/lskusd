<?php

use App\Models\BotAutoResponses;
use App\Models\Storage_disk;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

if(!function_exists('adminLoginCheck')){
    function adminLoginCheck()
    {
        try {
            $a = url("/");
            $version = setting("newupdate");
            $env = setting("mail_key_set");
            $pc = setting("update_setting");
            $ch = curl_init();

            $header = array();
            $header[] = 'lcode:' . setting("mail_key_set");
            curl_setopt($ch, CURLOPT_URL, "https://panel.spruko.com/api/api/newlaravelVersionCheck?lcode=$env&url=$a&pc=$pc&version=$version");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $val = json_decode($result);
            $httpCode == 429 ? abort(429) : null;
            $httpCode == 403 ? abort(403) : null;
            $httpCode == 503 ? abort(503) : null;
            $httpCode == 404 ? abort(404) : null;


        } catch (Exception $e) {
            die('network error');
        }

        if($val->has_token){
            $usermailkey = \App\Models\Setting::where("key", "isToken")->first();
            $usermailkey->value = 1;
            $usermailkey->save();
        }else{
            $usermailkey = \App\Models\Setting::where("key", "isToken")->first();
            $usermailkey->value = null;
            $usermailkey->save();
        }

        if (!$val) {
            $usermailkey = \App\Models\Setting::where("key", "update_setting")->first();
            $usermailkey->value = null;
            $usermailkey->save();
            return throw new \Exception("error response");
        }
        if ($val->code == "process") {
               $usermailkey = \App\Models\Setting::where("key", "update_setting")->first();
               $usermailkey->value = null;
               $usermailkey->save();


            return response()->json(['message' => 'Function completed']);


        }
        if ($val->lcode != "null") {
            $usermailkey = \App\Models\Setting::where("key", "mail_key_set")->first();
            $usermailkey->value = $val->lcode;
            $usermailkey->save();
        };

        if($val->code != 'working'){
            $sessionPath = storage_path('framework/sessions');
            $sessionFiles = glob($sessionPath . '/*');
            foreach ($sessionFiles as $sessionFile) {
                unlink($sessionFile);
            }
            Session::flush();
            return throw new \Exception('error response');
        }
    }
}
//license check
if(strpos(url()->current(), '/admin') !== false){
    session_start();
    if(strpos(url()->current(), '/login') !== false){
         if(!isset($_SESSION['userAction']) || !$_SESSION['userAction'] ){
                $_SESSION['userAction'] = true;
         }
    }else{
        if(isset($_SESSION['userAction']))
            if($_SESSION['userAction']){
                // adminLoginCheck();
                $_SESSION['userAction'] = false;
            }
    }
}

if(!function_exists('storage')){
    function storage()
    {
        $storage = Storage_disk::where('status', 1)->first();
        if ($storage) {

            return Storage_disk::where('status', 1)->first();
        }
        return Storage_disk::where('storage_disk', 'public')->first();
    }
}

if(!function_exists('existprovider')){
    function existprovider($storage_disk)
    {
        return Storage_disk::where('storage_disk', $storage_disk)->first();
    }
}

if(!function_exists('updateEnv')){
    function updateEnv($envKey, $envValue)
    {
        $envFilePath = base_path('.env');
        $envContent = file_get_contents($envFilePath);
        $newEnvLine = "{$envKey}={$envValue}";
        if (strpos($envContent, "{$envKey}=") !== false) {
            $envContent = preg_replace(
                "/^{$envKey}=.*/m",
                $newEnvLine,
                $envContent
            );
        } else {
            $envContent .= "\n{$newEnvLine}";
        }

        // Write the updated environment variables back to the .env file
        file_put_contents($envFilePath, $envContent);
    }
}

if(!function_exists('addonstatus')){
    function addonstatus($handler)
    {

        if ($handler) {

        $addonstatus = $handler::getStatus($handler);
        return $addonstatus;
        }
        return false;
    }
}


