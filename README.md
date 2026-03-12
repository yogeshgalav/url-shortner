artisan# URL Shortener

A web application for shortening URLs, built with Laravel and Inertia.js.

## Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL or another database supported by Laravel

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd url-shortner
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Copy the environment file and configure it:
   ```bash
   cp .env.example .env
   ```
   Update the `.env` file with your database credentials and other settings.

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Run database migrations:
   ```bash
   php artisan migrate
   ```

7. Seed the database (including roles and superadmin user):
   ```bash
   php artisan db:seed
   ```

## Running the Application

1. Start the Laravel development server:
   ```bash
   php artisan serve
   ```
   The application will be available at `http://localhost:8000`.

2. In a separate terminal, start the frontend build process:
   ```bash
   npm run dev
   ```
   This will compile and watch the frontend assets.

## Superadmin Credentials

After seeding the database, you can log in as the superadmin with the following credentials:

- **Email:** superadmin@example.com
- **Password:** password

## Features

- Shorten URLs
- User authentication and roles (super_admin, admin, member)
- Company and invitation management
- Responsive UI with Tailwind CSS

## Testing

Run the tests using Pest:

```bash
./vendor/bin/pest
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
