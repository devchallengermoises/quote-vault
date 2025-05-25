# Quote Vault

A modern web application for discovering, saving, and sharing inspirational quotes. Built with Laravel, Livewire, and Tailwind CSS.

## Features

- **Quote Discovery**: Browse through a curated collection of inspirational quotes
- **Favorites System**: Save your favorite quotes for quick access
- **Real-time Updates**: Instant feedback with Livewire-powered interactions
- **Responsive Design**: Beautiful UI that works on all devices
- **Dark Mode Support**: Seamless dark/light theme switching
- **Share Functionality**: Share quotes on Twitter or copy to clipboard
- **Smooth Animations**: Delightful user experience with smooth transitions
- **Toast Notifications**: Clear feedback for user actions
- **Pagination**: Easy navigation through long quotes
- **Background Processing**: Efficient handling of favorite operations

## Recent Improvements

- **Enhanced UI/UX**:
  - Consistent card sizing and layout
  - Improved button placement
  - Smooth animations for interactions
  - Better toast notification system
  - Pagination for long quotes

- **Performance Optimizations**:
  - Background job processing for favorites
  - Efficient cache management
  - Optimized database queries

- **Code Quality**:
  - Refactored components for better maintainability
  - Improved error handling
  - Better state management
  - Enhanced accessibility

## Technical Stack

- **Backend**: Laravel 10.x
- **Frontend**: Livewire 3.x, Tailwind CSS
- **Database**: MySQL
- **Cache**: Redis
- **Queue**: Laravel Queue for background jobs

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure your environment
4. Run migrations: `php artisan migrate`
5. Start the development server: `php artisan serve`

## Development

- Run tests: `php artisan test`
- Start queue worker: `php artisan queue:work`
- Clear cache: `php artisan cache:clear`

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

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

## API Documentation

The API documentation is available in OpenAPI format at `openapi.yaml`. You can view it using:
- Swagger UI
- Redoc
- Postman

### Main Endpoints

- `GET /quotes`: Get the quote of the day
- `POST /quotes/{quote}/favorite`: Toggle favorite status
- `GET /quotes/favorites`: Get favorite quotes
