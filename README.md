# KRAKEN - Laravel API Boilerplate with RBAC

Kraken is a API boilerplate created to take out the pain of developing a project from scratch. It includes the bare minimum you need to get started. I believe the less dependencies you have to manage, the less of a headache you will get to maintain the code long term. 

## What's Baked In?

-   Standardized API Response in JSON using Traits
-   OAuth2 implementation with Laravel Passport
-   Global Error Handling with Verbose messages.
-   Role Based Access Control ( RBAC ) without any package.
    - Permissions can be grouped with Role.
    - User can have Permissions without Role.
    - User can have Permissions through Role.


## Getting Started:


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

#### Auth Routes:
```sh
  POST       api/auth/login ............................. Auth\AuthController@login  
  POST       api/auth/logout ............................ Auth\AuthController@logout  
  GET|HEAD   api/auth/me ................................ Auth\AuthController@user  
  POST       api/auth/register .......................... Auth\AuthController@register
```

#### Permission CRUD Routes:

```sh
  GET|HEAD   api/admin/permission ....................... Admin\PermissionController@index  
  POST       api/admin/permission ....................... Admin\PermissionController@store   
  GET|HEAD   api/admin/permission/{slug} ................ Admin\PermissionController@show  
  PATCH      api/admin/permission/{slug} ................ Admin\PermissionController@update  
  DELETE     api/admin/permission/{slug} ................ Admin\PermissionController@destroy 
```

#### Role CRUD Routes:

```sh
  GET|HEAD   api/admin/role ............................. Admin\RoleController@index  
  POST       api/admin/role ............................. Admin\RoleController@store   
  GET|HEAD   api/admin/role/{slug} ...................... Admin\RoleController@show  
  PATCH      api/admin/role/{slug} ...................... Admin\RoleController@update  
  DELETE     api/admin/role/{slug} ...................... Admin\RoleController@destroy 
```

#### Role Based Access Control Routes: 

- `RoleAccessController` : Single Action Controller, Manages User Roles. 
- `PermissionAccessController@manageRolePermissions` : responsible for grouping permissions to roles
- `PermissionAccessController@manageUserPermissions` : responsible to assigning permissions to user

```sh
  POST       api/admin/access-control/role-permissions ........... RBAC\PermissionAccessController@manageRolePermissions  
  POST       api/admin/access-control/user-permissions ........... RBAC\PermissionAccessController@manageUserPermissions  
  POST       api/admin/access-control/user-role .................. RBAC\RoleAccessController
```

