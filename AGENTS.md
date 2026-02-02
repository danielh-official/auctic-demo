# Auction Ops Simulator Project

## Project Overview

This project is building an **Auction Ops Simulator**—a backend-focused Laravel application that models the operational backend of a live auction. The project demonstrates:

- Deep Laravel architecture and domain modeling
- Auction business domain expertise (aligning with Auctic's mission)
- Scalability thinking for bidding, concurrency, and time-boxed events
- Clean architecture with services, aggregates, and domain events
- Operations mindset (settlement, reconciliation, failure handling)
- Pragmatic backend design over UI flash

## Core Features Being Implemented

1. **Auction Lifecycle API**: Draft → Scheduled → Live → Closing → Closed → Settled
2. **Bidding Engine**: Bid placement, minimum increments, soft close logic with concurrency safety
3. **Post-Auction Settlement**: Winning bidder determination, buyer's premium, taxes/fees, settlement reports
4. **Minimal Admin UI**: Clean Inertia + Vue dashboard for auction management and settlement review

## Architectural Focus

- State pattern for auction lifecycle transitions
- Database transactions and row locking for concurrency safety
- Idempotency for duplicate bid requests
- Domain events (e.g., `AuctionClosed`, `BidPlaced`)
- Repository pattern with Eloquent
- Comprehensive feature and unit tests (TDD approach)
- GitHub Actions CI/CD pipeline
- Docker-based local development

## Database Schema

Refer to [docs/ER-DIAGRAM.md](docs/ER-DIAGRAM.md) for the complete Entity-Relationship diagram showing all tables, relationships, and constraints.

## Routes Reference

See [docs/ROUTES.md](/docs/ROUTES.md) for an in-progress list of intended application routes, their purposes, and access controls.

---

## Development Guidelines

To keep context lean, development guidelines are now modularized in [docs/guidelines/](docs/guidelines/).

**Always start with [docs/guidelines/CORE.md](docs/guidelines/CORE.md)** for foundational rules that apply to all work.

Then reference skill-specific guidelines based on your task:

| Task Type | Skills |
|-----------|--------|
| Backend / API / Database | [LARAVEL.md](docs/guidelines/LARAVEL.md) |
| Testing | [PEST.md](docs/guidelines/PEST.md) |
| Frontend / UI | [INERTIA.md](docs/guidelines/INERTIA.md), [TAILWIND.md](docs/guidelines/TAILWIND.md) |
| Type-safe routes | [WAYFINDER.md](docs/guidelines/WAYFINDER.md) |
| Authentication | [FORTIFY.md](docs/guidelines/FORTIFY.md) |

**See [docs/guidelines/SKILLS-INDEX.md](docs/guidelines/SKILLS-INDEX.md) for a complete task → skills mapping.**

### Tech Stack

- PHP 8.5.1
- Laravel v12
- Inertia v2 + Vue 3
- Tailwind CSS v4
- Pest v4
- Laravel Fortify v1

### How to Request Work

When assigning tasks, optionally mention skills to load only relevant guidelines:

- "Backend work: [Laravel, Pest]"
- "Frontend: [Inertia, Tailwind]"
- "Full-stack: [Laravel, Inertia, Wayfinder, Pest, Tailwind]"

I'll infer relevant skills if not named, but explicit naming helps ensure the right guidelines are loaded.
