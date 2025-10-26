# API Guide

This document provides a comprehensive guide to the RESTful API of the Stock Management System.

## Base URL

The base URL for all API endpoints is:

```
http://localhost:8000/api
```

## Authentication

The API uses Laravel Sanctum for token-based authentication. To access protected endpoints, you must include an `Authorization` header with a Bearer token.

### 1. Register

Create a new user account.

-   **Endpoint:** `POST /register`
-   **Authentication:** Not required.
-   **Request Body:**

| Field                   | Type   | Description            | Validation                          |
| :---------------------- | :----- | :--------------------- | :---------------------------------- |
| `name`                  | string | User's name.           | Required, string, max 255.          |
| `email`                 | string | User's email address.  | Required, email, unique.            |
| `password`              | string | User's password.       | Required, string, min 8, confirmed. |
| `password_confirmation` | string | Password confirmation. | Required.                           |

-   **Example Request:**

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}'
```

-   **Success Response (201):**

```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "name": "John Doe",
            "email": "john.doe@example.com",
            "updated_at": "2025-10-26T12:00:00.000000Z",
            "created_at": "2025-10-26T12:00:00.000000Z",
            "id": 1
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx"
    }
}
```

### 2. Login

Authenticate and receive an API token.

-   **Endpoint:** `POST /login`
-   **Authentication:** Not required.
-   **Request Body:**

| Field      | Type   | Description           | Validation        |
| :--------- | :----- | :-------------------- | :---------------- |
| `email`    | string | User's email address. | Required, email.  |
| `password` | string | User's password.      | Required, string. |

-   **Example Request:**

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "password": "password123"
}'
```

-   **Success Response (200):**

```json
{
    "message": "User logged in successfully",
    "data": {
        "user": { ... },
        "token": "2|yyyyyyyyyyyyyyyyyyyyyyyy"
    }
}
```

### 3. Logout

Revoke the current user's API token.

-   **Endpoint:** `POST /logout`
-   **Authentication:** Required.
-   **Success Response (200):**

```json
{
    "message": "User logged out successfully"
}
```

### 4. Get User

Get the authenticated user's information.

-   **Endpoint:** `GET /user`
-   **Authentication:** Required.
-   **Success Response (200):**

```json
{
    "message": "User retrieved successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com",
        ...
    }
}
```

---

## Product Endpoints

Manage products in the inventory.

### 1. Get All Products

-   **Endpoint:** `GET /products`
-   **Authentication:** Required.
-   **Query Parameters:**

| Parameter  | Type    | Description                              |
| :--------- | :------ | :--------------------------------------- |
| `per_page` | integer | Number of items per page. Default: `10`. |
| `search`   | string  | Search by product `name` or `sku`.       |

-   **Success Response (200):**

```json
{
    "message": "Products retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "sku": "LP-001-01",
                "name": "Laptop Pro",
                "description": "A high-end laptop.",
                "current_stock": 100,
                "created_at": "...",
                "updated_at": "..."
            }
        ],
        ...
    }
}
```

### 2. Create a Product

-   **Endpoint:** `POST /products`
-   **Authentication:** Required.
-   **Request Body:**

| Field           | Type    | Description             | Validation                                                                  |
| :-------------- | :------ | :---------------------- | :-------------------------------------------------------------------------- |
| `sku`           | string  | Stock Keeping Unit.     | Required, unique, max 15, regex `^[A-Z0-9]{1,4}-[A-Z0-9]{1,4}-[0-9]{1,5}$`. |
| `name`          | string  | Product name.           | Required, string, max 255.                                                  |
| `description`   | string  | Product description.    | Nullable, string, max 5000.                                                 |
| `current_stock` | integer | Initial stock quantity. | Required, integer, min 0.                                                   |

-   **Success Response (201):**

```json
{
    "message": "Product created successfully",
    "data": { ... }
}
```

### 3. Get a Single Product

-   **Endpoint:** `GET /products/{product}`
-   **Authentication:** Required.
-   **Path Parameters:**

