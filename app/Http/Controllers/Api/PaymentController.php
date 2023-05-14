<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Client;
use App\Models\Payment;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var AsaasService
     */
    private $asaasService;
    /**
     * @var Payment
     */
    private $payment;
    /**
     * @var Card
     */
    private $card;

    public function __construct(
        AsaasService $asaasService,
        Client $client,
        Payment $payment,
        Card $card
    )
    {
        $this->asaasService = $asaasService;
        $this->client = $client;
        $this->payment = $payment;
        $this->card = $card;
    }

    /**
     * Exibir pagamento
     */
    public function show($id)
    {
        try {
            // Recupera o pagamento
            $payment = $this->payment->find($id);
            if (!$payment) {
                return response()->json('Pagamento não localizado.', 404);
            }

            // Recupero o retorno do pagamento
            switch ($payment->billing_type){
                case 'PIX':
                    $response = $this->asaasService->getPixQrCode($payment->asaas_id);
                    break;
                case 'BOLETO':
                    $response = ['status' => 200, 'data' => $payment->bank_slip_url];
                    break;
                case 'CREDIT_CARD':
                    if($payment->installment_token){
                        $response = $this->asaasService->getPaymentInstallment($payment->installment_token);
                        if($response['status'] == 200){
                            $response['data'] = $response['data']['data'];
                        }
                    }
                    else{
                        $response = $this->asaasService->getPayment($payment->asaas_id);
                        if($response['status'] == 200){
                            $response['data'] = [$response['data']];
                        }
                    }
            }

            return response()->json($response['data'], $response['status']);

        } catch (\Exception $e) {
            return response()->json('Erro ao processar requisição.', 400);
        }
    }

    /**
     * Realizar pagamento
     */
    public function store(Request $request)
    {
        try {
            $request = $request->all();
            $errors  = [];

            // Validação do cliente
            $client = $this->client->find($request['client']['id']);
            if(!$client){
                $errors[] = 'Cliente não localizado.';
            }
            elseif(!$client->asaas_id){
                $errors[] = 'Cliente não autorizado realizar essa ação.';
            }

            // Validação das informações básicas
            if(!$request['value']){
                $errors[] = 'Selecione o produto que deseja contratar.';
            }
            if(!$request['type']){
                $errors[] = 'Selecione a forma de pagamento.';
            }
            elseif(!in_array($request['type'], asaasGetTypes(true))){
                $errors[] = 'Selecione uma forma de pagamento válida.';
            }

            // Caso tenha algum erro
            if(!empty($errors)){
                $errors = formatValidate($errors, false);
                return response()->json($errors, 422);
            }

            // Obtem o retorno do pagamento
            switch ($request['type']){
                case 'PIX':
                    $response = $this->storePix($client, $request);
                    break;
                case 'BOLETO':
                    $response = $this->storeBoleto($client, $request);
                    break;
                case 'CREDIT_CARD':
                    $response = $this->storeCreditCard($client, $request);
            }

            return response()->json($response['data'], $response['status']);

        } catch (\Exception $e) {
            return response()->json('Erro ao processar requisição.', 400);
        }
    }

    /**
     * Realizar pagamento por PIX
     */
    private function storePix($client, $request)
    {
        // Cadastro com transação
        DB::beginTransaction();

        // Cadastra o pagamento na base de dados
        $payment = $this->payment->create(
            asaasGetDataCreatePayment($client, $request)
        );

        // Cadastra o pagamento na ASAAS
        $response = $this->asaasService->paymentPix($payment->toArray());

        if($response['status'] != 200){
            return $response;
        }

        // Atualiza as informações do pagamento
        $payment->update([
            'asaas_id' => $response['data']['id'],
            'due_date' => $response['data']['dueDate'],
            'value'    => $response['data']['value'],
            'status'   => $response['data']['status'],
        ]);

        // Persiste as informações na base de dados
        DB::commit();

        return [
            'status' => 200,
            'data'   => $payment->toArray(),
        ];
    }

    /**
     * Realizar pagamento por BOLETO
     */
    private function storeBoleto($client, $request)
    {
        // Cadastro com transação
        DB::beginTransaction();

        // Cadastra o pagamento na base de dados
        $payment = $this->payment->create(
            asaasGetDataCreatePayment($client, $request)
        );

        // Cadastra o pagamento na ASAAS
        $response = $this->asaasService->paymentBoleto($payment->toArray());

        if($response['status'] != 200){
            return $response;
        }

        // Atualiza as informações do pagamento
        $payment->update([
            'asaas_id'      => $response['data']['id'],
            'due_date'      => $response['data']['dueDate'],
            'value'         => $response['data']['value'],
            'bank_slip_url' => $response['data']['bankSlipUrl'],
            'status'        => $response['data']['status'],
        ]);

        // Persiste as informações na base de dados
        DB::commit();

        return [
            'status' => 200,
            'data'   => $payment->toArray(),
        ];
    }

    /**
     * Realizar pagamento por CARTÃO DE CRÉDITO
     */
    private function storeCreditCard($client, $request)
    {
        // Cadastro com transação
        DB::beginTransaction();

        // Cadastra o pagamento na base de dados
        $payment = $this->payment->create(
            asaasGetDataCreatePayment($client, $request)
        );

        $dataHolderInfo = [];
        $dataCreditCard = [];

        // Recupera o token, caso seja um cartão já utilizado
        // e cadastrado para esse cliente
        if($request['card']){
            $card = $this->card->find($request['card']);
            if(!$card){
                return [
                    'status' => 422,
                    'data' => '- Cartão de Crédito não localizado.'
                ];
            }
            $dataCreditCard = ['creditCardToken' => $card->token];
        }
        else{
            $errorsCreditCard = '';
            $errorsHolderInfo = '';

            // Recupera os dados do cartão
            $dataCreditCard = [
                'creditCard' => [
                    'holderName'  => $request['credit_card']['holder_name'],
                    'number'      => $request['credit_card']['number'],
                    'expiryMonth' => $request['credit_card']['expiry_month'],
                    'expiryYear'  => $request['credit_card']['expiry_year'],
                    'ccv'         => $request['credit_card']['ccv'],
                ],
            ];

            $validate = Validator::make($dataCreditCard, [
                'creditCard.number'      => 'required|string|min:13|max:16',
                'creditCard.holderName'  => 'required|string|max:50',
                'creditCard.expiryMonth' => 'required|string|min:1|max:2',
                'creditCard.expiryYear'  => 'required|string|min:4|max:4',
                'creditCard.ccv'         => 'required|string|min:3|max:3',
            ]);

            if($validate->fails()){
                $errorsCreditCard = formatValidate($validate->errors());
            }

            // Recupera as informações do titular do cartão
            if($request['is_holder'] == 'yes'){
                $dataHolderInfo = [
                    'creditCardHolderInfo' => [
                        'name'          => $client->name,
                        'email'         => $client->email,
                        'cpfCnpj'       => $client->cpf_cnpj,
                        'phone'         => $client->phone,

                        'postalCode'    => $client->postal_code,
                        'address'       => $client->address,
                        'addressNumber' => $client->address_number,
                        'complement'    => $client->complement,
                        'province'      => $client->province,
                    ],
                ];
            }
            else {
                $dataHolderInfo = [
                    'creditCardHolderInfo' => [
                        'name'          => $request['holder']['name'],
                        'email'         => $request['holder']['email'],
                        'cpfCnpj'       => $request['holder']['cpf_cnpj'],
                        'phone'         => $request['holder']['phone'],

                        'postalCode'    => $request['holder']['postal_code'],
                        'address'       => $request['holder']['address'],
                        'addressNumber' => $request['holder']['address_number'],
                        'complement'    => $request['holder']['complement'],
                        'province'      => $request['holder']['province'],
                    ],
                ];

                $validate = Validator::make($dataHolderInfo['creditCardHolderInfo'], [
                    'cpfCnpj'       => 'required|string|min:11|max:14',
                    'name'          => 'required|string|max:50',
                    'email'         => 'required|string|email|max:50',
                    'phone'         => 'required|string|min:11|max:11',

                    'postalCode'    => 'required|string|min:8|max:8',
                    'address'       => 'required|string|max:100',
                    'province'      => 'required|string|max:50',
                    'addressNumber' => 'required|string|max:10',
                    'complement'    => 'max:50',

                ]);

                if(!validateCpfCnpj($dataHolderInfo['creditCardHolderInfo']['cpfCnpj'])){
                    $errorsHolderInfo = '- O campo CPF ou CNPJ é inválido.'.PHP_EOL;
                }

                if($validate->fails()){
                    $errorsHolderInfo .= formatValidate($validate->errors());
                }
            }

            // Caso tenha encontrado algum erro
            if($errorsCreditCard || $errorsHolderInfo) {
                $errors = implode(PHP_EOL, [$errorsCreditCard, $errorsHolderInfo]);
                return [
                    'status' => 422,
                    'data'   => $errors,
                ];
            }
        }

        // Recupera as informações básicas da transção
        $data = [
            'externalReference' => $payment->id,
            'customer'          => $payment->customer,
            'billingType'       => $payment->billing_type,
            'dueDate'           => $payment->due_date,
            'description'       => $payment->description,
        ];

        // Validação do valor e parcelas
        $dataValue = ['value' => $payment->value];

        if($payment->installment){
            $dataValue = [
                'totalValue' => $payment->value,
                'installmentCount' => $payment->installment,
            ];
        }

        // Cadastra o pagamento na ASAAS
        $data = array_merge($data, $dataValue, $dataCreditCard, $dataHolderInfo);

        $response = $this->asaasService->paymentCreditCard($data);

        if($response['status'] != 200){
            return $response;
        }

        // Atualiza as informações do pagamento
        $dataUpdate = [
            'asaas_id' => $response['data']['id'],
            'status'   => $response['data']['status'],
        ];

        if($payment->installment){
            unset($dataUpdate['asaas_id']);
            $dataUpdate['installment_token'] = $response['data']['installment'];
        }

        $payment->update($dataUpdate);

        // Caso for um novo cartão utilizado nessa transação,
        // cadastra o token dele para esse cliente
        if(!$request['card']){
            $this->card->create([
                'client_id' => $client->id,
                'name'      => "({$response['data']['creditCard']['creditCardNumber']}) {$response['data']['creditCard']['creditCardBrand']}",
                'token'     => $response['data']['creditCard']['creditCardToken'],
            ]);
        }

        // Persiste as informações na base de dados
        DB::commit();

        return [
            'status' => 200,
            'data'   => $payment->toArray(),
        ];
    }
}
