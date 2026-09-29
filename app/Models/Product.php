<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'quantity',
        'low_stock_threshold',
        'expiration_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'expiration_date' => 'date',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
