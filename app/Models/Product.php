<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'barcode',
        'purchase_price',
        'sale_price',
        'stock',
        'is_frequent',
        'min_stock',
        'category',
        'supplier',
        'sort_order',
        'expiration_date',
        'entry_date',
    ];

    protected $casts = [
        'is_frequent' => 'boolean',
        'expiration_date' => 'date',
        'entry_date' => 'date',
    ];

    /* ---------- Relationships ---------- */

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function expirationAlerts(): HasMany
    {
        return $this->hasMany(ExpirationAlert::class);
    }

    /* ---------- Scopes ---------- */

    public function scopeFrequent($query)
    {
        return $query->where('is_frequent', true)->orderBy('sort_order');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<', 'min_stock');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /* ---------- Helpers ---------- */

    public function isLowStock(): bool
    {
        return $this->stock < $this->min_stock;
    }

    public function profit(): float
    {
        return $this->sale_price - $this->purchase_price;
    }

    /**
     * Días restantes hasta vencimiento.
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->expiration_date) return null;
        
        return \Carbon\Carbon::today()->diffInDays($this->expiration_date, false);
    }

    /**
     * ¿Está próximo a vencer?
     */
    public function isExpiringSoon(): bool
    {
        $days = $this->daysUntilExpiration();
        return $days !== null && $days <= 3;
    }

    /**
     * ¿Está urgente?
     */
    public function isExpiringUrgent(): bool
    {
        $days = $this->daysUntilExpiration();
        return $days !== null && $days <= 1;
    }
}
