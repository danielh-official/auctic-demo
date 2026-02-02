# Laravel Wayfinder Guidelines

Wayfinder generates TypeScript functions and types for Laravel controllers and routes, providing type safety and automatic synchronization between backend routes and frontend code.

## Development Guidelines

- Always use the `search-docs` tool to check Wayfinder correct usage before implementing features.
- Always prefer named imports for tree-shaking: `import { show } from '@/actions/...'`
- Avoid default controller imports (prevents tree-shaking).
- Run `php artisan wayfinder:generate` after route changes if Vite plugin isn't installed.

## Feature Overview

### Form Support

Use `.form()` with `--with-form` flag for HTML form attributes:

```html
<form {...store.form()}>
  <!-- generates action="/posts" method="post" -->
</form>
```

### HTTP Methods

Call specific methods for each HTTP verb:

```typescript
show.get(1)       // { url: "/posts/1", method: "get" }
show.head(1)      // { url: "/posts/1", method: "head" }
show.post(1)      // { url: "/posts/1", method: "post" }
show.patch(1)     // { url: "/posts/1", method: "patch" }
show.put(1)       // { url: "/posts/1", method: "put" }
show.delete(1)    // { url: "/posts/1", method: "delete" }
```

### Invokable Controllers

Import and invoke directly as functions:

```typescript
import StorePost from '@/actions/.../StorePostController'
StorePost() // Invokes the controller
```

### Named Routes

Import from `@/routes/` for non-controller routes:

```typescript
import { show } from '@/routes/post'
show(1) // For route name 'post.show'
```

### Parameter Binding

Detects route keys (e.g., `{post:slug}`) and accepts matching object properties:

```typescript
show("my-post")             // Positional argument
show({ slug: "my-post" })   // Object with route key
```

### Query Merging

Use `mergeQuery` to merge with `window.location.search`. Set values to `null` to remove:

```typescript
show(1, { mergeQuery: { page: 2, sort: null } })
```

### Query Parameters

Pass `{ query: {...} }` in options to append params:

```typescript
show(1, { query: { page: 1 } })  // => "/posts/1?page=1"
```

### Route Objects

Functions return `{ url, method }` shaped objects:

```typescript
show(1)      // { url: "/posts/1", method: "get" }
```

### URL Extraction

Use `.url()` to get just the URL string:

```typescript
show.url(1)  // "/posts/1"
```

## Example Usage

```typescript
// Import controller methods (tree-shakable)
import { show, store, update } from '@/actions/App/Http/Controllers/PostController'

// Get route object with URL and method
show(1) // { url: "/posts/1", method: "get" }

// Get just the URL
show.url(1) // "/posts/1"

// Use specific HTTP methods
show.get(1) // { url: "/posts/1", method: "get" }
show.head(1) // { url: "/posts/1", method: "head" }

// Import named routes
import { show as postShow } from '@/routes/post'
postShow(1) // { url: "/posts/1", method: "get" }
```

## Wayfinder + Inertia

When using the `<Form>` component from Inertia:

```vue
<Form v-bind="store.form()"><input name="title" /></Form>
```

This auto-generates form `action` and `method` attributes.
