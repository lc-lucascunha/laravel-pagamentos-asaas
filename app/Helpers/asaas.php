<?php
//-- -------------------------------------------------
//-- GET DAS INFORMAÇÕES PADRÕES
//-- -------------------------------------------------

if (!function_exists('asaasGetTypes')) {
    function asaasGetTypes($onlyKeys = false)
    {
        $data = [
            'PIX'         => 'PIX',
            'BOLETO'      => 'BOLETO',
            'CREDIT_CARD' => 'CARTÃO DE CRÉDITO',
        ];
        return $onlyKeys ? array_keys($data) : $data;
    }
}

if (!function_exists('asaasGetDataCreatePayment')) {
    function asaasGetDataCreatePayment($client, $request)
    {
        $data = [
            'client_id'    => $client->id,
            'customer'     => $client->asaas_id,
            'billing_type' => $request['type'],
            'due_date'     => date('Y-m-d'),
            'value'        => floatval($request['value']),
            'installment'  => intval($request['installment']),
            'description'  => $request['value_name'],
        ];
        return $data;
    }
}

//-- -------------------------------------------------
//-- CURL
//-- -------------------------------------------------

if (!function_exists('asaasCurlSend')) {
    function asaasCurlSend($method, $endpoint, $data = '')
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, asaasCurlGetOpt(
                $method, $endpoint, $data
            ));

            $response = curl_exec($curl);

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

            return asaasCurlFormatResponse($response, $httpCode);
        }
        catch (\Exception $e) {
            return asaasCurlFormatResponse();
        }
    }
}

if (!function_exists('asaasCurlGetOpt')) {
    function asaasCurlGetOpt($method, $endpoint, $data = '')
    {
        if($method == 'POST') {
            return [
                CURLOPT_URL => env('ASAAS_DOMAIN') . $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'access_token: ' . env('ASAAS_KEY')
                ]
            ];
        }
        else if($method == 'GET'){
            return [
                CURLOPT_URL => env('ASAAS_DOMAIN').$endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'access_token: '.env('ASAAS_KEY')
                ]
            ];
        }
        else {
            return [];
        }
    }
}

if (!function_exists('asaasCurlFormatResponse')) {
    function asaasCurlFormatResponse($response = '', $httpCode = '')
    {
        if(!$response && !$httpCode){
            return [
                'status' => 400,
                'data'   => "Erro ao processar requisição.",
            ];
        }

        // Status code error
        $httpError = '';
        switch ($httpCode){
            case 401:
                $httpError = 'Operação não autorizada.';
                break;
            case 404:
                $httpError = 'O recurso solicitado não foi encontrado.';
                break;
            case 500:
                $httpError = 'Serviço indisponível no momento, tente novamente em alguns instantes.';
                break;
        }

        if(!empty($httpError)){
            return [
                'status' => $httpCode,
                'data'   => $httpError,
            ];
        }

        // Response error
        $response = json_decode($response, true);

        if(isset($response['errors'])){
            $errors = array_map(function ($error) {
                return '- ' . asaasPtBr($error['description']);
            }, $response['errors']);

            return [
                'status' => 422,
                'data'   => implode(PHP_EOL, $errors)
            ];
        }

        // Response success
        return [
            'status' => 200,
            'data'   => $response,
        ];
    }
}

//-- -------------------------------------------------
//-- TRADUÇÃO
//-- -------------------------------------------------

if (!function_exists('asaasPtBr')) {
    function asaasPtBr($str)
    {
        $de   = ['nome', 'name', 'email' , 'customer'];
        $para = ['Nome', 'Nome', 'E-mail', 'Cliente' ];
        $str  = str_replace($de, $para, $str);
        $de   = array_map('ucfirst', $de);
        return str_replace($de, $para, $str);
    }
}
