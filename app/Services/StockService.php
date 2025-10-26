<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function addStock(Product $product, int $quantity, User $user, ?string $notes)
    {
        if ($quantity <= 0) {
            throw new Exception("Quantity must be greater than zero.");
        }

        DB::transaction(function () use ($product, $quantity, $user, $notes) {
            // Update product stock
            $product->increment('current_stock', $quantity);

            // Record stock movement
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'type' => 'in',
                'quantity' => $quantity,
                'notes' => $notes,
            ]);

            return $movement;
        });
    }

    public function reduceStock(Product $product, int $quantity, User $user, ?string $notes)
    {
        if ($quantity <= 0) {
            throw new Exception("Quantity must be greater than zero.");
        }

        if ($product->fresh()->current_stock < $quantity) {
            throw new Exception("Insufficient stock to reduce. Current stock: {$product->current_stock}, Requested reduction: {$quantity}");
        }

        DB::transaction(function () use ($product, $quantity, $user, $notes) {
            // Update product stock
            $product->decrement('current_stock', $quantity);

            // Record stock movement
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'type' => 'out',
                'quantity' => $quantity,
                'notes' => $notes,
            ]);

            return $movement;
        });
    }
}
