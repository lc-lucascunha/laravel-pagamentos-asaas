<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class AsaasService
{
    public function __construct(

    )
    {

    }

    /**
     * Cadastrar Cliente
     */
    public function createClient($data)
    {
        try {
            $client = new Client();

            $headers = [
                'Content-Type' => 'application/json',
                'access_token' => env('ASAAS_KEY'),
            ];

            $body = json_encode([
                "externalReference" => $data['id'],
                "cpfCnpj"           => $data['cpf_cnpj'],
                "name"              => $data['name'],
                "email"             => $data['email'],
                "phone"             => $data['phone'],
                "mobilePhone"       => $data['phone'],
                "postalCode"        => $data['postal_code'],
                "address"           => $data['address'],
                "addressNumber"     => $data['address_number'],
                "complement"        => $data['complement'],
                "province"          => $data['province'],
            ]);

            $request = new Request(
                'POST',
                env('ASAAS_DOMAIN').'/api/v3/customers/',
                $headers,
                $body
            );

            $response = $client->sendAsync($request)->wait();

            return asaasFormatResponse($response);
        }
        catch (\Exception $e) {
            return asaasFormatResponse($e->getMessage());
        }
    }

    /**
     * Atualizar Cliente
     */
    public function updateClient($data, $id)
    {
        try {
            $client = new Client();

            $headers = [
                'Content-Type' => 'application/json',
                'access_token' => env('ASAAS_KEY'),
            ];

            $body = json_encode([
                "name"              => $data['name'],
                "email"             => $data['email'],
                "phone"             => $data['phone'],
                "mobilePhone"       => $data['phone'],
                "postalCode"        => $data['postal_code'],
                "address"           => $data['address'],
                "addressNumber"     => $data['address_number'],
                "complement"        => $data['complement'],
                "province"          => $data['province'],
            ]);

            $request = new Request(
                'POST',
                env('ASAAS_DOMAIN').'/api/v3/customers/'.$id,
                $headers,
                $body
            );

            $response = $client->sendAsync($request)->wait();

            return asaasFormatResponse($response);
        }
        catch (\Exception $e) {
            return asaasFormatResponse($e->getMessage());
        }
    }
}
