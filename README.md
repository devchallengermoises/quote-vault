# QuoteVault

A Laravel application for managing and sharing inspirational quotes.

## Features

- Daily inspirational quotes
- Favorite quotes management
- Social sharing capabilities
- Real-time notifications
- Responsive design
- Performance optimization with caching
- FavQs API integration

## Requirements

- Docker
- Docker Compose
- Git
- PHP 8.2 or higher
- Node.js 18 or higher
- Composer

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/quote-vault.git
cd quote-vault
```

2. Create and configure your environment file:
```bash
cp .env.example .env
```

3. Configure your environment variables in `.env`:
```env
APP_NAME=QuoteVault
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=quotevault
DB_USERNAME=quotevault
DB_PASSWORD=your_secure_password

REDIS_HOST=cache
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mail
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hello@quotevault.com
MAIL_FROM_NAME="${APP_NAME}"
```

4. Start the application:
```bash
# Start Docker containers
docker compose up -d
```

The application will automatically:
- Install PHP dependencies
- Install Node.js dependencies
- Generate application key
- Run database migrations
- Create storage link
- Build frontend assets
- Optimize application performance with caching

The application will be available at http://localhost:8080

## Configuration

### Environment Variables

The following environment variables can be configured in your `.env` file:

#### Application
- `APP_NAME`: The name of your application
- `APP_ENV`: The environment (local, production, etc.)
- `APP_DEBUG`: Enable/disable debug mode
- `APP_URL`: The base URL of your application

#### Database
- `DB_CONNECTION`: Database driver (mysql)
- `DB_HOST`: Database host (db)
- `DB_PORT`: Database port (3306)
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password

#### Redis
- `REDIS_HOST`: Redis host (cache)
- `REDIS_PASSWORD`: Redis password
- `REDIS_PORT`: Redis port (6379)

#### Mail
- `MAIL_MAILER`: Mail driver (smtp)
- `MAIL_HOST`: Mail host (mail)
- `MAIL_PORT`: Mail port (1025)
- `MAIL_USERNAME`: Mail username
- `MAIL_PASSWORD`: Mail password
- `MAIL_ENCRYPTION`: Mail encryption
- `MAIL_FROM_ADDRESS`: Default from address
- `MAIL_FROM_NAME`: Default from name

### Docker Services

The application uses the following Docker services:

- **app**: Laravel application server
- **db**: MySQL 8.0 database
- **cache**: Redis for caching and sessions
- **mail**: Mailpit for email testing

### Ports

Default ports used by the services:
- Web server: 8080
- Vite development server: 5173
- MySQL: 3306
- Redis: 6379
- Mailpit SMTP: 1025
- Mailpit Web UI: 8025

## Development

### Useful Commands

#### Run Tests
```bash
docker compose exec app php artisan test
```

#### Run Tests with Coverage
```bash
docker compose exec app php artisan test --coverage
```

#### Clear Quote Cache
```bash
docker compose exec app php artisan quotes:clear-cache
```

#### Access Laravel Console
```bash
docker compose exec app php artisan tinker
```

#### View Logs
```bash
docker compose logs -f app
```

#### Access MySQL Console
```bash
docker compose exec db mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE}
```

#### Access Redis CLI
```bash
docker compose exec cache redis-cli
```

#### View Mailpit Dashboard
Open http://localhost:8025 in your browser

#### Stop All Containers
```bash
docker compose down
```

#### Rebuild Containers
```bash
docker compose up -d --build
```

#### View Container Status
```bash
docker compose ps
```

### Project Structure

```
app/
├── ApiClients/     # External API clients
├── Console/        # Console commands
├── Http/          # Controllers and middleware
├── Livewire/      # Livewire components
├── Models/        # Eloquent models
├── Repositories/  # Data access layer
└── Services/      # Business logic
```

### Included Services

- **Laravel**: Main web server
- **MySQL**: Database
- **Redis**: Cache and sessions
- **Mailpit**: Development mail server
- **Selenium**: For browser testing

## API Documentation

The API documentation is available in OpenAPI format at `openapi.yaml`. You can view it using:
- Swagger UI
- Redoc
- Postman

### Main Endpoints

- `GET /quotes`: Get the quote of the day
- `POST /quotes/{quote}/favorite`: Toggle favorite status
- `GET /quotes/favorites`: Get favorite quotes

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License.

## Support

If you encounter any issues or have suggestions, please:
1. Check existing issues
2. Create a new issue with a detailed description
3. Include steps to reproduce the problem
4. Add screenshots if relevant

## Architecture

### Backend
- Laravel 10.x
- PHP 8.2
- MySQL 8.0
- Redis for caching
- Livewire for real-time updates

### Frontend
- Tailwind CSS
- Alpine.js
- Livewire components
- Responsive design

### Testing
- PHPUnit for unit and feature tests
- Selenium for browser testing
- Test coverage reporting

### Development Tools
- Docker for containerization
- Laravel Sail for development environment
- Mailpit for email testing
- Redis for caching and sessions
