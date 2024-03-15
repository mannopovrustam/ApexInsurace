<?php

namespace App\Services;

use Carbon\Carbon;

class EmitRepaymentService
{
    protected $headers;

    public function __construct()
    {
        $this->headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(env('EMIT_USERNAME') . ':' . env('EMIT_PASSWORD'))
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

        $details['firstName'] = $this->cyrillicToLatin($fullname[0]);
        $details['lastName'] = $this->cyrillicToLatin($fullname[1]);
        $details['middleName'] = $this->cyrillicToLatin(implode(" ", $thirdAndBeyond));
        $details['pinfl'] = $contract->pinfl;
        $details['passportSeries'] = substr($contract->passport,0,2);
        $details['passportNumber'] = substr($contract->passport, 2);
        $details['contractNumber'] = $contract->contract_name;
        $details['terminalGroupId'] = 40;

        $postData = [
            "jsonrpc" => "2.0",
            "method" => "repayment.client.create",
            "id" => "1",
            "params" => $details
        ];

        \Log::info('Request from EMIT clientCreate: ' . json_encode($postData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT clientCreate: ' . $response);
        return $response;
    }

    public function autopayCreate($detailsAutopay)
    {
        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.create",
            "id"=> "1",
            "params"=> $detailsAutopay
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayCreate: ' . $response);
        return $response;
    }

    public function autopayCreateList()
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.create.list",
            "id"=> "1",
            "params"=> [
                "autopays" => [
                    [
                        "contractId" => "0jw69in6x93e446cb4i87sbt2a70pn26",
                        "amount" => 100000, //in tiyins
                        "startDate" => "20220924" //yyyyMMdd *not required
                    ], []
                ]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayCreateList: ' . $response);
        return $response;
    }

    public function autopayUpdate($auto_pay, $amount)
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.update",
            "id"=> "1",
            "params"=> [
                "autopayId"=> "$auto_pay",
                "amount"=> $amount, //in tiyins
            ]
        ];
        \Log::info('Request from EMIT autopayUpdate: ' . json_encode($postData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayUpdate: ' . $response);
        return $response;
    }

    public function autopayGet($auto_pay)
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.get",
            "id"=> "1",
            "params"=> [
                "autopayId"=> "$auto_pay",
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayGet: ' . $response);
        return $response;
    }

    public function autopayPause($auto_pay)
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.pause",
            "id"=> "1",
            "params"=> [
                "autopayId"=> "$auto_pay",
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayPause: ' . $response);
        return $response;
    }

    public function autopayResume($auto_pay)
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.resume",
            "id"=> "1",
            "params"=> [
                "autopayId"=> "$auto_pay",
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayResume: ' . $response);
        return $response;
    }

    public function autopayPauseall()
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.pauseall",
            "id"=> "1"
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayPauseall: ' . $response);
        return $response;
    }

    public function autopayResumeall()
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.resumeall",
            "id"=> "1"
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayResumeall: ' . $response);
        return $response;
    }

    public function autopayStop($auto_pay)
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.stop",
            "id"=> "1",
            "params"=> [
                "autopayId"=> "$auto_pay",
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayStop: ' . $response);
        return $response;
    }

    public function autopayStopall()
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.autopay.stopall",
            "id"=> "1"
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT autopayStopall: ' . $response);
        return $response;
    }

    public function transHistory($startDate,$endDate)
    {

//        $startDate = Carbon::now()->subDay()->format('Ymd');
//        $endDate = Carbon::now()->subDay()->format('Ymd');

        $postData = [
            "jsonrpc" => "2.0",
            "method" => "repayment.trans.history",
            "id" => "1",
            "params" => [
                "startDate" => $startDate, //yyMMdd
                "endDate" => $endDate //yyMMdd
            ]
        ];
        \Log::info('Request from EMIT transHistory: ' . json_encode($postData));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT transHistory: ' . $response);
        return $response;
    }

    public function transAutopayHistory($auto_pay)
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.trans.autopay.history",
            "id"=> "1",
            "params"=> [
                "autopayId"=> "$auto_pay",
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT transAutopayHistory: ' . $response);
        return $response;
    }

    public function transReverseExt()
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.trans.reverse.ext",
            "id"=> "1",
            "params"=> [
                "ext"=> "a85a3d5a252d4518f7e34e3a0d2"
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT transReverseExt: ' . $response);
        return $response;
    }

    public function transReverseRefnum($refnum)
    {

        $postData = [
            "jsonrpc"=> "2.0",
            "method"=> "repayment.trans.reverse.refnum",
            "id"=> "1",
            "params"=> [
                "refnum"=> "$refnum"
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('EMIT_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        \Log::info('Response from EMIT transReverseRefnum: ' . $response);
        return $response;
    }

}
