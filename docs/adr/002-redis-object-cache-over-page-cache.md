# ADR 002 — Redis object cache over full-page cache

**Status**: Accepted
**Date**: 2024-02-01

## Context

WooCommerce stores can be accelerated at two different caching layers:

1. **Full-page cache** (e.g., WP Super Cache, W3 Total Cache page cache, Nginx `fastcgi_cache`): Cache entire HTML responses. Extremely fast — Nginx serves cached pages without hitting PHP at all. **But**: WooCommerce cart, checkout, account pages, and any page that shows session-dependent content cannot be cached. For a store where most traffic is authenticated or has items in cart, cache hit rate is low.

2. **Object cache** (Redis with WP Redis drop-in): Cache individual database query results in Redis. PHP still runs on every request, but MySQL queries are served from memory. Works on all pages, including cart and checkout.

## Decision

Use **Redis object cache** as the primary caching strategy. Full-page cache is not implemented by default — it can be layered on top for anonymous shop/product pages if needed.

## Rationale

High-risk merchant stores often have:
- Subscription checkout flows (always session-dependent — cannot page-cache)
- Personalized pricing (wholesale tiers, member pricing — session-dependent)
- Cart persistence across sessions (cookie-driven — cannot page-cache cleanly)
- Frequent product updates (inventory changes invalidate page cache aggressively)

In these conditions, full-page cache hit rates drop below 30%, while Redis object cache consistently achieves 85–95% hit rates because database queries for product catalog, options, and taxonomy are repeated across all users.

**Measured impact** (2 vCPU CX21, 500 products):
```
Before Redis object cache: 340ms avg TTFB (shop page, warm MySQL)
After Redis object cache:   42ms avg TTFB (>90% hit ratio after warm-up)
```

## Consequences

**Positive:**
- Works across all page types — cart, checkout, account pages all benefit
- No cache-invalidation complexity for dynamic WooCommerce pages
- `WP_REDIS_SELECTIVE_FLUSH` invalidates only changed object groups, not the entire cache

**Negative:**
- PHP still executes on every request (vs. Nginx serving a static file for full-page cache)
- Requires Redis process running (additional ~50 MB RSS)
- Cache is warm-up dependent — first requests after deploy or Redis restart are slower

**If full-page cache is also needed**: Layer Nginx `fastcgi_cache` for anonymous product/shop pages on top of Redis object cache, with cache exclusion rules for `PHPSESSID`, `woocommerce_*`, and `wordpress_logged_in_*` cookies.
