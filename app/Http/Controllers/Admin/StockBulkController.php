<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockBulkController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'products'   => ['required', 'array', 'min:1'],
            'products.*' => ['integer', 'exists:products,id'],
            'mode'       => ['required', 'in:increment,decrement,set'],
            'target'     => ['required', 'in:auto,products,variants,both'],
            'value'      => ['required', 'integer', 'min:0'],
            'reason'     => ['nullable', 'string', 'max:120'],
        ], [
            'products.required' => 'Selecione pelo menos um produto.',
        ]);

        if (in_array($data['mode'], ['increment', 'decrement']) && (int) $data['value'] === 0) {
            return back()->with('error', 'Informe uma quantidade maior que zero.');
        }

        $reason = $data['reason'] ?: 'ajuste em massa';
        $target = $data['target'] ?? 'auto';

        // ⬇️ MÉTODO NOVO — respeita has_variants
        $result = $this->stock->bulkAdjustTargets(
            $data['products'],
            $data['mode'],
            (int) $data['value'],
            $target,
            $reason
        );

        // ---------- Monta a mensagem de retorno ----------
        $parts = [];
        if ($result['breakdown']['products'] > 0) {
            $parts[] = $result['breakdown']['products'] . ' produto(s)';
        }
        if ($result['breakdown']['variants'] > 0) {
            $parts[] = $result['breakdown']['variants'] . ' variante(s)';
        }
        if (($result['breakdown']['skipped'] ?? 0) > 0) {
            $parts[] = $result['breakdown']['skipped'] . ' ignorado(s)';
        }

        $summary = implode(' e ', $parts) ?: 'nada';

        if ($result['success'] === 0) {
            return back()->with('error', 'Nenhum ajuste foi aplicado. ' . $this->summarizeFailures($result['failed']));
        }

        $msg = "Ajuste aplicado em {$summary}.";

        if (!empty($result['failed'])) {
            $msg .= ' ' . count($result['failed']) . ' falha(s): ' . $this->summarizeFailures($result['failed']);
            return back()
                ->with('success', $msg)
                ->with('error', 'Alguns itens não puderam ser ajustados.');
        }

        return back()->with('success', $msg);
    }

    private function summarizeFailures(array $failed): string
    {
        return collect($failed)
            ->take(3)
            ->map(fn ($f) => "{$f['id']}: {$f['reason']}")
            ->implode(' · ');
    }
}