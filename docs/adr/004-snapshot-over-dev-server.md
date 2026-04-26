# ADR 004 — Snapshot-before-deploy over persistent dev server

**Date:** 2026-04-26  
**Status:** Accepted

## Context

Deploying changes to `shop.danhle.net` without a rollback mechanism means any broken deploy requires manual SSH intervention to recover. Two options were considered: provision a permanent dev droplet to test against before pushing to prod, or snapshot the prod droplet before each deploy as a rollback point.

## Decision

Use DigitalOcean droplet snapshots as a pre-deploy safety net instead of a separate dev server.

## Rationale

- `shop.danhle.net` is a portfolio demo site with no real customers — brief downtime during a bad deploy + restore cycle is acceptable
- A dev droplet at the cheapest DO tier ($6/month) would rarely be used and adds ongoing cost for a demo project
- Snapshots cost ~$0.06/GB/month and are auto-deleted after 7 days by the deploy workflow, keeping cost near zero
- The snapshot + restore path is fast (~5 min) and documented in the workflow's failure step

## Trade-offs

**Accepted:** Testing still happens on prod. A bad deploy causes real (brief) downtime before the snapshot restore completes.

**Rejected alternative:** Persistent dev droplet gives a true pre-prod environment but adds $6+/month and requires keeping it in sync with prod state.

## Consequences

- Deploy workflow must snapshot before every Ansible run (added to `.github/workflows/deploy.yml`)
- Snapshots older than 7 days are auto-pruned to control cost
- Restore is manual — workflow prints the `doctl` restore command on failure
- If this project gains real traffic or customers, revisit and provision a dev droplet
