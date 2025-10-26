# Introduction

This document provides a detailed overview of the Stock Management System, a comprehensive solution for managing inventory. The system is built on the Laravel framework and includes both a web-based Admin Panel and a RESTful API.

## Purpose

The primary purpose of this project is to offer a robust and scalable platform for tracking product inventory. It allows for efficient management of products, monitoring stock levels, and maintaining a complete history of all stock movements. The dual interface (Admin Panel and API) ensures that the system can be used both manually by administrators and programmatically by other applications.

## Architecture

The system follows a standard Laravel architecture, with a clear separation of concerns:

-   **Models:** Define the database schema and relationships (`Product`, `StockMovement`, `User`).
-   **Controllers:** Handle incoming HTTP requests. A distinction is made between controllers for the web interface (`app/Http/Controllers`) and for the API (`app/Http/Controllers/Api`).
-   **Requests:** Manage validation for incoming data, ensuring data integrity.
-   **Services:** Encapsulate core business logic, such as the `StockService` which handles all stock manipulations.
-   **Views:** Blade templates are used for the Admin Panel, providing a user-friendly interface.
-   **Routes:** Separate route files (`web.php` and `api.php`) are used to define the application's endpoints.
-   **Authentication:** Laravel Sanctum is used for API authentication, providing a secure and straightforward token-based system.
