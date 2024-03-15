<?php

namespace App\Services;

use Carbon\Carbon;

class UniAccessService
{
    protected $headers;

    public function __construct()
    {
        $this->headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer bL2n95eCPf5vPIewQTiT3omh2cbVK3e2faCFWnuLhww63J3jyzrtfN2ya5oJ8OML'
        );
    }

    function cyrillicToLatin($text) {
        $cyrillic = array(
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'ў', 'ғ', 'қ', 'ҳ',
            'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'ъ', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Ў', 'Ғ', 'Қ', 'Ҳ',
            'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Ъ', 'Ь', 'Э', 'Ю', 'Я'
        );

        $latin = array(
            'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'j', 'z', 'i', 'y', 'k', 'l', 'm', 'o‘', 'g‘', 'q', 'h',
            'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'x', 'ts', 'ch', 'sh', '\'', '', 'e', 'yu', 'ya',
            'A', 'B', 'V', 'G', 'D', 'E', 'Yo', 'J', 'Z', 'I', 'Y', 'K', 'L', 'M', 'O‘', 'G‘', 'Q', 'H',
            'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'X', 'Ts', 'Ch', 'Sh', '\'', '', 'E', 'Yu', 'Ya'
        );

        return str_replace($cyrillic, $latin, $text);
    }

    public function clientCreate($contract)
    {
        $fullname = explode(" ", $contract->fullname);
        $thirdAndBeyond = array_slice($fullname, 2);

        $details["pinfl"] = $contract->pinfl;
        $details["passport_id"] = $contract->passport;
        $details["loan_id"] = $contract->contract_name;
        $details["ext_id"] = $contract->id.'+'.$contract->contract_name;
        $details["firstname"] = $this->cyrillicToLatin($fullname[0]);
        $details["lastname"] = $this->cyrillicToLatin($fullname[1]);
        $details["middlename"] = $this->cyrillicToLatin(implode(" ", $thirdAndBeyond));
        $details["middlename"] = $details["middlename"] == "" ? "XXX" : $details["middlename"];
        $details["filial_id"] = 1;
        $details["debit_amount"] = $contract->residue*100;
        $details["phones"] = explode(',',$contract->phone);

        $postData = [
            "method" => "client.create",
            "params" => $details
        ];

        \Log::info('Request from UNIACCESS clientCreate: ' . json_encode($postData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('UNIACCESS_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from UNIACCESS clientCreate: ' . $response);
        return $response;
    }

    public function autopayToggle($loan_id, $on)
    {

        $postData = [
            "method"=> "client.auto.toggle",
            "params"=> [
                "loan_id"=> "$loan_id",
                "auto"=>$on
            ]
        ];

        \Log::info("Request from UNIACCESS autopayToggle - $on: " . json_encode($postData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('UNIACCESS_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info("Response from UNIACCESS autopayToggle - $on: " . $response);
        return $response;
    }

    public function debitUpdate($loan_id, $amount)
    {
        $postData = [
            "method"=> "client.debit.update",
            "params"=> [
                "loan_id"=> "$loan_id",
                "remaining_amount"=> $amount
            ]
        ];

        \Log::info('Request from UNIACCESS debitUpdate: ' . json_encode($postData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env("UNIACCESS_URL"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from UNIACCESS debitUpdate: ' . $response);
        return $response;
    }

    public function paymentCancel($ext_id, $transaction_id)
    {
        $postData = [
            "method"=> "payment.cancel",
            "params"=> [
                "ext_id" => "$ext_id",
                "transaction_id" => "$transaction_id"
            ]
        ];

        \Log::info('Request from UNIACCESS paymentCancel: ' . json_encode($postData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env("UNIACCESS_URL"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from UNIACCESS paymentCancel: ' . $response);
        return $response;
    }

    public function getPayments($data)
    {
        $postData = [
            "method" => "payment.get",
            "params" => [
                "page_size" => 100,
                "page_number" => 0,
                "date_from" =>  "$data",
                "date_to" =>  "$data",
                "type" => "Online",
                "filial_id" => 1
            ]
        ];

        \Log::info('Request from UNIACCESS getPayment: ' . json_encode($postData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env("UNIACCESS_URL"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from UNIACCESS getPayment: ' . $response);
        return $response;
    }


}
