<?php

namespace App\Services;

class AsaasService
{
    /**
     * Cadastrar Cliente
     */
    public function createClient($data)
    {
        $data = json_encode([
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

        return asaasCurlSend(
            'POST',
            '/api/v3/customers', $data
        );
    }

    /**
     * Atualizar Cliente
     */
    public function updateClient($data, $id)
    {
        $data = json_encode([
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

        return asaasCurlSend(
            'POST',
            '/api/v3/customers/'.$id, $data
        );
    }

    /**
     * Pagamento por PIX
     */
    public function paymentPix($data)
    {
        $data = json_encode([
            "externalReference" => $data['id'],
            "customer"          => $data['customer'],
            "billingType"       => $data['billing_type'],
            "dueDate"           => $data['due_date'],
            "value"             => $data['value'],
            "description"       => $data['description'],
        ]);

        return asaasCurlSend(
            'POST',
            '/api/v3/payments', $data
        );
    }

    /**
     * Pagamento por BOLETO
     */
    public function paymentBoleto($data)
    {
        $data = json_encode([
            "externalReference" => $data['id'],
            "customer"          => $data['customer'],
            "billingType"       => $data['billing_type'],
            "dueDate"           => $data['due_date'],
            "value"             => $data['value'],
            "description"       => $data['description'],
            "discount" => [
                "value" => 10,
                "dueDateLimitDays" => 0
            ],
            "fine" => [
                "value" => 1
            ],
            "interest" => [
                "value" => 2
            ],
        ]);

        return asaasCurlSend(
            'POST',
            '/api/v3/payments', $data
        );
    }

    /**
     * Pagamento por CARTÃO DE CRÉDITO
     */
    public function paymentCreditCard($data)
    {
        $data = json_encode($data);

        return asaasCurlSend(
            'POST',
            '/api/v3/payments', $data
        );
    }

    /**
     * Recupera o QRCode do PIX
     */
    public function getPixQrCode($id)
    {
        return asaasCurlSend(
            'GET',
            '/api/v3/payments/'.$id.'/pixQrCode'
        );
    }

    /**
     * Recupera um pagamento
     */
    public function getPayment($id)
    {
        return asaasCurlSend(
            'GET',
            '/api/v3/payments/'.$id
        );
    }

    /**
     * Recupera um pagamento parcelado
     */
    public function getPaymentInstallment($installment)
    {
        return asaasCurlSend(
            'GET',
            '/api/v3/payments?limit=12&installment='.$installment
        );
    }
}
