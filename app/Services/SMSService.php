<?php

namespace App\Services;

use App\Models\Contract\ContractSms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SMSService
{
    public static function sendSMS($phone, $message, $contract_id = null)
    {

        $url = 'http://online.aic.uz/apex/ins/sms-push/send-re'; // Replace with your authentication endpoint URL
        $username = 'SMS_RE'; // Replace with the provided login username
        $password = 'smsre!2023@'; // Replace with the provided login password

        $credentials = $username . ':' . $password;
        $authToken = base64_encode($credentials);
        $payload = [
            'phone' => $phone,
            'message' => $message,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain',
                'Authorization: Basic '. $authToken
            ),
        ));
        $response = curl_exec($curl);
        Log::info("SMS: ".$response);
        curl_close($curl);
        $response = json_decode($response, true);
        // { "result":true ,"message":"OK" }
        ContractSms::create([
            'contract_id' => $contract_id,
            'phone' => $phone,
            'message' => $message,
            'is_sent' => 1,
            'created_by' => auth()->id(),
        ]);
        return $response;
    }
}
