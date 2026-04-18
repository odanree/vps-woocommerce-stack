# Provisioning Learnings — vps-woocommerce-stack

> One-off issues hit during the first Ansible + Terraform provisioning run (April 2026).
> Written so the next project starts cleaner.

---

## 1. Start from a clean server — always use Terraform

**What happened:** Tried to run Ansible against the old DO droplet (64.23.165.167) that had
previously run a WordPress/MariaDB stack. Hit cascading failures from leftover artifacts:
MariaDB config files in `/etc/mysql/mariadb.conf.d/` conflicting with MySQL 8.0, a frozen
MySQL data directory (`/etc/mysql/FROZEN`), and a temporary root password from `mysqld --initialize`.

**Fix each time:** Manual SSH + one-off commands to clean up. Took ~1 hour.

**The real fix:** Destroy the droplet, run `terraform apply` for a clean Ubuntu 24.04 image.
Ansible is idempotent but it doesn't clean up artifacts it didn't install. A fresh server
eliminates the entire class of problem.

**Rule:** Never run Ansible against a server that previously ran a different stack.
`terraform destroy && terraform apply` is always faster than debugging leftover state.

---

## 2. Nginx SSL chicken-and-egg problem

**What happened:** The Nginx role installs the WordPress site config (which references
`/etc/letsencrypt/live/.../fullchain.pem`) before the Certbot role has run to create the cert.
`nginx -t` fails because the cert file doesn't exist. Certbot can't run because it needs
Nginx. Classic deadlock.

**Workaround (old):** Stop Nginx, run `certbot certonly --standalone`, restart Nginx, re-run playbook.

**Fix (implemented):** Added tasks to the nginx role that generate a self-signed placeholder cert
before deploying the site config. Nginx starts with the placeholder, Certbot runs and replaces it,
Nginx reloads. One clean pass, no manual intervention.

```yaml
- name: Check if Let's Encrypt cert already exists
  stat:
    path: "/etc/letsencrypt/live/{{ server_domain }}/fullchain.pem"
  register: le_cert

- name: Generate self-signed placeholder cert
  command: >
    openssl req -x509 -nodes -newkey rsa:2048
    -keyout /etc/letsencrypt/live/{{ server_domain }}/privkey.pem
    -out /etc/letsencrypt/live/{{ server_domain }}/fullchain.pem
    -days 1 -subj "/CN={{ server_domain }}"
  args:
    creates: "/etc/letsencrypt/live/{{ server_domain }}/fullchain.pem"
  when: not le_cert.stat.exists
```

---

## 3. Missing dhparam.pem generation task

**What happened:** The Nginx SSL params snippet (`ssl-params.conf`) references
`/etc/ssl/dhparam.pem` but the nginx role had no task to generate it. Nginx fails to start.

**Workaround (old):** `sudo openssl dhparam -out /etc/ssl/dhparam.pem 2048` manually on server.

**Fix (implemented):** Added to the nginx role before the site config task:

```yaml
- name: Generate DH parameters (2048-bit)
  command: openssl dhparam -out /etc/ssl/dhparam.pem 2048
  args:
    creates: /etc/ssl/dhparam.pem  # skips if file already exists
```

The `creates:` argument makes this idempotent — only runs if the file is missing.

---

## 4. Certbot "live directory exists" error after placeholder cert

