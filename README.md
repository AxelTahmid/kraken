# Laravel API Starter Boilerplate.

Laracom is a Laravel API starter template created as an experiment. feel free to fork or use however you want.


## Project Features & Progress
---

-   OAuth2 using Laravel Passport
-   Role Based Access Control ( RBAC ) without any package.
    - User can have multiple Roles & Permissions.
    - User can have Permissions without Role.
    - User can have Permissions through Role.
    - Permissions can be grouped with Role.


## Getting Started:
---

1. `PHP >= 8.0.2` & `Composer >=2` installed in your environment.
    ```sh
     composer install
    ```
2. Copy `.env.example` to `.env` and set your DB variables, then migrate.
    ```sh
     php artisan key:generate
     php artisan migrate --seed
    ```
3. Seeder will create following admin credentials with Role & Permission CRUD
    ```sh
     super@tahmid.com
     password
    ```
4. Configure Passport. Use the keys given in terminal after command to set .env values
    ```sh
    php artisan passport:install
    ```
5. Serve your application
    ```sh
    php artisan serve
    ```

## API Documentation:
---

