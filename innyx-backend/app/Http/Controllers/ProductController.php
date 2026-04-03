<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    /**
     * Criar um novo produto (Ajustado para o seu Modal de Vidro)
     * Este é o método que estava faltando!
     */
    public function store(Request $request)
    {
        try {
            // 1. Validação dos campos enviados pelo Vue
            $validator = Validator::make($request->all(), [
                'name'        => 'required|max:50',
                'description' => 'required|max:200',
                'price'       => 'required|numeric|min:0.01',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();

            // 2. Lógica para upload de imagem
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                // Salva a URL absoluta no banco para facilitar a exibição no Front
                $validatedData['image'] = asset('storage/' . $path);
            }

            // 3. Persistência no Banco (MySQL)
            $product = Product::create($validatedData);

            return response()->json($product, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Falha interna: ' . $e->getMessage()], 500);
        }
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
            // Remove a imagem antiga do disco se existir
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