| Parameter | Type    | Description            |
| :-------- | :------ | :--------------------- |
| `product` | integer | The ID of the product. |

-   **Success Response (200):**

```json
{
    "message": "Product retrieved successfully",
    "data": { ... }
}
```

### 4. Update a Product

-   **Endpoint:** `PUT /products/{product}`
-   **Authentication:** Required.
-   **Request Body:**

| Field         | Type   | Description          | Validation                                                     |
| :------------ | :----- | :------------------- | :------------------------------------------------------------- |
| `sku`         | string | Stock Keeping Unit.  | Required, unique (ignored for current product), max 15, regex. |
| `name`        | string | Product name.        | Required, string, max 255.                                     |
| `description` | string | Product description. | Nullable, string, max 5000.                                    |

-   **Success Response (200):**

```json
{
    "message": "Product updated successfully",
    "data": { ... }
}
```

### 5. Delete a Product

-   **Endpoint:** `DELETE /products/{product}`
-   **Authentication:** Required.
-   **Success Response (200):**

```json
{
    "message": "Product deleted successfully"
}
```

---

## Stock Movement Endpoints

Manage stock levels and history.

### 1. Get All Stock Movements

-   **Endpoint:** `GET /stock-movements`
-   **Authentication:** Required.
-   **Query Parameters:**

| Parameter  | Type    | Description                                   |
| :--------- | :------ | :-------------------------------------------- |
| `per_page` | integer | Number of items per page. Default: `15`.      |
| `search`   | string  | Search by product name/SKU or movement notes. |
| `type`     | string  | Filter by movement type (`in` or `out`).      |

-   **Success Response (200):**

```json
{
    "message": "Stock movements retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "product_id": 1,
                "user_id": 1,
                "type": "in",
                "quantity": 10,
                "notes": "Initial stock.",
                "product": { ... },
                "user": { ... }
            }
        ],
        ...
    }
}
```

### 2. Create a Stock Movement

This endpoint is used to add or reduce stock for a product.

-   **Endpoint:** `POST /stock-movements`
-   **Authentication:** Required.
-   **Request Body:**

| Field        | Type    | Description             | Validation                            |
| :----------- | :------ | :---------------------- | :------------------------------------ |
| `product_id` | integer | The ID of the product.  | Required, exists in `products` table. |
| `type`       | string  | Movement type.          | Required, `in` or `out`.              |
| `quantity`   | integer | Quantity to add/reduce. | Required, integer, min 1.             |
| `notes`      | string  | Additional notes.       | Nullable, string, max 1000.           |

-   **Success Response (201):**

```json
{
    "message": "Stock added/reduced successfully",
    "data": {
        "product": { ... },
        "new_stock": 110
    }
}
```

-   **Error Response (422 - Insufficient Stock):**

If `type` is `out` and `quantity` exceeds `current_stock`.

```json
{
    "message": "Validation Error",
    "errors": {
        "quantity": [
            "Insufficient stock. Current stock is 100 units, but you're trying to reduce by 120 units."
        ]
    }
}
```

### 3. Get a Single Stock Movement

-   **Endpoint:** `GET /stock-movements/{stock_movement}`
-   **Authentication:** Required.
-   **Success Response (200):**

```json
{
    "message": "Stock movement retrieved successfully",
    "data": { ... }
}
```

### 4. Get Stock Movements by Product

-   **Endpoint:** `GET /products/{product}/stock-movements`
-   **Authentication:** Required.
-   **Query Parameters:**

| Parameter  | Type    | Description                              |
| :--------- | :------ | :--------------------------------------- |
| `per_page` | integer | Number of items per page. Default: `15`. |

-   **Success Response (200):**

```json
{
    "message": "Stock movements for product retrieved successfully",
    "data": {
        "product": { ... },
        "movements": { ... }
    }
}
```

---

## Error Responses

-   **401 Unauthorized:** Invalid or missing authentication token.
-   **404 Not Found:** The requested resource does not exist.
-   **422 Unprocessable Entity:** The request body contains validation errors.
-   **500 Internal Server Error:** An unexpected server error occurred.
