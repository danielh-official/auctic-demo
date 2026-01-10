# Auction Ops Simulator

A **backend-focused Laravel application** that models the operational backend of a live auction system. This project demonstrates deep Laravel architecture, auction business domain expertise, and scalability thinking for bidding, concurrency, and time-boxed auction events.

## 🛠 Tech Stack

### Backend
- **PHP 8.5** with Laravel 12
- **Laravel Fortify** for authentication
- **Pest 4** for testing (with browser testing support)
- **Laravel Pint** for code formatting
- **Laravel Sail** for Docker development

### Frontend
- **Inertia.js v2** (Vue 3)
- **Tailwind CSS 4**
- **Laravel Wayfinder** for type-safe route generation
- **ESLint & Prettier** for code quality

### Database
- MySQL with Eloquent ORM
- Comprehensive migrations and seeders
- Factory-based test data generation

## 📋 Prerequisites

- **Docker Desktop** (for Laravel Sail)
- **Node.js 18+** and npm
- **Composer**

## 🚀 Getting Started

Fork the repository.

### 1. Clone and Install Dependencies

```bash
# Clone the repository
git clone <repository-url>
cd auctic-demo

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Set up server

#### Docker

```bash
# Run migrations and seed database
composer sail artisan migrate:fresh --seed
```

```bash
# Start Sail (Docker containers)
composer sail up
```

#### Local

```bash
php artisan migrate:fresh --seed
```

```bash
composer dev
```

### 4. Access the Application

- **Application**: http://0.0.0.0

## 🧪 Testing

This project emphasizes comprehensive test coverage using Pest 4.

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BiddingEngineTest.php

# Run with filter
php artisan test --filter=auction_lifecycle

# Run with coverage
php artisan test --coverage
```

**Note**: Replace "php" with "composer sail" if using Docker.

### Browser Tests

Pest 4 includes powerful browser testing capabilities:

```bash
# Run browser tests
php artisan test tests/Browser/
```

**Note**: Replace "php" with "composer sail" if using Docker.

## 📐 Architecture

### Domain Models
- **Auction**: Central auction entity with state machine
- **Lot**: Individual items within an auction
- **Bid**: Bid records with status tracking
- **AuctionParticipant**: User participation in auctions
- **Settlement**: Post-auction financial settlement
- **PaymentIntent**: Payment processing records
- **Ban**: User ban management

### Design Patterns
- **State Pattern**: Auction lifecycle transitions
- **Repository Pattern**: Data access abstraction
- **Domain Events**: Decoupled event handling
- **Service Layer**: Business logic encapsulation

### Concurrency Safety
- Database row locking for bid placement
- Transaction isolation for consistency
- Idempotency keys for duplicate prevention

## 📚 Documentation

- **[Entity-Relationship Diagram](docs/ER-DIAGRAM.md)**: Complete database schema
- **[Intended Routes](docs/INTENDED-ROUTES.md)**: API and web route specifications
- **[Agent Guidelines](AGENTS.md)**: Development context and project goals

## 🔧 Development Commands

```bash
# Format code with Pint
./vendor/bin/pint

# Generate IDE helper files
php artisan ide-helper:models
php artisan ide-helper:generate

# Generate TypeScript routes (Wayfinder)
php artisan wayfinder:generate

# Run queue worker
php artisan queue:work

# Clear all caches
php artisan optimize:clear
```

**Note**: Replace "php" with "composer sail" if using Docker.

## 📝 Code Quality

This project maintains high code quality standards:

- **Laravel Pint**: Automatic code formatting
- **Pest Tests**: Comprehensive test coverage
- **Type Hints**: Strict PHP typing throughout
- **PHPDoc Blocks**: Complete documentation
- **Pest Architecture Testing**: Enforced architectural boundaries

## 🤝 Contributing

This is a demonstration project showcasing best practices for:
- Laravel application architecture
- Auction domain modeling
- Concurrent transaction handling
- Clean code principles

## 📄 License

This project is open-sourced software for demonstration purposes.

## 🙏 Acknowledgments

Built with Laravel ecosystem tools:
- Laravel Framework
- Inertia.js
- Tailwind CSS
- Pest PHP


