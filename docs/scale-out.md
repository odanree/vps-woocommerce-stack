# Scale-out guide

This guide documents the staged architecture evolution from a single-node WooCommerce install to a load-balanced, high-availability multi-tier stack.

---

## Stage 1 — Single node (this repo baseline)

All services on one VPS. Suitable for < 5,000 orders/month, < 50 concurrent users.

```
┌──────────────── Hetzner CX21 ────────────────┐
│  Nginx + PHP-FPM                             │
│  MySQL 8.0                                   │
│  Redis 7                                     │
│  Certbot, fail2ban, UFW                      │
│  Netdata                                     │
└──────────────────────────────────────────────┘
```

**Bottleneck signals**: slow query log hits > 10/min; PHP-FPM queue > 0; CPU steal > 5%

---

## Stage 2 — Dedicated database node

Move MySQL to a separate Hetzner CX21. Uses Hetzner private network (no traffic cost, < 1 ms latency).

**Application change required**: update `wp-config.php`:
```php
define('DB_HOST', '10.0.0.10');  // private IP of DB node
```

No code change. No WooCommerce plugin change.

```
                    ┌── Cloudflare ──┐
                    │                │
              ┌─────▼─────┐    ┌─────▼──────┐
              │  Hetzner LB│    │ (optional) │
              └─────┬─────┘    └────────────┘
                    │
          ┌─────────▼──────────┐
          │  Web VPS (CX21)    │
          │  Nginx, PHP-FPM    │
          │  Redis             │
          └─────────┬──────────┘
                    │ private network
          ┌─────────▼──────────┐
          │  DB VPS (CX21)     │
          │  MySQL 8.0         │
          └────────────────────┘
```

---

## Stage 3 — Load-balanced web tier

Add a second web node + Hetzner Load Balancer. Enables zero-downtime deployments via rolling restarts.

**Stateless requirement**: before adding a second node, verify:
1. PHP sessions stored in Redis (`session.save_handler = redis`) — ✔ configured by Ansible
2. `wp-content/uploads` on shared storage (NFS or Hetzner Volumes)
3. WooCommerce object cache uses Redis (not disk transients)

```
            Cloudflare CDN
                  │
          Hetzner Load Balancer
          (HTTP/HTTPS, health check: GET /)
            /           \
  ┌────────▼───┐   ┌────▼────────┐
  │ Web VPS 1  │   │ Web VPS 2   │
  │ Nginx+FPM  │   │ Nginx+FPM   │
  └────────┬───┘   └────┬────────┘
           │            │  shared private network
  ┌────────▼────────────▼─────────┐
  │  Redis (shared object cache + │
  │  PHP session storage)         │
  └───────────────┬───────────────┘
                  │
  ┌───────────────▼───────────────┐
  │  MySQL primary                │
  └───────────────────────────────┘
```

**Deploy procedure** (zero-downtime rolling restart):
```bash
# Drain node 1 from load balancer, update, re-add
hcloud load-balancer remove-target lb-name --server web1
ansible-playbook -i inventory/hosts site.yml --limit web1
hcloud load-balancer add-target lb-name --server web1

# Repeat for node 2
```

---

## Stage 4 — Peak traffic / high availability

For Black Friday / Cyber Monday scale. Automate web tier scaling via Hetzner Terraform provider.

```
              Cloudflare (cache static, absorb bot traffic)
                       │
              Hetzner Load Balancer
              (TCP 443, SSL termination at Nginx)
               /    |    |    \
          Web1  Web2  Web3  Web4   ← Terraform autoscale
              \    |    |    /
               Redis Cluster (Sentinel)
                       │
            MySQL Primary + Read Replica
```

**Key decisions for peak traffic:**
- Cloudflare cache rules: cache product pages (TTL 10m), never cache cart/checkout/account
- MySQL read replica for WooCommerce Analytics and shop queries (configure `W3 Total Cache` or `WP Rocket` to use replica connection)
- Redis Sentinel for HA: if primary fails, Sentinel promotes replica automatically
- PHP-FPM `pm.max_children` tuning: `available_RAM / avg_php_process_size` (typically 40–60 MB each)

---

## Capacity planning

| Stage | VPS Cost/mo | Concurrent Users | Orders/mo |
|-------|-------------|-----------------|-----------|
| 1 — Single CX21 | €5.77 | ~50 | < 5,000 |
| 2 — +DB CX21 | €11.54 | ~100 | < 15,000 |
| 3 — LB + 2 web | €28 | ~300 | < 50,000 |
| 4 — 4 web + HA | €70+ | ~1,000+ | Unlimited |

*Prices approximate as of 2024. Hetzner Managed Databases (MySQL) available as an alternative to self-managed DB node from Stage 2 onward.*
