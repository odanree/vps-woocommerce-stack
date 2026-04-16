# vps-woocommerce-stack

Production-ready WooCommerce hosting environment on Hetzner Cloud. Provisions a hardened Ubuntu 24.04 server with Nginx, PHP-FPM, MySQL 8, Redis, Certbot, and Netdata — no Docker, no containers, direct server installation for maximum performance and operational simplicity.

Addresses the production eCommerce hosting lifecycle end-to-end: provisioning, security hardening, TLS automation, Redis object caching, performance tuning, real-time monitoring, and a documented scale-out path from single-node to load-balanced multi-tier.

---

## Architecture

```
Internet
    │
    ▼
[Cloudflare CDN / DNS]
    │
    ▼
Hetzner Cloud VPS (CX21 → CX41)
├── Nginx 1.26  ←── reverse proxy + static files
│     └── PHP-FPM 8.3  ←── WordPress/WooCommerce
├── MySQL 8.0   ←── primary datastore
│     └── query cache tuning, InnoDB buffer pool
├── Redis 7     ←── WooCommerce object cache (wp-object-cache drop-in)
├── Certbot     ←── Let's Encrypt TLS, auto-renew via systemd timer
├── fail2ban    ←── brute-force protection (SSH, WordPress xmlrpc/login)
├── UFW         ←── stateful firewall (22/tcp, 80/tcp, 443/tcp only)
└── Netdata     ←── real-time perf monitoring (Nginx, PHP-FPM, MySQL, Redis)
```

---

## What's in this repo

| Path | Purpose |
|------|---------|
| `provisioning/` | Ansible playbook and roles — full server setup from bare Ubuntu |
| `nginx/` | Nginx site config, SSL params, security headers |
| `mysql/` | `my.cnf` with InnoDB/query tuning for WooCommerce workloads |
| `redis/` | `redis.conf` sized for object cache workload |
| `security/` | fail2ban jail + WordPress filter, UFW setup script |
| `monitoring/` | Netdata config, structured Nginx access log format |
| `scripts/` | Provisioning helper, smoke test, cert renewal check |
| `docs/` | Architecture decisions, scale-out guide, security hardening runbook |

---

## Quick start

### Prerequisites
- Ansible 2.15+ on your local machine
- A fresh Hetzner CX21 (2 vCPU, 4 GB RAM) running Ubuntu 24.04
- Your SSH public key pre-loaded on the server

```bash
# Clone and configure
git clone https://github.com/odanree/vps-woocommerce-stack.git
cd vps-woocommerce-stack

# Copy and edit inventory
cp provisioning/inventory/hosts.example provisioning/inventory/hosts
vim provisioning/inventory/hosts  # set your server IP

# Copy and edit group vars
cp provisioning/group_vars/all.example.yml provisioning/group_vars/all.yml
vim provisioning/group_vars/all.yml  # set domain, DB password, etc.

# Run full provisioning (≈ 8 minutes on a fresh box)
cd provisioning
ansible-playbook -i inventory/hosts site.yml
```

### Smoke test after provisioning
```bash
./scripts/smoke-test.sh your-domain.com
```

---

## Server spec and sizing

| Resource | Minimum (CX21) | Recommended (CX41) | Notes |
|----------|---------------|-------------------|-------|
| vCPU | 2 | 4 | PHP-FPM workers = CPU × 2 |
| RAM | 4 GB | 8 GB | InnoDB buffer: 70% of RAM |
| Disk | 40 GB SSD | 80 GB SSD | MySQL data + logs |
| Redis RAM | 256 MB | 512 MB | `maxmemory` in redis.conf |

---

## Security posture

- **SSH**: key-only auth, root login disabled, non-standard port optional
- **Firewall**: UFW default-deny inbound; only 22/80/443 open
- **Brute-force**: fail2ban jails for SSH (5 attempts), WordPress login (10 attempts), xmlrpc.php (3 attempts)
- **TLS**: Let's Encrypt via Certbot; OCSP stapling, HSTS, TLS 1.2/1.3 only
- **Headers**: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` via Nginx snippet
- **MySQL**: bind to 127.0.0.1 only, dedicated app user with minimal grants, no root remote login
- **WordPress hardening**: disable XML-RPC if not needed, block `wp-login.php` rate-limiting at Nginx

See [`docs/security-hardening.md`](docs/security-hardening.md) for the full control inventory.

---

## Redis object caching

WooCommerce makes 50–200 MySQL queries per page load. Redis object caching collapses repeated identical queries into sub-millisecond cache hits.

```
# Measured on a 2 vCPU CX21 with 500 products, 20 active coupons
Before Redis: 340ms avg TTFB (shop page, warm MySQL)
After Redis:   42ms avg TTFB (cache hit ratio > 90% after warm-up)
```

The provisioning role installs the [WP Redis](https://wordpress.org/plugins/redis-cache/) drop-in and configures `WP_REDIS_HOST`, `WP_REDIS_PORT`, and `WP_REDIS_SELECTIVE_FLUSH` in `wp-config.php`.

See [`redis/redis.conf`](redis/redis.conf) for `maxmemory`, `maxmemory-policy allkeys-lru`, and socket vs TCP trade-offs.

---

## Scale-out path

See [`docs/scale-out.md`](docs/scale-out.md) for the full architecture evolution. Summary:

```
Stage 1 — Single node (this repo)
  └─ 1 VPS handles web, DB, cache, cron

Stage 2 — Dedicated DB
  └─ Separate MySQL node (Hetzner CX21)
  └─ App VPS connects via private network
  └─ No app code changes; change DATABASE_HOST in wp-config.php

Stage 3 — Load-balanced web tier
  ├─ 2+ Nginx/PHP-FPM nodes (stateless)
  ├─ Shared Redis over private network (sessions + object cache)
  ├─ MySQL on dedicated node (or Hetzner Managed Databases)
  ├─ Hetzner Load Balancer in front (TCP/HTTPS)
  └─ Shared NFS or object storage for wp-content/uploads

Stage 4 — Peak traffic
  └─ Autoscale app tier via Hetzner Terraform provider
  └─ Read replicas for MySQL
  └─ Cloudflare cache rules for WooCommerce static assets
```

**Key design constraint**: PHP-FPM sessions must be stored in Redis (not disk) before scaling past one node. The provisioning role sets `session.save_handler = redis` in `php.ini`.

---

## Monitoring

Netdata runs on port 19999 (blocked by UFW, tunnel via SSH):

```bash
ssh -L 19999:localhost:19999 user@your-server
# Open http://localhost:19999
```

Dashboards preconfigured for:
- **Nginx**: requests/s, active connections, upstream response time
- **PHP-FPM**: active processes, queue length, slow requests
- **MySQL**: queries/s, InnoDB buffer pool hit ratio, slow query rate
- **Redis**: hit rate, memory usage, evictions/s
- **System**: CPU steal (Hetzner VPS headroom indicator), disk I/O wait

Nginx access logs use a structured JSON format for easy ingestion into Loki/Grafana if you later add a monitoring stack. See [`monitoring/nginx-access-log.conf`](monitoring/nginx-access-log.conf).

---

## CI

| Check | Trigger |
|-------|---------|
| YAML lint (`yamllint`) | push / PR |
| Shell lint (`shellcheck`) | push / PR |
| Secret scan (gitleaks) | push / PR |
| Ansible syntax check | push / PR |

---

## Related docs

- [Architecture decisions](docs/adr/)
- [Security hardening runbook](docs/security-hardening.md)
- [Scale-out guide](docs/scale-out.md)
- [Monitoring runbook](docs/monitoring-runbook.md)
