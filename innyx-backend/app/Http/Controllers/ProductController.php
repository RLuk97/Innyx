<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
        })
        ->with('category') // Carrega o relacionamento para o Front
        ->orderBy('created_at', 'desc')
        ->paginate(6); 

        return response()->json($products);
    }

    public function store(Request $request)
    {
        try {
            // 1. Validação com TODAS as regras do edital
            $validator = Validator::make($request->all(), [
                'name'            => 'required|max:50',
                'description'     => 'required|max:200',
                'price'           => 'required|numeric|min:0.01',
                'expiration_date' => 'required|date|after_or_equal:today',
                'category_id'     => 'required|exists:categories,id',
                'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();

            // 2. Upload de imagem com nome único [cite: 17]
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validatedData['image'] = asset('storage/' . $path);
            }

            // 3. Persistência (Lembrar de adicionar ao $fillable no Model)
            $product = Product::create($validatedData);

            return response()->json($product, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Falha interna: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'            => 'required|max:50',
            'description'     => 'required|max:200',
            'price'           => 'required|numeric|min:0.01',
            'expiration_date' => 'required|date|after_or_equal:today',
            'category_id'     => 'required|exists:categories,id',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('image')) {
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

    // Os métodos show e destroy permanecem os mesmos...
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
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