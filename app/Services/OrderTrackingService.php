<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTrackingEvent;
use Illuminate\Support\Facades\Auth;

class OrderTrackingService
{
    /**
     * Mapa de status → título, ícone e descrição padrão.
     */
    public const STATUS_MAP = [
        'awaiting_payment' => [
            'title'  => 'Pedido aguardando pagamento',
            'icon'   => 'hourglass-split',
            'desc'   => 'Estamos aguardando o envio do comprovativo.',
        ],
        'payment_under_review' => [
            'title'  => 'Comprovativo em análise',
            'icon'   => 'file-earmark-check',
            'desc'   => 'Recebemos seu comprovativo. O admin vai verificar.',
        ],
        'payment_rejected' => [
            'title'  => 'Comprovativo rejeitado',
            'icon'   => 'x-circle-fill',
            'desc'   => 'O comprovativo foi rejeitado. Envie um novo.',
        ],
        'paid' => [
            'title'  => 'Pagamento aprovado',
            'icon'   => 'check-circle-fill',
            'desc'   => 'Seu pagamento foi confirmado. O vendedor vai preparar o produto.',
        ],
        'preparing' => [
            'title'  => 'Preparando seu pedido',
            'icon'   => 'box-seam',
            'desc'   => 'O vendedor está preparando seu pedido.',
        ],
        'shipped' => [
            'title'  => 'Saiu para entrega',
            'icon'   => 'truck',
            'desc'   => 'Seu pedido saiu para entrega. Fique atento!',
        ],
        'delivered' => [
            'title'  => 'Entregue',
            'icon'   => 'house-door-fill',
            'desc'   => 'Seu pedido foi entregue. Confirme o recebimento.',
        ],
        'completed' => [
            'title'  => 'Pedido concluído',
            'icon'   => 'trophy-fill',
            'desc'   => 'Tudo certo! Obrigado pela compra.',
        ],
        'canceled' => [
            'title'  => 'Pedido cancelado',
            'icon'   => 'x-circle',
            'desc'   => 'O pedido foi cancelado.',
        ],
        'refunded' => [
            'title'  => 'Reembolso realizado',
            'icon'   => 'arrow-counterclockwise',
            'desc'   => 'O valor foi devolvido para você.',
        ],
        'disputed' => [
            'title'  => 'Disputa aberta',
            'icon'   => 'exclamation-triangle-fill',
            'desc'   => 'O cliente abriu uma disputa. Aguardando análise.',
        ],
    ];

    /**
     * Registra um evento de rastreio.
     */
    public function log(
        Order $order,
        string $status,
        ?string $title = null,
        ?string $description = null,
        ?string $location = null,
        ?string $icon = null,
        ?int $createdBy = null
    ): OrderTrackingEvent {
        $map = self::STATUS_MAP[$status] ?? [
            'title'  => 'Atualização',
            'icon'   => 'info-circle',
            'desc'   => null,
        ];

        return OrderTrackingEvent::create([
            'order_id'    => $order->id,
            'status'      => $status,
            'title'       => $title ?? $map['title'],
            'description' => $description ?? $map['desc'],
            'location'    => $location,
            'icon'        => $icon ?? $map['icon'],
            'created_by'  => $createdBy ?? Auth::id(),
        ]);
    }
}