<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'current_stock',
    ];

    protected $casts = [
        'current_stock' => 'integer',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
