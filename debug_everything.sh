#!/bin/bash
# sLMS Comprehensive Debug Script
# Usage: bash debug_everything.sh

set -e

print_section() {
  echo -e "\n=============================="
  echo -e "$1"
  echo -e "==============================\n"
}

print_section "1. SYSTEM INFO"
uname -a
echo "Hostname: $(hostname)"
echo "Date: $(date)"

print_section "2. APACHE STATUS & CONFIGURATION"
if command -v apache2 > /dev/null; then
  echo "Apache version:"; apache2 -v
  echo "\nChecking Apache service status..."
  if systemctl list-unit-files | grep -q apache2; then
    systemctl status apache2 || true
  else
    echo "Apache2 service not found in systemctl."
  fi
  echo "\nApache listening ports:"
  sudo netstat -tulnp | grep ':80\|:8081' || echo "No Apache ports open."
  echo "\nApache config test:"
  sudo apache2ctl configtest || true
else
  echo "Apache is not installed."
fi

print_section "3. PHP STATUS"
if command -v php > /dev/null; then
  php -v
  echo "\nLoaded PHP modules (pdo, mysql, json, curl, snmp):"
  php -m | grep -E "(pdo|mysql|json|curl|snmp)"
else
  echo "PHP is not installed."
fi

print_section "4. DATABASE CONNECTION TEST"
if [ -f config.php ]; then
  php -r "require 'config.php'; get_pdo(); echo 'DB OK';" 2>&1 || echo "Database connection failed."
else
  echo "config.php not found in current directory."
fi

print_section "5. PERMISSIONS CHECK"
ls -ld /var/www/html/slms
ls -l /var/www/html/slms | head -20

print_section "6. LOGS CHECK"
if [ -d logs ]; then
  echo "Listing logs directory:"
  ls -lh logs/
  echo "\nLast 20 lines of each log file:"
  for f in logs/*.log; do
    echo "\n--- $f ---"
    tail -n 20 "$f"
  done
else
  echo "logs/ directory not found."
fi

print_section "7. NETWORK & FIREWALL"
IP=$(hostname -I | awk '{print $1}')
echo "Server IP: $IP"
echo "\nTesting local HTTP access:"
curl -I http://localhost/ || echo "Localhost HTTP failed."
echo "\nTesting network HTTP access:"
curl -I http://$IP/ || echo "Network HTTP failed."
echo "\nFirewall status:"
if command -v ufw > /dev/null; then
  sudo ufw status verbose
else
  echo "ufw not installed."
fi

print_section "8. DOCKER/KUBERNETES STATUS (if applicable)"
if command -v docker > /dev/null; then
  echo "Docker containers:"
  docker ps -a
else
  echo "Docker not installed."
fi
if command -v kubectl > /dev/null; then
  echo "Kubernetes pods:"
  kubectl get pods -A
  echo "Kubernetes services:"
  kubectl get services -A
else
  echo "kubectl not installed."
fi

print_section "9. SUMMARY"
echo "If you see any errors above, please copy the relevant section and share it for further analysis." 