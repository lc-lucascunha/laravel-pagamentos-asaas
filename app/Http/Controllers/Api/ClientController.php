<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var AsaasService
     */
    private $asaasService;

    public function __construct(
        AsaasService $asaasService,
        Client $client
    )
    {
        $this->asaasService = $asaasService;
        $this->client = $client;
    }

    /**
     * Cadastrar Cliente
     */
    public function store(Request $request)
    {
        try {
            $statusHttp = 200;

            // Valida o CPF ou CNPJ
            $cpf_cnpj = formatOnlyNumber($request->cpf_cnpj);

            if(!validateCpfCnpj($cpf_cnpj)){
                return response()->json('O CPF ou CNPJ informado é inválido.', 422);
            }

            // Verifica se o cliente existe
            $client = $this->client->findCpfCnpj($cpf_cnpj)->first();

            // Cadastra o cliente
            if(!$client) {
                $statusHttp = 201;

                $this->client->create(['cpf_cnpj' => $cpf_cnpj]);

                // Recupero novamente o cliente com todos atributos
                $client = $this->client->findCpfCnpj($cpf_cnpj)->first();
            }

            return response()->json($client, $statusHttp);

        } catch (\Exception $e) {
            return response()->json('Erro ao processar requisição.', 400);
        }
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

    /**
     * Obter os Cartões de Crédito de um Cliente
     */
    public function cards($id)
    {
        try {
            // Verifica se o cliente existe
            $client = $this->client->find($id);
            if (!$client) {
                return response()->json('Cliente não encontrado.', 404);
            }

            // Recupera os cartões desse cliente
            $cards = $client->cards()->orderBy('created_at', 'desc')->get();

            return response()->json($cards);

        } catch (\Exception $e) {
            return response()->json('Erro ao processar requisição.', 400);
        }
    }

    /**
     * Obter os Pagamentos de um Cliente
     */
    public function payments($id)
    {
        try {
            // Verifica se o cliente existe
            $client = $this->client->find($id);
            if (!$client) {
                return response()->json('Cliente não encontrado.', 404);
            }

            // Recupera os pagamentos desse cliente
            $cards = $client->payments()->orderBy('created_at', 'desc')->get();

            return response()->json($cards);

        } catch (\Exception $e) {
            return response()->json('Erro ao processar requisição.', 400);
        }
    }



}
