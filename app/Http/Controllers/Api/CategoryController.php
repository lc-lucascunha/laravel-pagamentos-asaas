<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @var Category
     */
    private $category;

    public function __construct(
        Category $category
    )
    {
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
     * Exibir uma categoria específica
     */
    public function show($id)
    {
        try {

            $category = $this->category->withCount('products')->find($id);

            if (!$category) {
                return response()->json(['message' => 'Categoria não encontrada'], 404);
            }

            return response()->json($category);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 422);
        }
    }

    /**
     * Cadastrar categoria
     */
    public function store(Request $request)
    {
        try {

            $data = $this->validate($request, [
                'name' => 'required|string|max:255|unique:categories,name',
            ]);

            $category = $this->category->create($data);

            return response()->json($category, 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 422);
        }
    }

    /**
     * Atualizar uma categoria específica
     */
    public function update(Request $request, $id)
    {
        try {

            $category = $this->category->find($id);

            if (!$category) {
                return response()->json(['message' => 'Categoria não encontrada'], 404);
            }

            $data = $this->validate($request, [
                'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            ]);

            $category->update($data);

            return response()->json($category);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao processar requisição'], 422);
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
