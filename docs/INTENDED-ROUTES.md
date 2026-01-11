# Routes

> [!NOTE]
> Work in progress.

Last Updated: January 10, 2026

This document is intended to highlight the routes the backend engineer intends to build for the web app.

Information can be outdated. Be sure to check the above Last Updated date as well as the Git Revision history. For any questions, contact the backend developer @ {insert contact information here}.

## Public Auctions

Guests and users can access

- GET index: /auctions
- GET show: /auctions/{auction}

## My Auctions

Only interact with auctions I own

- GET index: /my/auctions
- GET show: /my/auctions/{auction}
- GET create: /my/auctions/create
- GET edit: /my/auctions/{auction}/edit
- POST store: /my/auctions
- PUT/PATCH update: /my/auctions/{auction}
- DELETE destroy: /my/auctions/{auction}

## My Auction Lots

Only interact with lots for auctions I own

- GET index: /my/auctions/{auction}/lots
- GET show: /my/auctions/{auction}/lots/{lot}
- GET create: /my/auctions/{auction}/lots/create
- GET edit: /my/auctions/{auction}/lots/{lot}/edit
- POST store: /my/auctions/{auction}/lots
- PUT/PATCH update: /my/auctions/{auction}/lots/{lot}
- DELETE destroy: /my/auctions/{auction}/lots/{lot}

## Invite user to auction

Only owner can invite user to an auction they own

- POST invoke: /my/auctions/{auction}/invite-users

## Join an auction

An authenticated user can join an auction they do not own, 

- POST invoke: /auctions/{auction}/join

## Agree to join auction I was invited to

An authenticated user (that has recieved an invite from an auction owner), can update their invite to accepted

- PUT/PATCH invoke: /auctions/{auction}/accept-invitation

## Ban user from my auction

An authenticated user can ban another user from joining an auction

- POST invoke: /my/auctions/{auction}/ban-users

## Ban user from joining any of my auctions

An authenticated user can ban another user from joining any auction they own

- POST invoke: /my/auctions/all/ban-users

## Bid on auction

An authenticated user can bid on auction if accepted participant

- POST invoke: /lots/{lot}/bid

## Pay for final amount on auction lot

An authenticated user that has won an auction they are participating in can pay for their items, with the total amount being the sum of the final bid for each of their winnings

- POST invoke: /auctions/{auction}/pay