# Core Concepts

This document explains some of the core concepts and logic within the Stock Management System.

## StockService

The `StockService` class (`app/Services/StockService.php`) is the heart of the inventory management logic. It provides a centralized and safe way to handle all stock manipulations. By using this service, we ensure that every change in stock is properly recorded and that the data remains consistent.

### Key Responsibilities

1.  **Atomicity:** All stock operations (updating the product's stock and creating a movement record) are wrapped in a database transaction. This ensures that if any part of the operation fails, the entire transaction is rolled back, preventing data inconsistencies.

2.  **Validation:** The service includes checks to prevent invalid operations, such as reducing stock by a negative quantity or reducing stock below zero.

3.  **Logging:** For every stock change, a corresponding record is created in the `stock_movements` table. This provides a complete audit trail of all inventory changes.

### Methods

#### `addStock(Product $product, int $quantity, User $user, ?string $notes)`

-   Increments the `current_stock` of the given product.
-   Creates a new `StockMovement` record with `type = 'in'`.

#### `reduceStock(Product $product, int $quantity, User $user, ?string $notes)`

-   Decrements the `current_stock` of the given product.
-   Performs a check to ensure there is sufficient stock before proceeding.
-   Creates a new `StockMovement` record with `type = 'out'`.

By channeling all stock adjustments through this service, the application maintains a high level of data integrity and provides a reliable history of all inventory operations.
