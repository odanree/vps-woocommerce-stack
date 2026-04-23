# Learnings — vps-woocommerce-stack

## L1 — stdin consumed by `php -l /dev/stdin` wipes the deploy target

**Date:** 2026-04-21

**What happened:** Chained lint + deploy in a single SSH pipe:
```bash
cat file.php | ssh host "php -l /dev/stdin && sudo tee /path/file.php > /dev/null"
```
`php -l` consumed all of stdin. `sudo tee` received an empty stream and wrote a 0-byte file to production, taking down the site.

**Fix:** Always run lint and deploy as separate commands:
```bash
# 1. Lint
cat file.php | ssh host "php -l /dev/stdin"
# 2. Deploy (only if lint passes)
cat file.php | ssh host "sudo tee /path/file.php > /dev/null"
```

---

## L2 — Single quotes inside PHP `echo '...'` CSS blocks break the parser

**Date:** 2026-04-21

**What happened:** `storefront-cleanup.php` injects all CSS via:
```php
echo '<style id="sf-headless-css">';
echo '... all CSS here ...';
echo '</style>';
```
Any literal `'` inside the CSS string closes the PHP single-quoted string, causing a fatal parse error. Caught twice:

1. CSS comment with apostrophe: `/* Override WC's float layout */`
   Fix: drop the apostrophe — `/* Override WC float layout */`

2. SVG data URL attribute quotes: `xmlns='http://www.w3.org/2000/svg'`
   Fix: URL-encode as %27 — `xmlns=%27http://www.w3.org/2000/svg%27`

**Rule:** Always run `php -l` before deploying. Avoid `'` entirely inside PHP single-quoted CSS strings.
