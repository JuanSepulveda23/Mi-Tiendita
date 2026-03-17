<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpirationAlert extends Model
{
    protected $fillable = [
        'product_id',
        'days_remaining',
        'risk_level',
        'stock_at_alert',
        'avg_daily_sales',
        'rotation_days',
        'will_expire',
        'dismissed_at',
    ];

    protected $casts = [
        'will_expire' => 'boolean',
        'dismissed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Marcar alerta como descartada.
     */
    public function dismiss(): void
    {
        $this->update(['dismissed_at' => now()]);
    }

    /**
     * ¿Está descartada?
     */
    public function isDismissed(): bool
    {
        return $this->dismissed_at !== null;
    }

    /**
     * Scope: solo alertas activas (no descartadas).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('dismissed_at');
    }

    /**
     * Scope: solo alertas urgentes.
     */
    public function scopeUrgent($query)
    {
        return $query->where('risk_level', 'urgent');
    }

    /**
     * Scope: solo alertas de alto riesgo.
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high');
    }

    /**
     * Scope: productos que se vencerán antes de venderse.
     */
    public function scopeWillExpire($query)
    {
        return $query->where('will_expire', true);
    }
}
