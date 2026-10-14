<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'province', 'municipality',
        'standard_fee', 'standard_hours',
        'express_fee', 'express_hours',
        'pickup_enabled', 'pickup_address',
        'free_above',
        'active', 'notes',
         'max_fee',

    ];

    protected $casts = [
        'standard_fee'   => 'decimal:2',
        'express_fee'    => 'decimal:2',
        'free_above'     => 'decimal:2',
        'pickup_enabled' => 'boolean',
        'active'         => 'boolean',
        'standard_hours' => 'integer',
        'max_fee' => 'decimal:2',
        'express_hours'  => 'integer',
    ];

    /* ============================================================
     * ESCOPOS
     * ============================================================ */

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */

    /**
     * Busca a zona ativa para um município.
     */
    public static function forMunicipality(string $province, string $municipality): ?self
    {
        return static::query()
            ->where('province', $province)
            ->where('municipality', $municipality)
            ->where('active', true)
            ->first();
    }

    /**
     * Calcula o frete para um subtotal dado.
     */
    public function feeFor(string $method, float $subtotal): float
    {
        // Retirada é sempre grátis
        if ($method === 'pickup') return 0.0;

        // Frete grátis?
        if ($this->free_above !== null && $subtotal >= (float) $this->free_above) {
            return 0.0;
        }

        return match ($method) {
            'standard' => (float) $this->standard_fee,
            'express'  => (float) $this->express_fee,
            default    => 0.0,
        };
    }

    /**
     * Retorna todas as opções disponíveis para o cliente.
     */
    public function optionsFor(float $subtotal): array
    {
        $options = [];

        // ---------- Padrão ----------
        $standardFee = $this->feeFor('standard', $subtotal);
        $options[] = [
            'code'         => 'standard',
            'name'         => 'Motoboy Padrão',
            'description'  => 'Entrega em até ' . $this->standard_hours . ' horas',
            'hours'        => $this->standard_hours,
            'fee'          => $standardFee,
            'is_free'      => $standardFee <= 0,
        ];

        // ---------- Expresso ----------
        if ($this->express_fee > 0) {
            $expressFee = $this->feeFor('express', $subtotal);
            $options[] = [
                'code'         => 'express',
                'name'         => 'Motoboy Expresso',
                'description'  => 'Entrega em até ' . $this->express_hours . ' horas',
                'hours'        => $this->express_hours,
                'fee'          => $expressFee,
                'is_free'      => $expressFee <= 0,
            ];
        }

        // ---------- Retirada ----------
        if ($this->pickup_enabled) {
            $options[] = [
                'code'         => 'pickup',
                'name'         => 'Retirada com o vendedor',
                'description'  => $this->pickup_address ?: 'Combine com o vendedor',
                'hours'        => 0,
                'fee'          => 0,
                'is_free'      => true,
            ];
        }

        return $options;
    }

    /**
     * Label completo: "Luanda · Belas"
     */
    public function getFullLocationAttribute(): string
    {
        return "{$this->province} · {$this->municipality}";
    }
}