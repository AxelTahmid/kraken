# Laracom -> Laravel E-commerce RESTful API

Laracom is a API starter template created for robust ecommerce performance.

## Project Features & Progress

-   OAuth2 Server
-   Role Based Access Control ( RBAC )

## Installation Steps:

1. PHP >= 8.0.2 & Composer >=2 installed in your environment.
    ```sh
     composer install
    ```
2. Copy .env.example to .env and set your DB variables.
    ```sh
     php artisan migrate
    ```
3. Configure Passport. Use the keys given in terminal after command to set .env values
    ```sh
    php artisan passport:install
    ```
4. Serve your application
    ```sh
    php artisan serve
    ```
