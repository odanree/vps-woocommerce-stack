terraform {
  required_version = ">= 1.6"
  required_providers {
    digitalocean = {
      source  = "digitalocean/digitalocean"
      version = "~> 2.0"
    }
  }
}

provider "digitalocean" {
  token = var.do_token
}

# ─────────────────────────────────────────
# SSH key — must already exist in your DO account
# ─────────────────────────────────────────
data "digitalocean_ssh_key" "deployer" {
  name = var.ssh_key_name
}

# ─────────────────────────────────────────
# Project (organizes resources in DO dashboard)
# ─────────────────────────────────────────
resource "digitalocean_project" "woocommerce" {
  name        = "woocommerce-${var.environment}"
  description = "WooCommerce production stack"
  purpose     = "Web Application"
  environment = var.environment == "prod" ? "Production" : "Staging"
}

# ─────────────────────────────────────────
# Droplet
# ─────────────────────────────────────────
resource "digitalocean_droplet" "web" {
  name     = "woocommerce-${var.environment}"
  region   = var.region
  size     = var.droplet_size
  image    = "ubuntu-24-04-x64"
  ssh_keys = [data.digitalocean_ssh_key.deployer.id]

  # cloud-init: create the 'ubuntu' user so Ansible can connect as non-root
  # DO Ubuntu droplets default to root; we add a sudo user at boot.
  user_data = <<-EOT
    #cloud-config
    users:
      - name: ubuntu
        groups: sudo
        shell: /bin/bash
        sudo: ALL=(ALL) NOPASSWD:ALL
        ssh_authorized_keys:
          - ${var.ssh_public_key}
    package_update: true
    package_upgrade: false
  EOT

  tags = ["woocommerce", var.environment]
}

# ─────────────────────────────────────────
# Cloud-level firewall (applied before Droplet boots — defense-in-depth)
# UFW also runs on the Droplet (Ansible security role).
# This cloud firewall is managed by DO's edge network, not the OS.
# ─────────────────────────────────────────
resource "digitalocean_firewall" "web" {
  name    = "woocommerce-${var.environment}-fw"
  droplet_ids = [digitalocean_droplet.web.id]

  # Inbound: allow SSH, HTTP, HTTPS only
  inbound_rule {
    protocol         = "tcp"
    port_range       = "22"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }

  inbound_rule {
    protocol         = "tcp"
    port_range       = "80"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }

  inbound_rule {
    protocol         = "tcp"
    port_range       = "443"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }

  # Netdata (19999) — restrict to your IP only; never expose to internet
  inbound_rule {
    protocol         = "tcp"
    port_range       = "19999"
    source_addresses = var.netdata_allowed_ips
  }

  # Outbound: allow all (OS updates, Certbot ACME, NMI/Stripe API calls)
  outbound_rule {
    protocol              = "tcp"
    port_range            = "all"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }
  outbound_rule {
    protocol              = "udp"
    port_range            = "all"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }
  outbound_rule {
    protocol              = "icmp"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }
}

# ─────────────────────────────────────────
# DNS (optional — remove if managing DNS externally via Cloudflare)
# ─────────────────────────────────────────
resource "digitalocean_domain" "main" {
  count      = var.manage_dns ? 1 : 0
  name       = var.domain
  ip_address = digitalocean_droplet.web.ipv4_address
}

resource "digitalocean_record" "www" {
  count  = var.manage_dns ? 1 : 0
  domain = digitalocean_domain.main[0].name
  type   = "A"
  name   = "www"
  value  = digitalocean_droplet.web.ipv4_address
  ttl    = 300
}

# ─────────────────────────────────────────
# Project membership
# ─────────────────────────────────────────
resource "digitalocean_project_resources" "woocommerce" {
  project = digitalocean_project.woocommerce.id
  resources = [
    digitalocean_droplet.web.urn,
  ]
}

# ─────────────────────────────────────────
# Generate Ansible inventory from Terraform output
# ─────────────────────────────────────────
resource "local_file" "ansible_inventory" {
  filename = "${path.module}/../../provisioning/inventory/hosts"
  content  = <<-EOT
    [webservers]
    ${digitalocean_droplet.web.ipv4_address} ansible_user=ubuntu ansible_ssh_private_key_file=~/.ssh/id_ed25519

    [webservers:vars]
    ansible_python_interpreter=/usr/bin/python3
  EOT
}
