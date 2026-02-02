# Laravel Guidelines

Specific guidance for Laravel v12 development.

## General Principles

Use `php artisan make:` commands to create new files (migrations, controllers, models, etc.). For generic PHP classes, use `php artisan make:class`.

Pass `--no-interaction` to all Artisan commands to ensure they work without user input. Use the `list-artisan-commands` tool to double-check available parameters.

For generic PHP classes, use `php artisan make:class`.

## Database & Models

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Leverage Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

When creating new models, also create useful factories and seeders. Ask the user if they need other things using `list-artisan-commands`.

### Migrations

When modifying a column, the migration must include all attributes that were previously defined on the column. Otherwise, they will be dropped and lost.

### Model Casts

Casts can and should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

## Controllers & Validation

Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.

Check sibling Form Requests to see if the application uses array or string based validation rules.

## APIs & Resources

For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not—then follow existing application convention.

## Queues

Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Authentication & Authorization

Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

When generating links to other pages, prefer named routes and the `route()` function.

## Configuration

Use environment variables only in configuration files—never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Laravel Boost Tools

Laravel Boost is an MCP server with powerful tools designed for this application. Use them:

- **Tinker**: Execute PHP to debug code or query Eloquent models directly. Use the `tinker` tool.
- **Database Query**: Read from the database. Use `database-query` tool.
- **Documentation Search**: Use `search-docs` before any other approaches for Laravel ecosystem package docs. This tool automatically filters for your specific package versions. Pass an array of packages to filter on if needed.
  - Syntax: `["rate limiting", "routing rate limiting", "routing"]` (auto-stemming and AND/OR logic supported)

## Artisan Commands

Common commands:
- `php artisan make:model` - Create a model with factory/migration
- `php artisan make:controller` - Create a controller
- `php artisan make:test` - Create a feature test
- `php artisan make:test --unit` - Create a unit test
- `php artisan make:request` - Create a form request
- `php artisan test --compact` - Run tests concisely
- `php artisan wayfinder:generate` - Generate TypeScript routes/actions

## Code Formatting

Run `vendor/bin/pint --dirty` before finalizing changes to ensure code matches project style. Do not run `--test`; simply run `vendor/bin/pint` to fix issues.
