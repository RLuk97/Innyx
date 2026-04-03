<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Listar produtos (Sincronizado com o seu Composable Vue)
    public function index(Request $request)
    {
        $search = $request->query('search');

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
        })
        ->orderBy('created_at', 'desc') // Mostrar os novos primeiro
        ->paginate(6); 

        return response()->json($products);
    }

    // Criar um novo produto (Ajustado para o seu Modal de Vidro)
    public function store(Request $request)
    {
        // 1. Validação (Simplificada para bater com o seu productForm do Vue)
        $validatedData = $request->validate([
            'name'        => 'required|max:50',
            'description' => 'required|max:200',
            'price'       => 'required|numeric|min:0.01',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // 2. Lógica para upload de imagem
        if ($request->hasFile('image')) {
            // Salva em storage/app/public/products
            $path = $request->file('image')->store('products', 'public');
            // Salva o link acessível no banco
            $validatedData['image'] = asset('storage/' . $path);
        }

        // 3. Persistência no banco de dados
        $product = Product::create($validatedData);

        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validatedData = $request->validate([
            'name'        => 'required|max:50',
            'description' => 'required|max:200',
            'price'       => 'required|numeric|min:0.01',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {
            // Remove a imagem antiga do disco se não for o placeholder
            if ($product->image && !str_contains($product->image, 'placeholder')) {
                $oldPath = str_replace(asset('storage/'), '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('image')->store('products', 'public');
            $validatedData['image'] = asset('storage/' . $path);
        }

        $product->update($validatedData);

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        if ($product->image) {
            $oldPath = str_replace(asset('storage/'), '', $product->image);
            Storage::disk('public')->delete($oldPath);
        }
        
        $product->delete();

        return response()->json(['message' => 'Produto excluído com sucesso']);
    }
}