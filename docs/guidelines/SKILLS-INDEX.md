# Skills Index

Quick reference for which guidelines apply to common development tasks.

**All work always includes**: [CORE.md](./CORE.md)

## Task Type → Skills Mapping

| Task | Skills | Quick Notes |
|------|--------|------------|
| Backend API / Database / Model work | LARAVEL, CORE | Use Eloquent relationships, migrations, form requests, factories |
| Testing (unit/feature/browser) | PEST, CORE | Use `php artisan make:test --pest`, run tests before finalizing |
| Frontend / UI Components | INERTIA, TAILWIND, CORE | Vue components in `resources/js/Pages`, use Tailwind v4 utilities |
| Forms (frontend) | INERTIA, WAYFINDER, CORE | Use `<Form>` component, integrate with Wayfinder for type safety |
| Routes / URL generation | WAYFINDER, LARAVEL, CORE | Generate TypeScript actions/routes, use named routes in Blade |
| Authentication workflows | FORTIFY, LARAVEL, CORE | Use `search-docs` for Fortify patterns, integrate with Inertia UI |
| Styling & theming | TAILWIND, CORE | Tailwind v4 only, dark mode support, CSS-first config |
| Full-stack feature | LARAVEL, INERTIA, WAYFINDER, PEST, TAILWIND, CORE | Models → API → Frontend → Tests |
| Code quality / formatting | CORE | Run `vendor/bin/pint --dirty` before finalizing |

## How to Use

1. **Identify your task type** from the table above
2. **Load the relevant skills** mentioned in the "Skills" column
3. **Reference [CORE.md](./CORE.md) always**—it contains foundational rules for all work

## Example: Building an Auction Management Page

- **Skills needed**: LARAVEL, INERTIA, WAYFINDER, TAILWIND, PEST, CORE
- **Workflow**:
  1. Create/update Eloquent model for `Auction` [LARAVEL.md](./LARAVEL.md)
  2. Create API controller with form request validation [LARAVEL.md](./LARAVEL.md)
  3. Run Wayfinder to generate TypeScript routes [WAYFINDER.md](./WAYFINDER.md)
  4. Build Vue page with `<Form>` component [INERTIA.md](./INERTIA.md)
  5. Style with Tailwind v4 utilities [TAILWIND.md](./TAILWIND.md)
  6. Write feature and unit tests [PEST.md](./PEST.md)
  7. Run Pint formatter [CORE.md](./CORE.md)

## All Available Guideline Files

- [CORE.md](./CORE.md) — Foundational rules for all work
- [LARAVEL.md](./LARAVEL.md) — Laravel v12, database, models, controllers, validation
- [PEST.md](./PEST.md) — Testing with Pest v4, browser tests
- [INERTIA.md](./INERTIA.md) — Inertia v2 + Vue 3, forms, navigation
- [TAILWIND.md](./TAILWIND.md) — Tailwind CSS v4 utilities and configuration
- [WAYFINDER.md](./WAYFINDER.md) — TypeScript route/action generation
- [FORTIFY.md](./FORTIFY.md) — Laravel Fortify authentication

## Workflow Tips

- **Name skills when requesting work**: e.g., "API endpoint work: [Laravel, Pest]" or "Frontend: [Inertia, Tailwind]"
- **Trust skill auto-detection**: I'll infer skills from your request, but naming them helps ensure the right guidelines are loaded
- **Use `search-docs`**: Instead of guidelines containing all documentation, use the `search-docs` tool to find version-specific Laravel ecosystem docs
