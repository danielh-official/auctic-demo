# Bid Placement Process

## Concrete Example In Writing

- We have four people: Adam, Bruce, Charlie, and Dennis
- We have one lot with a reserve price of $50.
- Because there is a $50 additional to reach maximum, the minimum bid must be at least $100.
- Adam bids $100, Bruce bids $120, Charlie bids $150, and Dennis bids $200.
- Each bid gets processed on the queue and each user is notified when their bid goes through.
- After Dennis' bid goes through, he is named the current bid and the minimum bid is $250.
- Dennis cannot bid again until someone takes his place.
- Adam, Bruce, and Charlie cannot bid again until X amount of seconds pass by since their last bid. When the timer reaches 0, they each recieve a notification explaining such with a link to visit the page of the lot.
- After X amount of seconds pass, Adam places a bid for $300, and Bruce also places a bid for $300.
- Because Adam was first by at least a millisecond, his bid goes through and Bruce's bid is rejected, with a notification being sent to him explaining the reason and, because Bruce's bid was cancelled, he does not have to wait X amount of seconds before placing his bid again, which should be at least $350.
- The current date/time is the same day as the auction's deadline. When this happens, the amount of seconds before a person can bid again gets reduced. If a person is currently in the waiting phase, their wait time is cut by Y amount based on how close we are to the end of the auction.
- For lots that Adam, Bruce, Charlie, and Dennis have bid on, they recieve notifications when the timer is reduced. Near the end of the auction, we should expect a higher level of concurrent bids for lots that Adam, Bruce, Charlie, and Dennis desire.
- At some point, Adam clicks the place bid 5 times rapidly. Ideally, the frontend should have a confirmation dialog to ask Adam if he's sure about his bid before sending it over to the server. But if not, the server would handle the first request and dump any subsequent requests until Adam's initial bid goes through. Remember that there is a countdown of X amount of seconds before he can send another bid (even towards the end of the auction, though it's less than usual). Even if he's not the highest bid, he still has to wait before submitting another bid, and this should be enforced in the backend.
- Let's say Bruce submits a bid at the exact millisecond of the auction deadline. By default, the auction is over when the current date/time in milliseconds UTC is equal to or greater than the auction's deadline in milliseconds UTC. So Bruce's bid would not go through. However, for such extreme edge cases, we could scale to having a manual review department that goes through high roller bids (e.g., Bruce paying a billion dollars for a lot) and decides if they can be exceptions to the rule. In this web application, an admin would then go in and decide if Bruce's bid can be marked as an exception, placing him as the winner of the lot.
- Dennis is highest bidder. Adam's cooldown expires, but Bruce outbids Dennis before Adam's "you can bid now" notification arrives. In this situation, we keep as is and have whoever is the highest bidder come out on top. If Bruce outbids Dennis's $100 by $150 and Adam, thinking that it's still $100 min bid decides to bid the same later than Bruce, then the aforementioned FIFO bid rule will be maintained and Adam will recieve the notification stating that his bid lost out due to Bruce bidding the same earlier. Again, he won't have to wait, unlike Bruce.
    - Maybe we can have another notification for when the countdown is close to ending (e.g., within 1 minute). When a lot is highly desired and the current min bid is low enough that Adam, Bruce, Charlie, and Dennis can all afford it, then it's to be expected that these rapid fire bids will be placed and players will lose out on milliseconds.
    - The idea is to incentivize high bidding strategies for lots that are highly desired. If I'm Adam, and I'm constantly dealing with my bid within the $1,000 range being beaten by Bruce, Dennis, or Charlie, then perhaps it would be best for me to raise the stakes by raising the min bid to the $10,000 range. If I really want this lot, then I need to consider splurging a bit more than my competition is capable of paying.
    - As the auctioneer, this thought process is exactly what I want to encourage.
- On another lot, Dennis bids $100, becomes highest bidder, but nobody else bids or can bid. In this case, if the auction ends with him as the highest bidder, he wins the lot for $100. However, the exact reason nobody can bid matters. If a manual review team gets reports stating that a bug or some failure of the system was making it impossible for others to bid, the winning can be rendered null and void, with the item being allowed to be registered in another auction.

### Other Information

Bid placements are processed in a dedicated high priority queue. The exact date/time UTC in micmilliseconds is passed along with the bid placement. If there are any queue processing delays, even if bids are processed out of order, the bid dominance is determined by the date/time of placement within the bids table.

### Cases

> Zero or negative reserve - How do lots with no reserve calculate minimum bid?

If no reserve is set, we still have the increment (e.g., $50). So a $0 reserve price lot would have a min bid of $0 plus that increment.

> Cooldown reduction timing - If Adam has 50 seconds left on cooldown and reduction changes from 60s to 30s, does he immediately become eligible?

His time would be cut to 50 seconds - 30 seconds (60-30), making his timeout 20 seconds.

> Cooldown at 0 seconds - Near auction end, does cooldown reach 0 or have a floor (e.g., minimum 5 seconds)?

We still have a cooldown, but it's definitely less than what it was in the beginning.

> Minimum increment changes - What if minimum bid increment rules change mid-auction?

This is allowed and happens as the minimum bid increases.

> Network failure during bid - User's bid is recorded but they never receive confirmation due to network issue on our end.

We should prioritize Consistency (C) and Partition Tolerance (P) (with regards to the CAP theorem) for bidding functionality.

We cannot guarentee that there won't ever be a hiccup with regard to sending notifications to users. We can reduce that probability as much as possible by having the notifications be sent on a dedicated queue. Or, if it becomes even more necessary, we can rely on a dedicated microservice (e.g., one written in Go) to handle analyzing the data on an interval and deciding what kinds of notifications to send to users. This latter option might become necessary as we scale later on, but the complexity and difficulty in maintenance of the system would increase as a result. I would stick to relying on Laravel queueing for now.

Furthermore, on the frontend, we can have the client use polling API endpoint(s) or rely on a web sockets server (e.g., Laravel Reverb) for ensuring the client presents the most up to date information imaginable to each bidder.

> Cooldown notification delivery failure - Adam's cooldown expires but notification fails to send.

For any network-failure-related questions, see the answer for "Network failure during bid - User's bid is recorded but they never receive confirmation due to network issue on our end."

If notifications failing to send becomes a persistent issue, such that not even high priority Laravel queues can reduce it to an ideal amount, then we decide to scale. Perhaps we store the source of truth over what notifications were sent and what should be sent in a dedicated store. Then we have multiple different systems look at that source of truth and have a handler decide what system to handle sending the notifications.

Again, this is a question for when we scale. Pre-optimizing for this problem increases the difficulty of maintenance early on and should be avoided if the consequences of not pre-optimizing are deemed less destructive than the consequences of adding unnecessary complexity to the system.

> Lot removed or hidden - Lot is removed from auction while bids are pending in queue.

The bids are rejected and all bidders are notified that the lot was removed. The current highest bidder for that lot should not have it be included in their invoice.

> When Bruce's $300 bid is rejected (Adam beat him), you say he doesn't wait X seconds. Does this apply to all rejection reasons, or only when outbid? (e.g., if a bid is rejected for insufficient funds, does he also skip the cooldown?)

For now, it applies to all rejection reasons. If a bid is rejected, there's no reason the bidder should have to wait on a cooldown phase.

> Dennis is highest at $200. Can he immediately place a new bid (e.g., $250), or must someone else outbid him first before he can bid again?

The current highest bidder must wait to be outbid before placing a new bid. No exceptions.

## Diagram

TODO
