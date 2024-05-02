<?php
namespace App\Helper\Installer\trait;

use App\Helper\Curl;
use Exception;

trait ApichecktraitHelper
{
    /**
	 * IMPORTANT: Do not change this part of the code to prevent any data losing issue.
	 *
	 * @param $purchaseCode
	 * @return false|mixed|string
	 */

     private function verifyupdatechecker($verifyupdate)
     {
         $apiUrl = config('installer.requirements.purchasecodCheckerUrl') . $verifyupdate . '&item_id=' . config('installer.requirements.itemId');
         $data = Curl::fetch($apiUrl);

         // Format object data
         $data = json_decode($data);

         // Check if 'data' has the valid json attributes
         if (!isset($data->valid) || !isset($data->message)) {
             $data = json_encode(['valid' => false, 'message' => 'Invalid purchase code. Incorrect data format.']);
             $data = json_decode($data);
         }

         return $data;
     }

     private function checkPurch($token){
         $checkUrl = 'https://panel.spruko.com/api/api/apidetail/'. $token;
         $checkedUrl = Curl::fetch($checkUrl);

         $datas = json_decode($checkedUrl);

         return $datas;
     }

     private function verifysettingupdate($verifyupdate, $name, $email)
     {
         $data = array(
             'item_id' => config('installer.requirements.itemId'),
             'name' => $name,
             'email' => $email,
             'purchaseCode' => $verifyupdate,
             'url' => url('/')
         );

         $payload = json_encode($data);

         // update cURL resource
         $ch = curl_init('https://panel.spruko.com/api/api/apiupdate/');

         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLINFO_HEADER_OUT, true);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

         // Set HTTP Header for POST request
         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
         'Content-Type: application/json',
         'Content-Length: ' . strlen($payload))
         );

         // Submit the POST request
         $result = curl_exec($ch);


         // Close cURL session handle
         curl_close($ch);

         // Format object data
         $result = json_decode($result);
         return $result;
     }

     private function verifysettingcreate($verifyupdate, $firstname, $lastname, $email)
     {
         // A sample PHP Script to POST data using cURL
         // Data in JSON format
         $data = array(
             'item_id' => config('installer.requirements.itemId'),
             'name' => $firstname . ' ' . $lastname,
             'email' => $email,
             'purchaseCode' => $verifyupdate,
             'url' => url('/')
         );

         $payload = json_encode($data);

         // Prepare new cURL resource
         $ch = curl_init('https://panel.spruko.com/api/api/newlaravelapicreate');
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLINFO_HEADER_OUT, true);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

         // Set HTTP Header for POST request
         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
             'Content-Type: application/json',
             'Content-Length: ' . strlen($payload))
         );

         // Submit the POST request
         $result = curl_exec($ch);


         // Close cURL session handle
         curl_close($ch);

         // Format object data
         $result = json_decode($result);
         return $result;
     }


     private function updatesettingapi($verifyupdate)
     {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, "https://panel.spruko.com/api/api/apidetail/". $verifyupdate);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         $result = curl_exec($ch);
         curl_close($ch);

         return $result;
     }

    private function verifysettingtoken($verifyupdate, $app_token)
    {
        // A sample PHP Script to POST data using cURL
        // Data in JSON format
        $data = array(
            'item_id' => config('installer.requirements.itemId'),
            'app_token' => $app_token,
            'purchaseCode' => $verifyupdate,
            'url' => url('/')
        );
        $payload = json_encode($data);

        // Prepare new cURL resource
        $ch = curl_init('https://panel.spruko.com/api/api/larvelapitoken');
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

        // Format object data
        $result = json_decode($result);
        return $result;
    }


}
