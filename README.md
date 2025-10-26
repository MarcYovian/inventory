# Stock Management System

A comprehensive Stock Management System built with Laravel, featuring a web-based Admin Panel and a RESTful API for seamless inventory control.

## Key Features

### Admin Panel

-   **Product Management:** CRUD functionality for products.
-   **Stock Control:** Easily add or reduce product stock.
-   **Movement History:** Track all stock movements.
-   **Search and Pagination:** Efficiently navigate through products and stock records.

### RESTful API

-   **Authentication:** Secure token-based authentication using Laravel Sanctum.
-   **Product Endpoints:** Full CRUD capabilities for products.
-   **Stock Movement Endpoints:** Programmatically manage stock levels and history.
-   **Validation:** Robust validation and error handling.

## Technology Stack

-   **Backend:** Laravel 12, PHP 8.2
-   **Database:** MySQL
-   **API Authentication:** Laravel Sanctum
-   **Frontend:** Blade, Tailwind CSS, Alpine.js
-   **Development:** Vite

## Prerequisites

-   PHP >= 8.2
-   Composer
-   Node.js & npm
-   MySQL

## Installation Instructions

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/MarcYovian/inventory.git
    cd inventory-management
    ```

2.  **Install dependencies:**

    ```bash
    composer install
    npm install
    ```

3.  **Environment setup:**

    -   Copy the example `.env` file:
        ```bash
        cp .env.example .env
        ```
    -   Generate an application key:
        ```bash
        php artisan key:generate
        ```
    -   Configure your database credentials in the `.env` file:
        ```
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=inventory
        DB_USERNAME=root
        DB_PASSWORD=
        ```

4.  **Run database migrations:**

    ```bash
    php artisan migrate
    ```

5.  **Build frontend assets:**

    ```bash
    npm run build
    ```

6.  **Run the development server:**
    ```bash
    php artisan serve
    ```

## Basic Usage

### Admin Panel

-   Access the admin panel by navigating to `http://localhost:8000`.
-   Register a new user or log in with existing credentials.
-   Manage products and stock through the web interface.

### API

-   The API base URL is `http://localhost:8000/api`.
-   Refer to the [API Guide](docs/api_guide.md) for detailed endpoint documentation.

## Project Structure

-   `app/Http/Controllers/`: Contains controllers for the Admin Panel.
-   `app/Http/Controllers/Api/`: Contains controllers for the API.
-   `app/Models/`: Defines the application's data models.
-   `app/Services/`: Houses core business logic like `StockService`.
-   `database/migrations/`: Contains the database schema definitions.
-   `resources/views/`: Holds the Blade templates for the Admin Panel.
-   `routes/web.php`: Defines routes for the Admin Panel.
-   `routes/api.php`: Defines routes for the API.
-   `docs/`: Contains detailed project documentation.

## Contribution Guidelines

Contributions are welcome! Please feel free to submit a pull request.

1.  Fork the repository.
2.  Create a new feature branch (`git checkout -b feature/your-feature`).
3.  Commit your changes (`git commit -m 'Add some feature'`).
4.  Push to the branch (`git push origin feature/your-feature`).
5.  Open a pull request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
