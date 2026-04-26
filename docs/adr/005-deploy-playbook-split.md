# ADR 005 — Separate lightweight deploy playbook from full provisioning

**Date:** 2026-04-26  
**Status:** Accepted

## Context

`site.yml` provisions the entire server stack (MySQL, Nginx, PHP, WordPress, TLS, Redis, monitoring). Running it on every push to master is slow (~10 min), noisy, and carries risk of unintended config drift on a live server.

## Decision

Split into two playbooks:

- **`provisioning/site.yml`** — full stack provisioning, run manually when reprovisioning a fresh droplet or making infrastructure changes
- **`provisioning/deploy.yml`** — lightweight deploy, run automatically on every push to master via GitHub Actions

`deploy.yml` only does what actually changes between code pushes:
1. Sync mu-plugins from the repo
2. `git pull` the ODR Image Optimizer plugin
3. `git pull` the payment gateway plugin
4. Flush Redis cache
5. Reload PHP-FPM

## Rationale

- Deploy time drops from ~10 min to ~30 sec
- Idempotent provisioning tasks (install MySQL, configure Nginx, etc.) don't need to run on code deploys
- Reduces blast radius — a bad deploy only touches plugin code and mu-plugins, not server config

## Consequences

- Infrastructure changes (Nginx config, PHP version, new roles) must be applied manually via `site.yml` — they won't auto-deploy
- `deploy.yml` must be kept in sync as new plugins or mu-plugins are added to the repo