**What happened:** The nginx role creates `/etc/letsencrypt/live/{{ server_domain }}/` for the
self-signed placeholder (fix for issue #2). Certbot sees the directory but has no renewal config
for it — so it errors with "live directory exists" and refuses to proceed even with `--expand`.
`--expand` only works when Certbot itself previously created the cert.

**Fix (implemented):** In the certbot role, check for the renewal config before running certbot.
If it's missing, the live directory is our placeholder — delete it so Certbot can start clean:

```yaml
- name: Check for real LE renewal config
  stat:
    path: "/etc/letsencrypt/renewal/{{ server_domain }}.conf"
  register: le_renewal

- name: Remove self-signed placeholder cert (not a real LE cert)
  file:
    path: "/etc/letsencrypt/live/{{ server_domain }}"
    state: absent
  when: not le_renewal.stat.exists
```

---

## 5. MySQL root password task assumes no password exists

**What happened:** The `Set MySQL root password` task connects via unix socket without a
password — which works on a completely fresh MySQL install (auth_socket plugin). But if
Ansible ran previously and already set the password, the second run fails with
`Access denied (using password: NO)`.

**Workaround (old):** Write `/root/.my.cnf` with the root credentials manually on the server.

**Fix (implemented):** Write `/root/.my.cnf` as a task immediately before setting the password.
On first run (auth_socket), MySQL ignores the file. On re-runs (caching_sha2_password), the
module reads it automatically.

```yaml
- name: Write /root/.my.cnf for passwordless root CLI access
  copy:
    content: |
      [client]
      user=root
      password={{ mysql_root_password }}
    dest: /root/.my.cnf
    owner: root
    group: root
    mode: "0600"
```

Side benefit: `mysql -e "SHOW DATABASES;"` works on the server without `-p` flag.

---

## 6. Certbot role requests www subdomain that doesn't exist in DNS

**What happened:** The certbot role requests a cert for both `{{ server_domain }}` and
`www.{{ server_domain }}`. If only the apex subdomain is in Cloudflare (e.g. `shop.danhle.net`
but not `www.shop.danhle.net`), Let's Encrypt returns NXDOMAIN and the cert request fails.

**Fix:** Removed `www.{{ server_domain }}` from the certbot role. For a portfolio demo a
`www` record isn't needed. Added `--expand` flag to handle re-runs where a cert already exists.

**Rule:** Only request cert coverage for DNS records that actually exist.

---

## 7. Nginx mainline PPA not available for Ubuntu 24.04

**What happened:** The nginx role tried to add `ppa:ondrej/nginx-mainline` which returns
404 on Ubuntu 24.04 Noble — the PPA hadn't been updated for this release.

**Fix:** Removed the PPA. Ubuntu 24.04 ships Nginx 1.24 in its default repos which is
recent enough for this stack.

**Rule:** When adding PPAs, verify they support the target Ubuntu release before writing
the playbook task. Check: `https://launchpad.net/~ondrej/+archive/ubuntu/nginx-mainline`

---

## 8. Ansible installed via apt was too old (2.10)

**What happened:** `sudo apt install ansible` on Ubuntu 22.04 WSL installed Ansible 2.10
which threw `ModuleNotFoundError: No module named 'ansible.module_utils.six.moves'` on
the remote host.

**Fix:** Install via conda/pip instead: `pip install ansible` which installs the current
stable version (2.15+).

**Rule:** Never install Ansible via apt — the distro packages are always multiple major
versions behind. Always use pip.

---

## 9. Terraform-generated Ansible inventory uses id_ed25519

**What happened:** The `local_file` resource in `main.tf` hardcodes `~/.ssh/id_ed25519`
in the generated inventory. The actual key on the machine was `id_rsa`.

**Fix:** `sed -i 's/id_ed25519/id_rsa/' provisioning/inventory/hosts` after `terraform apply`.

**The real fix (TODO):** Make the key path a Terraform variable:

```hcl
variable "ssh_private_key_path" {
  default = "~/.ssh/id_ed25519"
}
```

Then reference `var.ssh_private_key_path` in the `local_file` resource.

---

## What a clean first run looks like (target state)

```
terraform apply          # creates droplet, firewall, project, inventory
ansible-playbook site.yml  # one command, no manual steps, ~5 minutes
curl -I https://shop.danhle.net  # 200 OK
```

Issues 2, 3, 4, 5, and 9 are all now fixed in the roles. The playbook runs clean on any
fresh Terraform-provisioned Ubuntu 24.04 server.
