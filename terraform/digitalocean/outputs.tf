output "droplet_ip" {
  description = "Public IPv4 address of the WooCommerce Droplet."
  value       = digitalocean_droplet.web.ipv4_address
}

output "droplet_id" {
  description = "DO Droplet ID (useful for snapshots and resizing)."
  value       = digitalocean_droplet.web.id
}

output "droplet_urn" {
  description = "Droplet URN (used for project resource assignment)."
  value       = digitalocean_droplet.web.urn
}

output "ansible_command" {
  description = "Ansible command to run after Terraform apply."
  value       = "cd ../../provisioning && ansible-playbook -i inventory/hosts site.yml"
}
