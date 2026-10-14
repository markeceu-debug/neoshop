<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use App\Models\VariantItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VariantItemController extends Controller
{
   public function __construct(private StockService $stock) {}

public function store(Request $request, Variant $variant)
{
    $data = $this->validated($request);
    $stockInput = (int) ($data['stock'] ?? 0);

    unset($data['stock']);

    $data['variant_id'] = $variant->id;
    $data['active']     = $request->boolean('active', true);
    $data['position']   = $data['position'] ?? 0;
    $data['stock']      = 0;

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('variants', 'public');
    }

    $item = VariantItem::create($data);

    if ($stockInput > 0) {
        $this->stock->increment($item, $stockInput, 'estoque inicial da variante');
    }

    return back()->with('success', 'Item adicionado!');
}

public function update(Request $request, VariantItem $item)
{
    $data = $this->validated($request);
    $stockInput = $data['stock'] ?? null;
    unset($data['stock']);

    if ($request->hasFile('image')) {
        if ($item->image) Storage::disk('public')->delete($item->image);
        $data['image'] = $request->file('image')->store('variants', 'public');
    }

    $data['active']   = $request->boolean('active', true);
    $data['position'] = $data['position'] ?? 0;

    $item->update($data);

    if (!is_null($stockInput)) {
        $this->stock->setAbsolute($item, (int) $stockInput, 'edição da variante');
    }

    return back()->with('success', 'Item atualizado!');
}

public function destroy(VariantItem $item)
{
    if ($item->image) Storage::disk('public')->delete($item->image);
    $item->delete();

    return back()->with('success', 'Item removido!');
}
    private function validated(Request $request): array
    {
        return $request->validate([
            'value'     => ['required', 'string', 'max:100'],
            'color_hex' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'price'     => ['nullable', 'numeric', 'min:0'],
            'stock'     => ['nullable', 'integer', 'min:0'],
            'position'  => ['nullable', 'integer', 'min:0'],
            'image'     => ['nullable', 'image', 'max:2048'],
        ]);
    }
}