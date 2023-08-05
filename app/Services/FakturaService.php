<?php

namespace App\Services;

use App\Models\Contract\ContractHybrid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FakturaService
{

    const STATUS = [
        0 => "Доставлен",
        1 => "Адресат умер",
        2 => "Адресат по указанному адресу не проживает",
        3 => "Указан не полный адрес",
        4 => "Адресат от получения отказался",
        5 => "Нет дома",
        6 => "Не явился по извещению",
        7 => "Адресат не определен",
        8 => "Попытка вручения",
        9 => "По указанному адресу организация не найдена",
        -2 => "В процессе обработки",
        -1 => "Создан",
    ];


    public function getToken()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://account.faktura.uz/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'password',
                'username' => '998909032444',
                'password' => '9032444jk',
                'client_id' => 'apexsystem',
                'client_secret' => 'uoxFqWzn5B3bi4LtqMu1iNdcGHB2c95spMPSZcVDmzchNMciiNT0qpXODbIc',
            ]),
        ));

        $response = curl_exec($curl);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);
            return $error;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($statusCode >= 200 && $statusCode < 300) {
            $data = json_decode($response, true);
            $token = $data['access_token'];
            // Use the token as needed
            curl_close($curl);
            if (env('IS_IDE',0) == 1) return $token;
            return $response;
        } else {
            // Handle the API error response
            curl_close($curl);
            return $response;
        }
    }

    public function getSendRequest($url, $method, $postData = [], $contractId)
    {

        $token = $this->getToken();

        if (isset($token['error'])) return 'Error: ' . $token['error'];

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        );

        \Log::info($postData);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        \Log::info('Response from Faktura' . $response);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);

            \Log::info('Response Error' . $response);

            return $error;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        \Log::info('Response StatusCode' . $statusCode);

        if ($statusCode >= 200 && $statusCode < 300) {

            $data = json_decode($response, true);
            $uid = $data['Data']['Uid'];
            $createdAt = $data['Data']['CreatedDate'];
            $status = $data['Data']['Status'];

            ContractHybrid::create([
                'contract_id' => $contractId,
                'uid' => $uid,
                'created_at' => $createdAt,
                'status' => $status,
            ]);

//            User::auditable('hybrid', $uid, $postData,'S');
            // Request successful
            curl_close($curl);
            return $response;

        } else {
            // Handle the API error response
            curl_close($curl);
            return $response;
        }
    }


    public function getDetail($uid)
    {
        $url = "https://api.faktura.uz/Api/HybridDocument/GetDetails?uid=$uid&companyInn=" . env('COMPANY_INN');
        $token = $this->getToken();
        \Log::info($token);
        if (isset($token['error'])) return 'Error: ' . $token['error'];

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        );


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        \Log::info('Response from Faktura' . $response);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);

            \Log::info('Response Error' . $response);

            return $error;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        \Log::info('Response StatusCode' . $statusCode);

        if ($statusCode >= 200 && $statusCode < 300) {
            // Request successful
            curl_close($curl);
            return json_decode($response);
        } else {
            // Handle the API error response
            curl_close($curl);
            return $response;
        }
    }


    public function getDelete($uid)
    {

        $url = "https://api.faktura.uz/Api/HybridDocument/Delete?uid=$uid&companyInn=" . env('COMPANY_INN');

        $token = $this->getToken();
        \Log::info($token);
        if (isset($token['error'])) return 'Error: ' . $token['error'];

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        );


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        \Log::info('Response from Faktura' . $response);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);

            \Log::info('Response Error' . $response);

            return $error;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        \Log::info('Response StatusCode' . $statusCode);

        if ($statusCode >= 200 && $statusCode < 300) {
            // Request successful
            curl_close($curl);
            return json_decode($response);
        } else {
            // Handle the API error response
            curl_close($curl);
            return $response;
        }
    }

    public function getStatuses()
    {

        $url = "https://api.faktura.uz/Api/HybridDocument/GetStatuses";

        $token = $this->getToken();
        \Log::info($token);
        if (isset($token['error'])) return 'Error: ' . $token['error'];

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        \Log::info('Response from Faktura' . $response);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);

            \Log::info('Response Error' . $response);

            return $error;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        \Log::info('Response StatusCode' . $statusCode);

        if ($statusCode >= 200 && $statusCode < 300) {
            // Request successful
            curl_close($curl);
            return json_decode($response);
        } else {
            // Handle the API error response
            curl_close($curl);
            return $response;
        }
    }

    public function getPreview($uid, $check = false)
    {
        $check = $check ? 1 : 0;
        $url = "https://api.faktura.uz/Api/HybridDocument/GetPreview/$uid?withCheck=$check&companyInn=" . env('COMPANY_INN');

        $token = $this->getToken();
        \Log::info($token);
        if (isset($token['error'])) return 'Error: ' . $token['error'];

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        \Log::info('Response from Faktura' . $response);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);

            \Log::info('Response Error' . $response);

            return $error;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        \Log::info('Response StatusCode' . $statusCode);

        if ($statusCode >= 200 && $statusCode < 300) {
            // Request successful
            curl_close($curl);
            return $response;
        } else {
            // Handle the API error response
            curl_close($curl);
            return $response;
        }
    }


    public function getRegions()
    {
        $url = "https://api.faktura.uz/Api/HybridDocument/GetRegions";

        $token = $this->getToken();
        \Log::info($token);
        if (isset($token['error'])) return 'Error: ' . $token['error'];

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        \Log::info('Response from Faktura' . $response);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);

            \Log::info('Response Error' . $response);

            return $error;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        \Log::info('Response StatusCode' . $statusCode);

        if ($statusCode >= 200 && $statusCode < 300) {
            // Request successful
            curl_close($curl);
            return $response;
        } else {
            // Handle the API error response
            curl_close($curl);
            return $response;
        }
    }

    public function getRegionsResponse($id)
    {
        $url = "https://api.faktura.uz/Api/HybridDocument/GetRegionsResponse?id=".$id;

        $token = $this->getToken();
        \Log::info($token);
        if (isset($token['error'])) return 'Error: ' . $token['error'];

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        \Log::info('Response from Faktura' . $response);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);

            \Log::info('Response Error' . $response);

            return $error;
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        \Log::info('Response StatusCode' . $statusCode);

        if ($statusCode >= 200 && $statusCode < 300) {
            // Request successful
            curl_close($curl);
            return $response;
        } else {
            // Handle the API error response
            curl_close($curl);
            return $response;
        }
    }
}
