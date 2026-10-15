<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DisputeController extends Controller
{
    /**
     * Formulário pra abrir reclamação.
     */
    public function create(Order $order)
    {
        // Segurança
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Só se estiver entregue
        if ($order->status !== 'delivered') {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('error', 'Só é possível abrir reclamação após o recebimento.');
        }

        // Só se estiver dentro do prazo (P2 = Sim, prazo sempre conta)
        if (! $order->isDisputable()) {
            $days = (int) Setting::get('guarantee_days', 7);
            return redirect()
                ->route('user.orders.show', $order)
                ->with('error', "O prazo de {$days} dias para reclamação expirou.");
        }

        // P1 = B — bloqueia se já houver disputa ativa.
        if ($order->openDispute()) {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('error', 'Este pedido já possui uma reclamação em andamento. Aguarde a resolução.');
        }

        return view('user.disputes.create', compact('order'));
    }

    /**
     * Salva a reclamação.
     */
    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $order->isDisputable()) {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('error', 'Não é possível abrir reclamação neste pedido.');
        }

        $data = $request->validate([
            'reason'        => ['required', 'in:not_received,wrong_product,defective,damaged,not_as_described,other'],
            'description'   => ['required', 'string', 'min:20', 'max:2000'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'reason.required'      => 'Escolha o motivo da reclamação.',
            'description.required' => 'Descreva o problema.',
            'description.min'      => 'Descreva o problema com pelo menos 20 caracteres.',
        ]);

        // Foto obrigatória se for defeito/danificado/errado
        $requiresPhoto = in_array($data['reason'], ['defective', 'damaged', 'wrong_product', 'not_as_described']);

        if ($requiresPhoto && empty($request->file('attachments'))) {
            return back()->withErrors(['attachments' => 'Este motivo exige pelo menos 1 foto.'])->withInput();
        }

        try {
            $dispute = DB::transaction(function () use ($order, $data, $request) {
                // P1 = B — revalida dentro da transação com lock
                // (o índice único do banco é o backup final contra race condition)
                $disputaAtiva = Dispute::where('order_id', $order->id)
                    ->whereIn('status', ['open', 'under_review'])
                    ->lockForUpdate()
                    ->exists();

                if ($disputaAtiva) {
                    throw new RuntimeException('DUPLICATE_ACTIVE_DISPUTE');
                }

                // Upload de anexos
                $attachments = [];
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $attachments[] = $file->store('disputes/' . $order->id, 'public');
                    }
                }

                // Cria a disputa
                $dispute = Dispute::create([
                    'order_id'    => $order->id,
                    'opened_by'   => Auth::id(),
                    'reason'      => $data['reason'],
                    'description' => $data['description'],
                    'attachments' => $attachments,
                    'status'      => 'open',
                ]);

                // Mensagem inicial (do cliente)
                DisputeMessage::create([
                    'dispute_id'  => $dispute->id,
                    'sender_id'   => Auth::id(),
                    'sender_role' => 'buyer',
                    'message'     => $data['description'],
                    'attachments' => $attachments,
                ]);

                // Mensagem automática do sistema
                DisputeMessage::create([
                    'dispute_id'  => $dispute->id,
                    'sender_id'   => null,
                    'sender_role' => 'system',
                    'message'     => 'Reclamação registrada. Um administrador irá analisar em breve. O prazo de garantia foi pausado.',
                ]);

                // Pausa a liberação do pedido
                $order->update([
                    'status'          => 'disputed',
                    'auto_release_at' => null,
                ]);

                return $dispute;
            });
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'DUPLICATE_ACTIVE_DISPUTE') {
                return redirect()
                    ->route('user.orders.show', $order)
                    ->with('error', 'Este pedido já possui uma reclamação em andamento. Aguarde a resolução.');
            }
            throw $e;
        } catch (QueryException $e) {
            // Backup final: constraint do banco barrou (race condition extrema)
            if (str_contains($e->getMessage(), 'disputes_active_order_unique')) {
                return redirect()
                    ->route('user.orders.show', $order)
                    ->with('error', 'Este pedido já possui uma reclamação em andamento. Aguarde a resolução.');
            }
            throw $e;
        }

        return redirect()
            ->route('user.disputes.show', $dispute)
            ->with('success', 'Reclamação aberta! Aguarde o contato do admin.');
    }

    /**
     * Detalhe da reclamação + chat.
     */
    public function show(Dispute $dispute)
    {
        if ($dispute->opened_by !== Auth::id()) {
            abort(403);
        }

        $dispute->load(['order.items', 'messages.sender', 'resolver']);

        return view('user.disputes.show', compact('dispute'));
    }

    /**
     * Cliente envia mensagem no chat.
     */
    public function message(Request $request, Dispute $dispute)
    {
        if ($dispute->opened_by !== Auth::id()) {
            abort(403);
        }

        if ($dispute->isResolved()) {
            return back()->with('error', 'Esta reclamação já foi resolvida.');
        }

        $data = $request->validate([
            'message'       => ['required', 'string', 'min:2', 'max:2000'],
            'attachments'   => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('disputes/' . $dispute->order_id, 'public');
            }
        }

        DisputeMessage::create([
            'dispute_id'  => $dispute->id,
            'sender_id'   => Auth::id(),
            'sender_role' => 'buyer',
            'message'     => $data['message'],
            'attachments' => $attachments,
        ]);

        return back()->with('success', 'Mensagem enviada.');
    }
}