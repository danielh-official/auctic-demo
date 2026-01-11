# Database ER Diagram

Last Updated: January 10, 2026

This document is intended to highlight the entity relationship plan the backend engineer intends to build for the web app.

Information can be outdated. Be sure to check the above Last Updated date as well as the Git Revision history. For any questions, contact the backend developer @ {insert contact information here}.

## Entity Relationship Diagram

```mermaid
erDiagram
    users ||--o{ bids : "places"
    users ||--o{ auction_registrations : "registers"
    users ||--o{ auctions : "owns"
    users {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string remember_token
        boolean is_admin
        text two_factor_secret
        text two_factor_recovery_codes
        timestamp two_factor_confirmed_at
        timestamp created_at
        timestamp updated_at
    }

    auctions ||--o{ lots : "contains"
    auctions ||--o{ auction_registrations : "has"
    auctions {
        bigint id PK
        bigint user_id FK
        string title
        text description
        string state "draft|scheduled|live|closing|closed|settled"
        timestamp scheduled_at
        timestamp live_at
        timestamp live_ends_at
        timestamp closed_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    lots ||--o{ bids : "receives"
    lots {
        bigint id PK
        bigint auction_id FK
        string title
        string sku
        bigint reserve_price_cents
        string status "pending|active|sold|unsold"
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    bids {
        bigint id PK
        bigint lot_id FK
        bigint user_id FK
        bigint amount_cents
        string status "accepted|rejected|outbid"
        timestamp placed_at
        timestamp created_at
        timestamp updated_at
    }

    auction_registrations {
        bigint id PK
        bigint auction_id FK
        bigint user_id FK
        string status "approved|rejected|suspended"
        timestamp created_at
        timestamp updated_at
    }

    auctions ||--o{ auction_bills : "generates"
    users ||--o{ auction_bills : "owes"
    auction_bills {
        bigint id PK
        bigint auction_id FK
        bigint user_id FK
        bigint subtotal_cents
        bigint buyer_premium_cents
        bigint tax_cents
        bigint total_cents
        bigint paid_cents
        string status "unpaid|partially_paid|paid|overdue|voided"
        timestamp due_at
        timestamp created_at
        timestamp updated_at
    }
```

## Relationships

### Auction Ownership
- **Users** own **Auctions** (one-to-many)
- By extension, lots within an auction are implicitly owned by the auction's owner

### Core Auction Flow
- **Auctions** contain multiple **Lots**
- **Lots** receive multiple **Bids** from users
- **Auction Bills** aggregate all winning lots for a user in an auction

### User Participation
- **Users** place **Bids** on lots
- **Users** participate in auctions via **Auction Registrations**
- Only approved participants can bid

### Billing & Payment
- Each **User** has at most one **Auction Bill** per auction (unique constraint on auction_id, user_id)
- **Auction Bill** aggregates totals from all lots won by the user in an auction

## Key Constraints

- `auction_registrations`: Unique constraint on `(auction_id, user_id)` prevents duplicate registrations
- `auction_bills`: Unique constraint on `(auction_id, user_id)` ensures one bill per user per auction
- All foreign keys use `cascadeOnDelete` for referential integrity
- Composite index on `bids(lot_id, amount_cents)` for efficient bid querying

## State Enums

Refer to `app/Enums/` for authoritative enum values:
- `AuctionState`: Draft, Scheduled, Live, Closing, Closed, Settled
- `LotStatus`: Pending, Active, Sold, Unsold
- `BidStatus`: Accepted, Rejected, Outbid
- `AuctionBillStatus`: Unpaid, PartiallyPaid, Paid, Overdue, Void
- `PaymentIntentStatus`: Initiated, Authorized, Captured, Failed
