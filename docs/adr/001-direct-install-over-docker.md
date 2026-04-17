# ADR 001 — Direct server installation over Docker

**Status**: Accepted
**Date**: 2026-04-16

## Context

WooCommerce hosting can be containerized (Docker Compose with nginx, php-fpm, mysql, redis containers) or installed directly on the OS. Both approaches work; the choice has real operational consequences.

## Decision

Install all services directly on Ubuntu 24.04 — no Docker, no containers.

## Rationale

**Performance**: On a 2 vCPU / 4 GB VPS, Docker adds measurable overhead — container networking (veth pairs, iptables NAT), copy-on-write filesystem layers, and per-container memory footprints. For a resource-constrained node where the InnoDB buffer pool competes with PHP-FPM workers, every MB matters. Direct install gives MySQL full control of the buffer pool without container memory limits.

**Operational simplicity**: A direct install means one less abstraction layer to debug when PHP-FPM is slow, MySQL is locking, or Redis is evicting. `systemctl status nginx`, `journalctl -u mysql`, and `redis-cli INFO` work exactly as the documentation says — no `docker exec`, no compose project namespacing, no volume mount inspection.

**Ansible fit**: Ansible's module ecosystem (mysql_db, mysql_user, apt, systemd, lineinfile) is designed for direct OS configuration. Running Ansible against Docker containers is possible but adds friction (Ansible modules need to know whether to run inside or outside the container).

**Restart granularity**: Handlers like `Reload nginx` and `Restart php-fpm` map directly to `systemctl` — no compose-level restarts that bounce unrelated services.

## Consequences

**Positive:**
- Maximum performance on constrained hardware
- Standard OS tooling for debugging and monitoring
- Ansible roles are straightforward with no container layer
- Netdata integrates directly with system metrics (no cgroup translation)

**Negative:**
- No container image portability — provisioning is tied to Ubuntu 24.04
- Dependency isolation is weaker (PHP version conflicts if multiple apps share the server)
- No `docker-compose up` for local replication of prod — requires a real VM or DO/Hetzner dev droplet

**Mitigation for negative**: The Ansible playbook itself is the reproducibility artifact. Spin up any Ubuntu 24.04 VM and `ansible-playbook site.yml` produces an identical environment in 8 minutes.
