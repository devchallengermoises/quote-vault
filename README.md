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

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/level-connections.git
cd level-connections
```

2. Start the application:
```bash
docker compose up -d
```

This command will:
- Build and start Docker containers
- Install PHP dependencies
- Install Node.js dependencies
- Run database migrations
- Start the development server
- Configure additional services (MySQL, Redis, Mailpit, Selenium)

The application will be available at http://localhost:8000

## Development

### Useful Commands

#### Run Tests
```bash
docker compose exec laravel.test php artisan test
```

#### Run Tests with Coverage
```bash
docker compose exec laravel.test php artisan test --coverage
```

#### Clear Quote Cache
```bash
docker compose exec laravel.test php artisan quotes:clear-cache
```

#### Access Laravel Console
```bash
docker compose exec laravel.test php artisan tinker
```

#### View Logs
```bash
docker compose logs -f laravel.test
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
