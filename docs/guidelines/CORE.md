# Core Development Guidelines

These rules apply to **all** work on the Auction Ops Simulator, regardless of task type.

## Foundational Context

This application uses:
- PHP 8.5.1
- Laravel v12
- Inertia v2 + Vue 3
- Tailwind CSS v4
- Pest v4 for testing

## PHP Standards

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

### Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless something is very complex.
- Add useful array shape type definitions for arrays when appropriate.

### Enums
- Typically, keys should be TitleCase (e.g., `FavoritePerson`, `BestLake`, `Monthly`).

## Architecture & Code Structure

- Follow all existing code conventions used in the application. Check sibling files for correct structure, approach, and naming.
- Use descriptive variable and method names (e.g., `isRegisteredForDiscounts`, not `discount()`).
- Check for existing components to reuse before writing new ones.
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change application dependencies without approval.

## URLs

Whenever sharing a project URL with the user, use the `get-absolute-url` tool to ensure correct scheme, domain/IP, and port.

## Frontend Bundling

If frontend changes aren't visible in the UI, the user likely needs to run:
- `npm run build`
- `npm run dev`
- `composer run dev`

## Testing

Every change must be programmatically tested:
- Write a new test or update an existing test
- Run tests: `php artisan test --compact` with specific filename or filter
- Run minimal tests needed to ensure code quality and speed

When creating tests with models, use factories. Check if the factory has custom states before manually setting up models.

## Test Enforcement

- Do not create verification scripts or tinker when tests already cover that functionality.
- Unit and feature tests are more important than manual verification.
