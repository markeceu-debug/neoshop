<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CouponsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index(CouponsDataTable $dataTable)
    {
        return $dataTable->render('admin.coupons.index');
    }

    public function create()
    {
        return view('admin.coupons.create', [
            'categories' => Category::orderBy('name')->get(),
            'products'   => Product::orderBy('name')->limit(200)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = strtoupper($data['code']);

        Coupon::create($data);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Cupom criado com sucesso!');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', [
            'coupon'     => $coupon,
            'categories' => Category::orderBy('name')->get(),
            'products'   => Product::orderBy('name')->limit(200)->get(),
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validated($request, $coupon->id);
        $data['code'] = strtoupper($data['code']);

        $coupon->update($data);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Cupom atualizado!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Cupom excluído!');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code'            => ['required', 'string', 'max:50', 'unique:coupons,code,' . $ignoreId],
            'name'            => ['nullable', 'string', 'max:100'],
            'description'     => ['nullable', 'string'],
            'type'            => ['required', 'in:percent,fixed,free_shipping'],
            'value'           => ['required', 'numeric', 'min:0'],
            'min_purchase'    => ['nullable', 'numeric', 'min:0'],
            'max_discount'    => ['nullable', 'numeric', 'min:0'],
            'usage_limit'     => ['nullable', 'integer', 'min:1'],
            'per_user_limit'  => ['nullable', 'integer', 'min:1'],
            'applies_to'      => ['required', 'in:all,products,categories'],
            'product_ids'     => ['nullable', 'array'],
            'product_ids.*'   => ['integer', 'exists:products,id'],
            'category_ids'    => ['nullable', 'array'],
            'category_ids.*'  => ['integer', 'exists:categories,id'],
            'starts_at'       => ['nullable', 'date'],
            'expires_at'      => ['nullable', 'date', 'after_or_equal:starts_at'],
            'active'          => ['nullable', 'boolean'],
        ]);
    }
}