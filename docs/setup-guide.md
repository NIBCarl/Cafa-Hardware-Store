# CAFA Hardware Store POS System - Setup Guide

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL or PostgreSQL database
- Semaphore SMS API account

## Installation Steps

1. Clone the repository:
```bash
git clone <repository-url>
cd cafa-pos
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Configure your `.env` file with the following settings:

```env
# Application Settings
APP_NAME="CAFA Hardware Store POS"
APP_ENV=local
APP_KEY=<generated-key>
APP_DEBUG=true
APP_URL=http://localhost

# Database Settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cafa_pos
DB_USERNAME=<your-db-username>
DB_PASSWORD=<your-db-password>

# WebSocket Settings
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=<your-pusher-app-id>
PUSHER_APP_KEY=<your-pusher-key>
PUSHER_APP_SECRET=<your-pusher-secret>
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# SMS Settings
SEMAPHORE_API_KEY=<your-semaphore-api-key>
SEMAPHORE_SENDER_NAME="CAFA Hardware"

# Admin Settings
ADMIN_PHONE_NUMBERS=<comma-separated-admin-phone-numbers>
```

6. Generate application key:
```bash
php artisan key:generate
```

7. Run database migrations:
```bash
php artisan migrate
```

8. Build frontend assets:
```bash
npm run build
```

9. Start the development server:
```bash
php artisan serve
```

## Additional Configuration

### Semaphore SMS Integration

1. Sign up for a Semaphore SMS account at https://semaphore.co/
2. Obtain your API key from the Semaphore dashboard
3. Add your API key to the `.env` file:
```env
SEMAPHORE_API_KEY=your-api-key-here
```

### Admin Phone Numbers

Configure admin phone numbers for low stock alerts:
```env
ADMIN_PHONE_NUMBERS=+639123456789,+639987654321
```

### WebSocket Configuration

1. Set up a Pusher account or use a self-hosted WebSocket server
2. Configure the WebSocket settings in your `.env` file:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

## Development Commands

- Run development server: `php artisan serve`
- Watch frontend changes: `npm run dev`
- Run tests: `php artisan test`
- Run code style fixes: `./vendor/bin/pint`

## API Documentation

The API endpoints are organized into the following groups:

### Authentication
- POST `/api/login` - User login
- POST `/api/logout` - User logout
- GET `/api/user` - Get authenticated user

### Products
- GET `/api/products` - List products
- POST `/api/products` - Create product
- GET `/api/products/{product}` - Get product details
- PUT `/api/products/{product}` - Update product
- DELETE `/api/products/{product}` - Delete product
- GET `/api/products/low-stock` - Get low stock products
- POST `/api/products/{product}/adjust-stock` - Adjust product stock

### Categories
- GET `/api/categories` - List categories
- POST `/api/categories` - Create category
- GET `/api/categories/{category}` - Get category details
- PUT `/api/categories/{category}` - Update category
- DELETE `/api/categories/{category}` - Delete category

### Transactions
- GET `/api/transactions` - List transactions
- POST `/api/transactions` - Create transaction
- GET `/api/transactions/{transaction}` - Get transaction details
- POST `/api/transactions/{transaction}/refund` - Refund transaction

## Security Considerations

1. Always use HTTPS in production
2. Keep your API keys secure and never commit them to version control
3. Regularly update dependencies for security patches
4. Monitor SMS usage to prevent abuse
5. Implement rate limiting for API endpoints
6. Use strong passwords for admin accounts

## Troubleshooting

### Common Issues

1. **Database Connection Issues**
   - Verify database credentials in `.env`
   - Ensure database server is running
   - Check database user permissions

2. **SMS Sending Failures**
   - Verify Semaphore API key
   - Check SMS credit balance
   - Validate phone number format

3. **WebSocket Connection Issues**
   - Verify Pusher/WebSocket credentials
   - Check SSL certificate if using HTTPS
   - Ensure proper CORS configuration

### Getting Help

If you encounter any issues:
1. Check the Laravel log file in `storage/logs/laravel.log`
2. Review the system requirements
3. Contact the development team for support
