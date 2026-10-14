<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images'   => ['required', 'array'],
            'images.*' => ['image', 'max:4096'], // 4MB cada
        ]);

        $pos = $product->images()->max('position') ?? 0;

        foreach ($request->file('images') as $file) {
            $path = $file->store('products', 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'position'   => ++$pos,
                'is_cover'   => !$product->images()->exists(), // primeira = capa
            ]);
        }

        return back()->with('success', count($request->file('images')) . ' imagem(ns) adicionada(s)!');
    }

    public function cover(ProductImage $image)
    {
        ProductImage::where('product_id', $image->product_id)->update(['is_cover' => false]);
        $image->update(['is_cover' => true]);

        return back()->with('success', 'Capa atualizada!');
    }

    public function destroy(ProductImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Imagem removida!');
    }
}