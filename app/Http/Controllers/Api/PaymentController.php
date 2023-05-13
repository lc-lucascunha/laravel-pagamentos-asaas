<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function __construct(
        AsaasService $asaasService,
        Client $client,
        Payment $payment
    )
    {
        $this->asaasService = $asaasService;
        $this->client = $client;
        $this->payment = $payment;
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

            // Recupero o retorno
            switch ($payment->billing_type){
                case 'PIX':
                    $response = $this->asaasService->getPixQrCode($payment->asaas_id);
                    break;
                case 'BOLETO':
                    $response = [
                        'status' => 200,
                        'data'   => $payment->bank_slip_url,
                    ];
                    break;
                case 'CREDIT_CARD':
                    $response = [];
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
            elseif(!in_array($request['type'], ['PIX', 'BOLETO', 'CREDIT_CARD'])){
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

        // Cadastra o pagamento
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

        // Cadastra o pagamento
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
        dd('CARTÃO DE CRÉDITO', $request, $client);

        // Realiza o rollback das informações na base de dados
        //DB::rollBack();
    }








    /**
     * Atualizar Cliente
     */
    public function update(Request $request, $id)
    {
        try {
            $request = $request->all();

            // Verifica se o cliente existe
            $client = $this->client->find($id);
            if (!$client) {
                return response()->json('Cliente não encontrado.', 404);
            }

            // Valida o formulário
            $validate = Validator::make($request, [
                'name'  => 'required|string|max:50',
                'email' => 'required|string|email|max:50',
                'phone' => 'required|string|min:11|max:11',

                'postal_code'    => 'required|string|min:8|max:8',
                'address'        => 'required|string|max:100',
                'province'       => 'required|string|max:50',
                'address_number' => 'required|string|max:10',
                'complement'     => 'max:50',
            ]);

            if($validate->fails()){
                return response()->json(formatValidate($validate->errors()), 422);
            }

            // Cadastra o cliente na ASAAS
            if(!$client->asaas_id){
                $response = $this->asaasService->createClient($request);

                if($response['status'] == 200){
                    $client->asaas_id = $response['data']['id'];
                    $client->save();
                }
                else{
                    return response()->json($response['data'], $response['status']);
                }
            }

            // Atualiza o cliente na ASAAS
            else{
                $response = $this->asaasService->updateClient($request, $client->asaas_id);

                if($response['status'] != 200){
                    return response()->json($response['data'], $response['status']);
                }
            }

            // Atualiza o cliente na Base de Dados
            unset($request['id']);
            unset($request['asaas_id']);
            unset($request['cpf_cnpj']);

            $client->update($request);

            return response()->json($client);

        } catch (\Exception $e) {
            return response()->json('Erro ao processar requisição.', 400);
        }
    }

}
