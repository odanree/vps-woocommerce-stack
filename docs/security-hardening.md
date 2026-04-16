# Security hardening runbook

Control inventory for the WooCommerce VPS. Maps each hardening measure to the threat it addresses and the Ansible role that implements it.

---

## SSH

| Control | Implementation | Ansible role |
|---------|---------------|--------------|
| Key-only authentication | `PasswordAuthentication no` in sshd_config | `security` |
| Root login disabled | `PermitRootLogin no` | `security` |
| Allowed users allowlist | `AllowUsers ubuntu` | `security` |
| fail2ban SSH jail | 5 failures → 1 hr ban | `security` |

**Verify:**
```bash
ssh -o PasswordAuthentication=yes user@host  # should fail
fail2ban-client status sshd
```

---

## Firewall (UFW)

Default deny inbound. Explicitly allowed:

| Port | Protocol | Purpose |
|------|----------|---------|
| 22 | TCP | SSH |
| 80 | TCP | HTTP (redirects to HTTPS) |
| 443 | TCP | HTTPS |

All other inbound traffic denied. Outbound unrestricted.

**Verify:**
```bash
ufw status verbose
nmap -sS -p 1-65535 your-server-ip  # should show only 22, 80, 443
```

---

## TLS

| Control | Setting |
|---------|---------|
| Protocols | TLS 1.2 and 1.3 only |
| Cipher suite | Mozilla Intermediate profile (ECDHE/AES-GCM/ChaCha20) |
| HSTS | `max-age=63072000; includeSubDomains; preload` |
| OCSP stapling | Enabled |
| Session tickets | Disabled |
| Certificate auto-renewal | Certbot systemd timer (daily at 03:00) |

**Verify:**
```bash
testssl.sh https://your-domain.com  # should score A or A+
```

---

## WordPress hardening

| Control | Implementation |
|---------|---------------|
| xmlrpc.php blocked | Nginx `deny all; return 403` |
| wp-config.php blocked | Nginx location block |
| wp-login.php rate-limited | `limit_req zone=wp_login burst=3` |
| WordPress login fail2ban | 10 failures → 1 hr ban |
| xmlrpc fail2ban | 3 failures → 24 hr ban |
| File editor disabled | `define('DISALLOW_FILE_EDIT', true)` in wp-config |
| Direct PHP execution in uploads | Nginx: deny `.php` in `wp-content/uploads` |

---

## MySQL

| Control | Setting |
|---------|---------|
| Bind address | `127.0.0.1` (no remote access) |
| Root remote login | Removed |
| Anonymous users | Removed |
| Test database | Removed |
| App user privileges | `GRANT ALL ON wordpress.* TO 'wp_app'@'127.0.0.1'` |
| Binary logging | Enabled (for point-in-time recovery) |
| Slow query log | Enabled (> 2s threshold) |

---

## Automated security patches

`unattended-upgrades` is configured to auto-apply security-only updates. This covers OS packages and their dependencies.

WordPress core, plugin, and theme updates are **not** automated — they require manual review to avoid breaking WooCommerce extensions.

**Monitor pending updates:**
```bash
apt list --upgradable 2>/dev/null | grep -i security
```

---

## Response playbook: suspected compromise

1. Immediately rotate all credentials (MySQL, Redis if authenticated, WordPress admin)
2. Check fail2ban logs: `fail2ban-client status --all`
3. Review Nginx access logs for anomalous IPs: `jq '.remote_addr' /var/log/nginx/*.access.log | sort | uniq -c | sort -rn | head -20`
4. Check for unexpected processes: `ps aux | grep -v '\[' | sort -k3 -rn | head -20`
5. Check for new cron jobs: `crontab -l; ls /etc/cron.*`
6. If confirmed: snapshot the disk immediately (Hetzner console), then rebuild from Ansible
