<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index(Product $product)
    {
        $product->load('variants.items');
        return view('admin.products.variants', compact('product'));
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'type'     => ['required', 'in:padrao,nao_padrao'],
            'required' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['product_id'] = $product->id;
        $data['required']   = $request->boolean('required');
        $data['position']   = $data['position'] ?? 0;

        Variant::create($data);

        return back()->with('success', 'Variante adicionada!');
    }

    public function update(Request $request, Variant $variant)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'type'     => ['required', 'in:padrao,nao_padrao'],
            'required' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['required'] = $request->boolean('required');
        $data['position'] = $data['position'] ?? 0;

        $variant->update($data);

        return back()->with('success', 'Variante atualizada!');
    }

    public function destroy(Variant $variant)
    {
        $variant->delete();
        return back()->with('success', 'Variante removida!');
    }
}