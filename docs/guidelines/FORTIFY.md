# Laravel Fortify Guidelines

Using Laravel Fortify v1 for authentication.

## Overview

Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.

## Important

**Always use the `search-docs` tool for detailed Laravel Fortify patterns and documentation.** This ensures you get version-specific guidance for Fortify v1.

## Key Concepts

- Fortify provides the backend authentication logic
- It's headless, meaning you define the UI (in this project, using Inertia + Vue)
- Routes and controllers are automatically registered
- Customize behavior through configuration in `config/fortify.php`

## Common Tasks

For guidance on:
- Custom authentication logic
- Two-factor authentication
- Password reset flows
- Profile updates
- Account deletion

Use the `search-docs` tool with relevant queries like:
- `fortify authentication`
- `fortify two factor`
- `fortify password reset`
- `fortify custom middleware`

## Integration with This Project

This project uses Fortify in conjunction with:
- **Inertia + Vue**: For the authentication UI
- **Laravel's authorization**: For checking permissions on authenticated users
- **Custom Form Requests**: For validation of authentication inputs

See [LARAVEL.md](./LARAVEL.md) for related form validation and authorization guidance.
