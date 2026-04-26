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

---

## L3 — wp-env on Windows silently ignores port config due to Hyper-V reservations

**Date:** 2026-04-25

**What happened:** `npx wp-env start` kept binding to 8888/8889 (its defaults) even after setting `port` and `testsPort` in `.wp-env.json`. The ports 8828–8927 are reserved by Hyper-V on Windows, which includes both defaults. wp-env also caches a generated `docker-compose.yml` in `~/.wp-env/[hash]/` — changing the config doesn't take effect until the cache is cleared.

**Fix:**
1. Set ports above all excluded ranges (check with `netsh interface ipv4 show excludedportrange protocol=tcp`)
2. Delete the cache dir: `rm -rf ~/.wp-env/[hash]`
3. Pass ports as env vars so Docker Compose picks them up: `export WP_ENV_PORT=9080 && export WP_ENV_TESTS_PORT=9091`
4. Use `.wp-env.json` (dot-prefixed) — wp-env 11.x warns it can't find `wp-env.json` without the dot

**Rule:** On Windows, always check Hyper-V excluded port ranges before picking wp-env ports. Use ports > 9057 to clear all typical reservations.

---

## L4 — wp-env seed must hook into `woocommerce_init`, not `init`

**Date:** 2026-04-26

**What happened:** Seed mu-plugin used `add_action('init', ...)` to create WooCommerce products. When triggered from a browser request, `WC_Product_Simple` wasn't available yet at priority 10 — products were never created but `_sf_seed_done` was set, so the seed silently skipped on every subsequent request.

**Fix:** Change to `add_action('woocommerce_init', ...)`. WooCommerce fires this hook after its own classes are fully loaded, making `WC_Product_Simple` available.

**Rule:** Any seed code that instantiates WC classes must run on `woocommerce_init` or later, never plain `init`.

---

## L5 — wp-env CLI container can't reach placehold.co; use picsum.photos; use eval-file not eval for multiline PHP

**Date:** 2026-04-26

**What happened:** Two separate issues hit during local seeding:
1. `placehold.co` returned errors from inside the CLI container. `picsum.photos` works fine.
2. Multiline PHP passed to `wp eval '...'` via shell is silently stripped when the code contains single quotes — the shell ends the string early and wp-env runs an empty eval with no error.

**Fix:**
1. Use `https://picsum.photos/seed/{slug}/600/600.jpg` for seed images
2. Write PHP to a file, copy into the container with `docker cp`, and run with `wp eval-file`

**Rule:** Never trust shell quoting for multiline `wp eval`. Always use `eval-file` for anything beyond a one-liner.

---

## L6 — `wp config set VALUE --raw` breaks PHP syntax for string values

**Date:** 2026-04-26

**What happened:** Running `wp config set WP_MEMORY_LIMIT 256M --raw` wrote `define( 'WP_MEMORY_LIMIT', 256M )` to wp-config.php — no quotes around `256M`, which is invalid PHP. Site returned 500 immediately.

**Fix:** `docker exec [container] sed -i` to correct the define. For string config values, omit `--raw`:
```bash
wp config set WP_MEMORY_LIMIT '256M'   # correct — adds quotes
wp config set WP_MEMORY_LIMIT 256M --raw  # wrong — no quotes, fatal
```

**Rule:** Only use `--raw` for numeric or boolean constants (`true`, `false`, integers). Always omit it for string values.
