#!/bin/bash

# Create Apache config snippet for metrics IP whitelist
if [ -n "$METRICS_ALLOWED_IPS" ]; then
    cat > /etc/apache2/conf-available/metrics-access.conf <<EOF
# Metrics IP whitelist
<Location /metrics>
    Require ip $METRICS_ALLOWED_IPS 127.0.0.1
</Location>
EOF
    a2enconf metrics-access > /dev/null 2>&1
else
    # If no IPs specified, deny all access to metrics
    cat > /etc/apache2/conf-available/metrics-access.conf <<EOF
# Metrics access denied (no IPs configured)
<Location /metrics>
    Require all denied
</Location>
EOF
    a2enconf metrics-access > /dev/null 2>&1
fi

# Start Apache
exec apache2-foreground
