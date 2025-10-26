# Admin Panel Guide

This guide provides a detailed explanation of the features available in the web-based Admin Panel.

## Dashboard

After logging in, you are redirected to the dashboard, which serves as the main entry point to the Admin Panel's features.

## Product Management

Product management is handled by the `ProductController` and its associated views in `resources/views/admin/product`.

### Product List
-   **Route:** `GET /products`
-   **View:** `resources/views/admin/product/index.blade.php`
-   **Description:** Displays a paginated list of all products, with options to search by name or SKU. Each product in the list has links to edit or delete it.

### Create Product
-   **Route:** `GET /products/create` (display form), `POST /products` (store data)
-   **View:** `resources/views/admin/product/create.blade.php`
-   **Description:** A form to add a new product to the system. The form includes fields for SKU, name, description, and initial stock quantity.

### Edit Product
-   **Route:** `GET /products/{product}/edit` (display form), `PUT/PATCH /products/{product}` (update data)
-   **View:** `resources/views/admin/product/edit.blade.php`
-   **Description:** A form to update the details of an existing product. The SKU, name, and description can be modified.

### Delete Product
-   **Route:** `DELETE /products/{product}`
-   **Description:** Removes a product from the system. This action is typically triggered from the product list.

## Stock Management

Stock management is handled by the `StockMovementController` and its views in `resources/views/admin/stock`.

### Stock Movement History
-   **Route:** `GET /stock-management`
-   **View:** `resources/views/admin/stock/index.blade.php`
-   **Description:** Displays a paginated history of all stock movements (both `in` and `out`). The list can be filtered by movement type and searched by product name/SKU.

### Add/Reduce Stock
-   **Route:** `GET /stock-management/create` (display form), `POST /stock-management` (store data)
-   **View:** `resources/views/admin/stock/create.blade.php`
-   **Description:** A form to create a new stock movement. You can select a product, choose the movement type (`in` or `out`), specify the quantity, and add notes. This is the primary interface for manually adjusting stock levels.
