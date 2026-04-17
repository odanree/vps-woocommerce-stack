# Monitoring runbook

Operations guide for the Netdata + structured logging stack on the WooCommerce VPS.

---

## Accessing Netdata

Netdata runs on port 19999, blocked by both UFW and the DO cloud firewall. Access via SSH tunnel:

```bash
ssh -L 19999:localhost:19999 ubuntu@your-server-ip
# Then open: http://localhost:19999
```

---

## Key dashboards and alert thresholds

### Nginx

| Metric | Dashboard path | Alert threshold |
|--------|---------------|----------------|
| Active connections | Nginx → Active connections | > 200 (CX21/s-2vcpu-4gb) |
| Requests/s | Nginx → Requests | Baseline × 5 (traffic spike) |
| 4xx/5xx rate | Nginx → Responses by code | > 5% of total requests |

**Investigate 5xx spike:**
```bash
tail -f /var/log/nginx/your-domain.error.log
# or query JSON access log:
jq 'select(.status | startswith("5"))' /var/log/nginx/your-domain.access.log | tail -50
```

---

### PHP-FPM

| Metric | Dashboard path | Alert threshold |
|--------|---------------|----------------|
| Active processes | PHP-FPM → Active | ≥ `pm.max_children` (pool exhaustion) |
| Request queue | PHP-FPM → Requests | > 0 sustained (bottleneck) |
| Slow requests | PHP-FPM → Slow requests | > 0 (check slow log) |

**Pool exhaustion**: PHP-FPM queue > 0 means all workers are busy and new requests are waiting. Either:
1. Scale `pm.max_children` (check RAM headroom first: `free -h`)
2. Identify the slow query causing workers to block: `tail -f /var/log/php-fpm/slow.log`

---

### MySQL

| Metric | Dashboard path | Alert threshold |
|--------|---------------|----------------|
| Queries/s | MySQL → Queries | Drop to 0 (service crash) |
| InnoDB buffer pool hit ratio | MySQL → InnoDB → Buffer pool hit ratio | < 95% (increase buffer pool) |
| Slow queries | MySQL → Slow queries | > 5/min sustained |
| Connections used | MySQL → Connections | > 80% of `max_connections` |

**Buffer pool hit ratio < 95%:** InnoDB is reading from disk instead of memory. Either the buffer pool is too small or there's a runaway query doing full table scans.

```bash
# Show top 10 slowest queries
mysqldumpslow -s t -t 10 /var/log/mysql/slow.log

# Check buffer pool utilization
mysql -u root -p -e "SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool%';"
```

---

### Redis

| Metric | Dashboard path | Alert threshold |
|--------|---------------|----------------|
| Hit rate | Redis → Hit rate | < 80% (cache not warming or too small) |
| Memory used | Redis → Memory | > 90% of `maxmemory` |
| Evictions/s | Redis → Evictions | > 0 sustained (maxmemory too low) |
| Connected clients | Redis → Clients | > 50 (check PHP-FPM pool size) |

**Evictions > 0 sustained**: Redis is evicting keys before they naturally expire. Either:
1. Increase `maxmemory` in `redis/redis.conf` (if server has headroom)
2. Check for a runaway process storing large keys: `redis-cli --hotkeys`

**Hit rate < 80% after warm-up:**
```bash
redis-cli INFO stats | grep -E "keyspace_hits|keyspace_misses"
redis-cli INFO keyspace
```

---

### System

| Metric | Alert threshold | Meaning |
|--------|----------------|---------|
| CPU steal > 5% | Hetzner/DO is throttling the vCPU — consider resizing |
| Disk I/O wait > 20% | Database writes exceeding SSD throughput — check slow query log |
| Available RAM < 256 MB | OOM risk — reduce `pm.max_children` or add swap |
| Disk used > 80% | MySQL binary logs filling disk — run `PURGE BINARY LOGS BEFORE DATE_SUB(NOW(), INTERVAL 3 DAY)` |

---

## Log locations

| Service | Log path |
|---------|----------|
| Nginx access (JSON) | `/var/log/nginx/your-domain.access.log` |
| Nginx error | `/var/log/nginx/your-domain.error.log` |
| PHP-FPM slow | `/var/log/php-fpm/slow.log` |
| MySQL slow query | `/var/log/mysql/slow.log` |
| MySQL error | `/var/log/mysql/error.log` |
| Redis | `/var/log/redis/redis-server.log` |
| fail2ban | `journalctl -u fail2ban` |
| Certbot | `journalctl -u certbot` |
| System | `journalctl -xe` |

---

## Useful one-liners

```bash
# Top IPs by request volume (last hour)
jq -r '.remote_addr' /var/log/nginx/*.access.log | sort | uniq -c | sort -rn | head -20

# Slowest Nginx upstream responses
jq 'select(.upstream_response_time != "") | {uri: .request, time: .upstream_response_time}' \
  /var/log/nginx/*.access.log | sort -t: -k2 -rn | head -20

# Active fail2ban bans
fail2ban-client status --all

# Redis cache hit ratio (live)
watch -n 2 'redis-cli INFO stats | grep -E "keyspace_hits|keyspace_misses"'

# MySQL process list (what's running right now)
mysql -u root -p -e "SHOW FULL PROCESSLIST;"

# Disk usage by directory
du -sh /var/lib/mysql /var/log /var/www /tmp | sort -rh
```

---

## Alert response decision tree

```
Checkout page returning 500?
    └── Check: nginx error log → PHP-FPM log → MySQL error log (in that order)
    └── Common cause: MySQL max_connections reached → increase or kill idle connections

Site slow but not erroring?
    └── Check: PHP-FPM active processes (pool exhaustion?)
    └── Check: MySQL slow query log (new slow query from recent WC plugin update?)
    └── Check: Redis hit rate (cache invalidated by WC update?)

TLS certificate error?
    └── journalctl -u certbot → look for renewal failure reason
    └── Manual renew: certbot renew --force-renewal
    └── Check DO firewall allows outbound port 80 (ACME HTTP challenge)

fail2ban banning legitimate traffic?
    └── fail2ban-client set wordpress-login unbanip <IP>
    └── Add IP to ignoreip in jail.local if persistent false-positive
```
