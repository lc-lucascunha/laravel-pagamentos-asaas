<?php
if (!function_exists('asaasGetDataCreatePayment')) {
    function asaasGetDataCreatePayment($client, $request)
    {
        return [
            'client_id'    => $client->id,
            'customer'     => $client->asaas_id,
            'billing_type' => $request['type'],
            'due_date'     => date('Y-m-d'),
            'value'        => floatval($request['value']),
            'description'  => $request['value_name'],
        ];
    }
}

if (!function_exists('asaasFormatResponse')) {
    function asaasFormatResponse($response){
        $status = '';
        $data   = '';
        if(is_string($response)){
            $errors = [];
            if (strpos($response, "401 Unauthorized") !== false) {
                $status   = 401;
                $errors[] = 'Operação não autorizada.';
            } else {
                $status = 422;
                $array  = explode(PHP_EOL, $response);
                foreach ($array as $key => $value){
                    $array[$key] = json_decode($value, true);
                }
                $array = array_filter($array);
                foreach ($array as $row){
                    if(isset($row['errors'])){
                        foreach ($row['errors'] as $erro){
                            if(isset($erro['description'])) {
                                $errors[] = '- '.asaasPtBr($erro['description']);
                            }
                        }
                    }
                }
            }
            $data = implode(PHP_EOL, $errors);
            if(empty($data)){
                $status = 400;
                $data   = "Erro ao processar requisição.";
            }
        }
        else{
            $status = intval($response->getStatusCode());
            $data   = json_decode($response->getBody()->getContents(), true);
        }
        return [
            'status' => $status,
            'data'   => $data,
        ];
    }
}

if (!function_exists('asaasPtBr')) {
    function asaasPtBr($string)
    {
        $de = [
            'name',
            'email',
        ];
        $para = [
            'Nome',
            'E-mail'
        ];
        return str_replace($de, $para, $string);
    }
}
