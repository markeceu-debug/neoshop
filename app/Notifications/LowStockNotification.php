<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\VariantItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    /**
     * @param  Product|VariantItem  $stockable
     * @param  int                  $threshold
     */
    public function __construct(
        public mixed $stockable,
        public int $threshold = 10,
    ) {}

    /**
     * Canais de entrega.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Dados salvos na tabela `notifications`.
     */
    public function toArray(object $notifiable): array
    {
        $isVariant = $this->stockable instanceof VariantItem;

        return [
            'type'        => 'low_stock',
            'severity'    => $this->stockable->stock === 0 ? 'critical' : 'warning',
            'title'       => $this->stockable->stock === 0
                                ? 'Produto esgotado'
                                : 'Estoque baixo',
            'message'     => $this->buildMessage(),
            'stockable'   => [
                'class' => get_class($this->stockable),
                'id'    => $this->stockable->id,
            ],
            'product_id'  => $isVariant
                                ? $this->stockable->variant->product_id
                                : $this->stockable->id,
            'product_name'=> $isVariant
                                ? $this->stockable->variant->product->name
                                : $this->stockable->name,
            'variant'     => $isVariant ? $this->stockable->variant->name : null,
            'item_value'  => $isVariant ? $this->stockable->value : null,
            'stock'       => $this->stockable->stock,
            'threshold'   => $this->threshold,
            'url'         => route('admin.products.variants.index', $isVariant
                                ? $this->stockable->variant->product_id
                                : $this->stockable->id),
        ];
    }

    /**
     * E-mail.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $severity = $this->stockable->stock === 0 ? 'CRÍTICO' : 'ATENÇÃO';

        return (new MailMessage)
            ->subject("[{$severity}] {$this->buildSubject()}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line($this->buildMessage())
            ->line("Estoque atual: **{$this->stockable->stock}** unidade(s).")
            ->line("Limite configurado: {$this->threshold} unidade(s).")
            ->action('Ver Produto', route('admin.products.variants.index',
                $this->stockable instanceof VariantItem
                    ? $this->stockable->variant->product_id
                    : $this->stockable->id))
            ->line('Recomendamos repor o estoque o quanto antes.');
    }

    private function buildMessage(): string
    {
        if ($this->stockable instanceof VariantItem) {
            $product = $this->stockable->variant->product->name;
            $variant = $this->stockable->variant->name;
            $value   = $this->stockable->value;

            return "O item **{$value}** (variante **{$variant}**) do produto **{$product}** está com apenas **{$this->stockable->stock}** unidade(s).";
        }

        return "O produto **{$this->stockable->name}** está com apenas **{$this->stockable->stock}** unidade(s).";
    }

    private function buildSubject(): string
    {
        return $this->stockable instanceof VariantItem
            ? "Estoque baixo: {$this->stockable->value} — {$this->stockable->variant->product->name}"
            : "Estoque baixo: {$this->stockable->name}";
    }
}