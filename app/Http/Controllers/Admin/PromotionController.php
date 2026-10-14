<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\PromotionsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(PromotionsDataTable $dataTable)
    {
        return $dataTable->render('admin.promotions.index');
    }

    public function create()
    {
        return view('admin.promotions.create', [
            'categories' => Category::orderBy('name')->get(),
            'products'   => Product::orderBy('name')->limit(200)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Promotion::create($data);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Promoção criada!');
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', [
            'promotion'  => $promotion,
            'categories' => Category::orderBy('name')->get(),
            'products'   => Product::orderBy('name')->limit(200)->get(),
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validated($request);

        $promotion->update($data);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Promoção atualizada!');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Promoção excluída!');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'type'           => ['required', 'in:percent,fixed,buy_x_get_y'],
            'value'          => ['required', 'numeric', 'min:0'],
            'applies_to'     => ['required', 'in:all,products,categories'],
            'product_ids'    => ['nullable', 'array'],
            'product_ids.*'  => ['integer', 'exists:products,id'],
            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'buy_qty'        => ['nullable', 'integer', 'min:1'],
            'get_qty'        => ['nullable', 'integer', 'min:1'],
            'starts_at'      => ['nullable', 'date'],
            'expires_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'priority'       => ['nullable', 'integer', 'min:0'],
            'active'         => ['nullable', 'boolean'],
        ]);
    }
}