# Tailwind CSS Guidelines

Using Tailwind CSS v4.

## General Principles

- Use Tailwind CSS classes to style HTML
- Check and use existing Tailwind conventions in the project before writing your own
- Think through class placement, order, priority, and defaults
- Remove redundant classes; add classes to parent or child carefully to limit repetition
- Group elements logically
- Use the `search-docs` tool for exact examples from official documentation

## Spacing

When listing items, use gap utilities for spacing; don't use margins:

```html
<div class="flex gap-8">
    <div>Superior</div>
    <div>Michigan</div>
    <div>Erie</div>
</div>
```

## Dark Mode

If existing pages and components support dark mode, new pages and components must support dark mode similarly using `dark:` prefix.

## Tailwind v4 Specific

### Configuration

Tailwind v4 is CSS-first using the `@theme` directive—no separate `tailwind.config.js` file is needed.

```css
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
```

### Imports

In Tailwind v4, import Tailwind using a regular CSS `@import` statement, NOT the old `@tailwind` directives:

```css
/* ✅ Correct (v4) */
@import "tailwindcss";

/* ❌ Wrong (deprecated v3) */
/* @tailwind base;
   @tailwind components;
   @tailwind utilities; */
```

### Replaced Utilities

Tailwind v4 removed deprecated utilities. Use the replacement:

| Deprecated | Replacement |
|-----------|-------------|
| `bg-opacity-*` | `bg-black/*` |
| `text-opacity-*` | `text-black/*` |
| `border-opacity-*` | `border-black/*` |
| `divide-opacity-*` | `divide-black/*` |
| `ring-opacity-*` | `ring-black/*` |
| `placeholder-opacity-*` | `placeholder-black/*` |
| `flex-shrink-*` | `shrink-*` |
| `flex-grow-*` | `grow-*` |
| `overflow-ellipsis` | `text-ellipsis` |
| `decoration-slice` | `box-decoration-slice` |
| `decoration-clone` | `box-decoration-clone` |

Note: Opacity values are still numeric (not percentages).

## Components

Offer to extract repeated patterns into components that match the project's conventions (Blade, JSX, Vue, etc.).
