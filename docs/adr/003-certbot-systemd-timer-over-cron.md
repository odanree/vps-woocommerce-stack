# ADR 003 — Certbot renewal via systemd timer over cron

**Status**: Accepted
**Date**: 2026-04-16

## Context

Let's Encrypt certificates expire every 90 days. Certbot needs to run `renew` at least every 60 days to maintain valid certificates. Two standard scheduling mechanisms exist: cron and systemd timers.

## Decision

Use a **systemd timer** (`certbot.timer`) rather than a cron job.

## Rationale

**Jitter**: The timer uses `RandomizedDelaySec=1h` which spreads renewal attempts across a random window. This prevents all Let's Encrypt clients from hammering the ACME API at the same wall-clock time (midnight cron jobs have caused LE rate limiting across shared infrastructure).

**Logging**: systemd captures timer output in the journal (`journalctl -u certbot`). Cron output goes to mail or `/dev/null` unless explicitly redirected — renewal failures are often silently missed.

**Dependency awareness**: systemd timers integrate with the service dependency graph. The timer can depend on `network-online.target`, ensuring renewal doesn't fail because the network isn't ready yet (a real failure mode on reboot-triggered cron jobs).

**Persistent**: `Persistent=true` means if the server was off during a scheduled renewal window, the timer fires immediately on next boot rather than waiting for the next scheduled window.

## Consequences

**Positive:**
- Renewal failures visible in `journalctl` and Netdata health alerts
- Random jitter avoids LE rate limit spikes
- Boot-persistent: no "missed renewal on rebooted server" failure mode
- Standard Ubuntu 24.04 mechanism (Certbot snap and apt both use systemd timers by default)

**Negative:**
- `systemctl list-timers` is less familiar to admins used to `crontab -l`
- Timer unit file must be managed separately from Certbot package (done via Ansible certbot role)
