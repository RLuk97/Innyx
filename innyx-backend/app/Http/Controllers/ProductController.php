<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Listar todos os produtos com paginação e busca (Requisito 2.22, 2.24, 2.25)
    public function index(Request $request)
    {
        // 1. Pegamos o termo de busca que vem do Front-end
        $search = $request->query('search');

        // 2. Fazemos a query: Se houver busca, filtra por nome ou descrição
        $products = \App\Models\Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
        })
        ->paginate(6); // 3. Pagina de 10 em 10 (Requisito 30)

        return response()->json($products);
    }

    // Criar um novo produto (Requisito 2.6, 2.12 a 2.18)
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:50',
            'description' => 'required|max:200',
            'price' => 'required|numeric|min:0.01', // Valor positivo
            'expiration_date' => 'required|date|after_or_equal:today', // Não anterior à data atual
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Lógica para upload de imagem com nome único
        if ($request->hasFile('image')) {
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $path = $request->file('image')->storeAs('products', $fileName, 'public');
            $validatedData['image'] = $path;
        }

        $product = Product::create($validatedData);

        return response()->json($product, 201);
    }
    // Visualizar um produto específico (Requisito 6)
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    // Editar um produto (Requisito 23)
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|max:50',
            'description' => 'required|max:200',
            'price' => 'required|numeric|min:0.01',
            'expiration_date' => 'required|date|after_or_equal:today',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('image')) {
            // Deleta a imagem antiga se existir
            Storage::disk('public')->delete($product->image);
            
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $path = $request->file('image')->storeAs('products', $fileName, 'public');
            $validatedData['image'] = $path;
        }

        $product->update($validatedData);

        return response()->json($product);
    }

    // Excluir um produto (Requisito 23)
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        Storage::disk('public')->delete($product->image);
        $product->delete();

        return response()->json(['message' => 'Produto excluído com sucesso']);
    }
}