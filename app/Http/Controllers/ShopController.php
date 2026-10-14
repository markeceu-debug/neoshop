<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Home do marketplace.
     * - Visitante sem município → vê todos os produtos + aviso
     * - Cliente com município → vê só produtos que entregam na região
     * - Cliente com município mas sem produtos locais → vê todos + aviso amarelo
     */
    public function home()
    {
        $customerMunicipality = app(\App\Services\CartService::class)->customerMunicipality();

        // ---------- QUERY BASE ----------
        $query = Product::query()
            ->where('status', 'active')
            ->with(['variants.items', 'coverImage', 'categoryRelation', 'images']);

        // Filtro por município (só se tiver)
        if (! empty($customerMunicipality)) {
            $query->whereHas('user', function ($q) use ($customerMunicipality) {
                $q->where('municipality', $customerMunicipality);
            });
        }

        $products = (clone $query)->latest()->paginate(12);

        // ---------- FALLBACK ----------
        // Se filtrou por município e não achou nada, mostra todos com aviso
        $noProductsInMunicipality = false;

        if (! empty($customerMunicipality) && $products->total() === 0) {
            $noProductsInMunicipality = true;

            $products = Product::query()
                ->where('status', 'active')
                ->with(['variants.items', 'coverImage', 'categoryRelation', 'images'])
                ->latest()
                ->paginate(12);
        }

        return view('shop.listagem', [
            'products'                  => $products,
            'categories'                => Category::where('active', true)->orderBy('position')->get(),
            'total'                     => $products->total(),
            'filters'                   => [
                'q'         => null,
                'categoria' => null,
                'min'       => null,
                'max'       => null,
                'sort'      => 'relevance',
            ],
            'customerMunicipality'      => $customerMunicipality,
            'noProductsInMunicipality'  => $noProductsInMunicipality,
        ]);
    }

    /**
     * Listagem de produtos com filtros (busca, categoria, preço, sort).
     * Também filtra por município do cliente.
     */
    public function index(Request $request)
    {
        $customerMunicipality = app(\App\Services\CartService::class)->customerMunicipality();

        $query = Product::query()
            ->where('status', '!=', 'inactive')
            ->with(['variants.items', 'coverImage', 'categoryRelation']);

        // ---------- Filtro por município ----------
        if (! empty($customerMunicipality)) {
            $query->whereHas('user', function ($q) use ($customerMunicipality) {
                $q->where('municipality', $customerMunicipality);
            });
        }

        // ---------- Busca ----------
        if ($search = $request->input('q')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // ---------- Categoria ----------
        if ($categorySlug = $request->input('categoria')) {
            $query->whereHas('categoryRelation', fn ($q) => $q->where('slug', $categorySlug));
        }

        // ---------- Preço ----------
        if ($min = $request->input('min')) {
            $query->where('price', '>=', (float) $min);
        }
        if ($max = $request->input('max')) {
            $query->where('price', '<=', (float) $max);
        }

        // ---------- Ordenação ----------
        $sort = $request->input('sort', 'relevance');
        match ($sort) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'rating'     => $query->orderByDesc('sold'),
            'newest'     => $query->orderByDesc('created_at'),
            default      => $query->orderByDesc('sold'),
        };

        $products = $query->paginate(12)->withQueryString();

        // ---------- FALLBACK ----------
        $noProductsInMunicipality = false;

        if (! empty($customerMunicipality) && $products->total() === 0 && ! $request->filled('q') && ! $request->filled('categoria')) {
            $noProductsInMunicipality = true;

            // Mostra todos sem o filtro de município
            $products = Product::query()
                ->where('status', '!=', 'inactive')
                ->with(['variants.items', 'coverImage', 'categoryRelation'])
                ->orderByDesc('sold')
                ->paginate(12);
        }

        return view('shop.listagem', [
            'products'                  => $products,
            'categories'                => Category::where('active', true)->orderBy('position')->get(),
            'total'                     => $products->total(),
            'filters'                   => [
                'q'         => $request->input('q'),
                'categoria' => $request->input('categoria'),
                'min'       => $request->input('min'),
                'max'       => $request->input('max'),
                'sort'      => $sort,
            ],
            'customerMunicipality'      => $customerMunicipality,
            'noProductsInMunicipality'  => $noProductsInMunicipality,
        ]);
    }

    /**
     * Detalhes de um produto.
     * Bloqueia se o vendedor for de outro município.
     */
    public function show(Product $product)
    {
        if ($product->status === 'inactive') abort(404);

        // ---------- Bloqueio por município ----------
        $customerMunicipality = app(\App\Services\CartService::class)->customerMunicipality();
        $seller = $product->user;

        if (
            ! empty($customerMunicipality) &&
            $seller &&
            $seller->municipality &&
            $seller->municipality !== $customerMunicipality
        ) {
            return redirect()
                ->route('home')
                ->with('error', "Este produto é vendido em {$seller->municipality} e você está em {$customerMunicipality}. Não entregamos fora do seu município.");
        }

        $product->load(['variants.items', 'images', 'coverImage', 'categoryRelation', 'user']);

        $related = Product::where('status', '!=', 'inactive')
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->when(! empty($customerMunicipality), function ($q) use ($customerMunicipality) {
                $q->whereHas('user', fn ($qq) => $qq->where('municipality', $customerMunicipality));
            })
            ->with(['variants.items', 'coverImage'])
            ->take(6)
            ->get();

        return view('shop.detalhes', [
            'product'              => $product,
            'related'              => $related,
            'customerMunicipality' => $customerMunicipality,
        ]);
    }

    /**
     * Listagem por categoria.
     */
    public function category(Category $category)
    {
        return $this->index(request()->merge(['categoria' => $category->slug]));
    }
}