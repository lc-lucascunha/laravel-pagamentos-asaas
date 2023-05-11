<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @var Product
     */
    private $product;

    public function __construct(
        Product $product
    )
    {
        $this->product = $product;
    }

    /**
     * Listar produtos
     */
    public function index(Request $request)
    {
        try {

            $products = $this->product->search($request->q)->with('category')->get();

            return response()->json($products);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 500);
        }
    }

    /**
     * Exibir um produto específico
     */
    public function show($id)
    {
        try {

            $product = $this->product->with('category')->find($id);

            if (!$product) {
                return response()->json(['message' => 'Produto não encontrado'], 404);
            }

            return response()->json($product);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 422);
        }
    }

    /**
     * Cadastrar produto
     */
    public function store(Request $request)
    {
        try {

            $data = $this->validate($request, [
                'category_id' => 'required|integer',
                'name' => 'required|string|max:255',
            ]);

            $product = $this->product->create($data);

            return response()->json($product, 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 422);
        }
    }

    /**
     * Atualizar um produto específico
     */
    public function update(Request $request, $id)
    {
        try {

            $product = $this->product->find($id);

            if (!$product) {
                return response()->json(['message' => 'Produto não encontrado'], 404);
            }

            $data = $this->validate($request, [
                'category_id' => 'required|integer',
                'name' => 'required|string|max:255',
            ]);

            $product->update($data);

            return response()->json($product);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 422);
        }
    }

    /**
     * Remover um produto específico
     */
    public function destroy($id)
    {
        try {

            $product = $this->product->find($id);

            if (!$product) {
                return response()->json(['message' => 'Produto não encontrado'], 404);
            }

            $product->delete();

            return response()->json(['message' => 'Produto removido com sucesso']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 422);
        }
    }
}
