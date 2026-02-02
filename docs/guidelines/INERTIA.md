# Inertia + Vue Guidelines

Development with Inertia v2 + Vue 3.

## Basic Setup

- Inertia.js components should be placed in `resources/js/Pages` directory (unless specified differently in `vite.config.js`).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.

Example:
```php
// routes/web.php
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
```

## Navigation

- Vue components must have a single root element.
- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

```vue
import { Link } from '@inertiajs/vue3'
<Link href="/">Home</Link>
```

## Inertia v2 Features

Make use of all Inertia v2 features:
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll
- Polling
- Prefetching

### Deferred Props & Empty States

When using deferred props on the frontend, add a nice empty state with pulsing/animated skeleton.

### Forms

The recommended way to build forms is with the `<Form>` component:

```vue
<Form
    action="/users"
    method="post"
    #default="{
        errors,
        hasErrors,
        processing,
        progress,
        wasSuccessful,
        recentlySuccessful,
        setError,
        clearErrors,
        resetAndClearErrors,
        defaults,
        isDirty,
        reset,
        submit,
  }"
>
    <input type="text" name="name" />

    <div v-if="errors.name">
        {{ errors.name }}
    </div>

    <button type="submit" :disabled="processing">
        {{ processing ? 'Creating...' : 'Create User' }}
    </button>

    <div v-if="wasSuccessful">User created successfully!</div>
</Form>
```

Available form slot properties:
- `resetOnError`: Reset form on validation errors
- `resetOnSuccess`: Reset form after successful submission
- `setDefaultsOnSuccess`: Set new defaults after success

For more programmatic control, use the `useForm` helper.

Use the `search-docs` tool with queries like:
- `form component`
- `useForm helper`
- `form component resetting`

## Integration with Wayfinder

If your application uses the `<Form>` component, use Wayfinder to auto-generate form action and method:

```vue
<Form v-bind="store.form()"><input name="title" /></Form>
```

This generates `action="/posts"` and `method="post"` automatically.

## Documentation

Use the `search-docs` tool for accurate guidance on all things Inertia. It searches version-specific documentation for your installed packages.
