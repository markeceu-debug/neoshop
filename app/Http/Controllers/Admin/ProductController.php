<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ProductsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
public function __construct(private StockService $stock) {}

public function index(ProductsDataTable $dataTable)
{
return $dataTable->render('admin.products.index');
}

public function create()
{
return view('admin.products.create');
}

public function store(Request $request)
{
$data = $this->validated($request);

DB::transaction(function () use ($data, $request) {
$data['slug'] = Str::slug($data['name']) . '-' . uniqid();
$data['user_id'] = auth()->id();
$data['stock'] = 0; // criamos com 0 e o service aplica o valor real

if ($request->hasFile('image')) {
$data['image'] = $request->file('image')->store('products', 'public');
}

$product = Product::create($data);

// Aplica o estoque inicial via service → gera StockMovement
if (($data['stock_input'] ?? 0) > 0) {
$this->stock->increment($product, (int) $data['stock_input'], 'estoque inicial');
}

return $product;
});

return redirect()->route('admin.products.index')->with('success', 'Produto cadastrado!');
}


public function edit(Product $product)
{
return view('admin.products.edit', compact('product'));
}

public function update(Request $request, Product $product)
{
$data = $this->validated($request, $product->id);

DB::transaction(function () use ($data, $request, $product) {
if ($request->hasFile('image')) {
if ($product->image) Storage::disk('public')->delete($product->image);
$data['image'] = $request->file('image')->store('products', 'public');
}

$product->update(collect($data)->except('stock_input')->toArray());

// Ajuste de estoque passa SEMPRE pelo service
if (isset($data['stock_input'])) {
$this->stock->setAbsolute($product, (int) $data['stock_input'], 'edição manual');
}
});

return redirect()->route('admin.products.index')->with('success', 'Produto atualizado!');
}

public function destroy(Product $product)
{
if ($product->image) {
Storage::disk('public')->delete($product->image);
}

$product->delete();

return response(['status' => 'success', 'message' => 'Excluido succeso!']);
}

/**
 * Validação compartilhada entre store/update.
 */
private function validated(Request $request, ?int $ignoreId = null): array
{
return $request->validate([
'name' => ['required', 'string', 'max:255'],
'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku,' . $ignoreId],
'description' => ['nullable', 'string'],
'price'   => ['required', 'numeric', 'min:0'],
'old_price'   => ['nullable', 'numeric', 'min:0'],
'stock_input'   => ['required', 'integer', 'min:0'],
'category_id' => ['required', 'exists:categories,id'],
'status'  => ['required', 'in:active,inactive,out_of_stock'],
'image'   => ['nullable', 'image', 'max:2048'],
]);
}
}
