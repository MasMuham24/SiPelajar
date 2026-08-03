# Laravel School Management App: Agent Instructions

This document provides high-signal instructions for interacting with the Laravel 12 school management application.

## 1. Commands

*   **Development Server**:
    ```bash
    composer run dev
    # This command runs `php artisan serve`, `npm run dev`, `php artisan queue:listen`, and `php artisan pail` concurrently.
    ```
*   **Run Tests**:
    ```bash
    composer run test
    # This command clears config cache and runs `php artisan test`.
    ```
*   **Run Migrations**:
    ```bash
    php artisan migrate
    ```
*   **Rollback Migrations**:
    ```bash
    php artisan migrate:rollback
    ```
*   **Seed Database**:
    ```bash
    php artisan db:seed
    ```

## 2. Role-Based Access and Routing

The application uses role-based access control (RBAC) defined in `app/Models/User.php` and enforced via middleware in `routes/`.

*   **Roles**: `admin`, `guru` (teacher), `siswa` (student).
*   **Access Pattern**: Routes are grouped by role using `role:rolename` middleware.
    *   `routes/admin.php`: Restricted to `admin` role.
    *   `routes/guru.php`: Restricted to `guru` role.
    *   `routes/siswa.php`: Restricted to `siswa` role.
    *   `routes/auth.php`: Handles authentication (login, logout, profile).
*   **Routing Structure**:
    *   `auth` routes: `/login`, `/logout`, `/profile`.
    *   `admin` routes: prefixed with `/admin`.
    *   `guru` routes: prefixed with `/guru`.
    *   `siswa` routes: prefixed with `/siswa`.

## 3. Environment Configuration and Database Setup

*   **Environment Variables**: The application uses a `.env` file for configuration. A template is provided as `.env.example`. Copy and configure it for local development.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
*   **Database**: The application uses SQLite by default (`database/database.sqlite`). Use migrations to set up the schema.
    *   After copying `.env`, ensure database credentials are correct.
    *   Run migrations to set up the database tables.
    *   Optionally, run seeders for initial data.

## 4. Testing Conventions

*   **Framework**: PHPUnit. Testing env is configured in `phpunit.xml` (uses `:memory:` sqlite).
*   **Location**: Tests are located in the `tests/` directory (e.g., `tests/Feature`, `tests/Unit`).
*   **Execution**: Use `composer run test` to execute all tests.
*   **Workflow**: When adding new features or fixing bugs, ensure relevant tests are written or updated. Prefer feature tests for end-to-end flow and unit tests for isolated logic.