variable "do_token" {
  description = "DigitalOcean API token. Set via TF_VAR_do_token env var — never commit."
  type        = string
  sensitive   = true
}

variable "ssh_key_name" {
  description = "Name of the SSH key already uploaded to your DO account."
  type        = string
  default     = "deployer"
}

variable "ssh_public_key" {
  description = "Full SSH public key string (ed25519 preferred). Used in cloud-init to create the ubuntu user."
  type        = string
}

variable "region" {
  description = "DO region slug. See: doctl compute region list"
  type        = string
  default     = "nyc3"
  # Other good options: sfo3 (US West), tor1 (Canada), ams3 (EU), sgp1 (Asia)
}

variable "droplet_size" {
  description = "Droplet size slug. See: doctl compute size list"
  type        = string
  default     = "s-2vcpu-4gb"
  # Hetzner equivalent mapping:
  #   s-1vcpu-1gb  (~CX11,  1 vCPU, 1 GB RAM)  — dev only
  #   s-2vcpu-4gb  (~CX21,  2 vCPU, 4 GB RAM)  — small production
  #   s-4vcpu-8gb  (~CX31,  4 vCPU, 8 GB RAM)  — medium production
  #   s-8vcpu-16gb (~CX41,  8 vCPU, 16 GB RAM) — high-traffic
}

variable "domain" {
  description = "Primary domain for the WooCommerce store."
  type        = string
  default     = "shop.example.com"
}

variable "environment" {
  description = "Deployment environment label."
  type        = string
  default     = "prod"
  validation {
    condition     = contains(["prod", "staging"], var.environment)
    error_message = "Must be 'prod' or 'staging'."
  }
}

variable "manage_dns" {
  description = "Set to true to let Terraform manage DNS via DO. Set false if DNS is managed by Cloudflare or externally."
  type        = bool
  default     = false
}

variable "netdata_allowed_ips" {
  description = "IPs allowed to reach Netdata (port 19999). Use your VPN or office IP. Never expose to 0.0.0.0/0."
  type        = list(string)
  default     = []
  # Example: ["203.0.113.10/32"]
}
