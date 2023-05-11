<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * @var Category
     */
    private $category;
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
        Client $client,
        Category $category
    )
    {
        $this->asaasService = $asaasService;
        $this->client = $client;
        $this->category  = $category;
    }

    /**
     * Listar categorias
     */
    public function index(Request $request)
    {
        try {

            $categories = $this->category->search($request->q)->withCount('products')->get();

            return response()->json($categories);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 500);
        }
    }

    /**
     * OK Cadastrar Cliente
     */
    public function store(Request $request)
    {
        try {
            // Valida o CPF ou CNPJ
            $cpf_cnpj = unformatCpfCnpj($request->cpf_cnpj);

            if(!validateCpfCnpj($cpf_cnpj)){
                return response()->json('CPF ou CNPJ inválido.', 422);
            }

            // Verifica se o cliente existe
            $client = $this->client->findCpfCnpj($cpf_cnpj)->first();

            // Cadastra o cliente
            if(!$client) {
                $this->client->create(['cpf_cnpj' => $cpf_cnpj]);

                // Recupero novamente o cliente com todos atributos
                $client = $this->client->findCpfCnpj($cpf_cnpj)->first();
            }

            return response()->json($client);

        } catch (\Exception $e) {
            return response()->json('Erro ao processar requisição.', 400);
        }
    }

    /**
     * Atualizar uma categoria específica
     */
    public function update(Request $request, $id)
    {
        try {

            // Verifica se o cliente existe
            $client = $this->client->find($id);
            if (!$client) {
                return response()->json('Cliente não encontrado.', 404);
            }

            // Valida o formulário
            $validate = Validator::make($request->all(), [
                'name'  => 'required|string|max:50',
                'email' => 'required|string|email|max:50',
                'phone' => 'required|string|min:11|max:11',

                'postal_code'    => 'required|string|min:9|max:9',
                'address'        => 'required|string|max:100',
                'province'       => 'required|string|max:50',
                'address_number' => 'required|string|max:10',
                'complement'     => 'max:50',
            ]);

            if($validate->fails()){
                return response()->json(formatValidate($validate->errors()), 422);
            }

            // Realiza o cadastro do cliente na ASAAS
            if(!$client->asaas_id){

                $response = $this->asaasService->createClient($request->all());

                dd('response', $response);
            }

            dd($client);


            $category->update($data);

            return response()->json($category);

        } catch (\Exception $e) {
            return response()->json('Erro ao processar requisição', 400);
        }
    }

    /**
     * Remover uma categoria específica
     */
    public function destroy($id)
    {
        try {

            $category = $this->category->find($id);

            if (!$category) {
                return response()->json(['message' => 'Categoria não encontrada'], 404);
            }

            // Exclui todos os produtos relacionados com a categoria
            $category->products()->delete();

            // Exclui a categoria
            $category->delete();

            return response()->json(['message' => 'Categoria removida com sucesso']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 422);
        }
    }
}
