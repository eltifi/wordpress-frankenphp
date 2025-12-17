#!/bin/sh
set -e

# Function to generate a random string for WordPress salts/keys
generate_random_string() {
    openssl rand -base64 48 | tr -d '\n'
}

# Handle Caddy TLS Configuration
if [ -z "$CADDY_SERVER_TLS" ] || [ "$CADDY_SERVER_TLS" = "internal" ]; then
    echo "CADDY_SERVER_TLS is 'internal' or empty. Generating local self-signed certificate..."
    mkdir -p /data/caddy/certs
    if [ ! -f /data/caddy/certs/cert.pem ]; then
        openssl req -x509 -newkey rsa:4096 -keyout /data/caddy/certs/key.pem -out /data/caddy/certs/cert.pem -days 365 -nodes -subj "/CN=localhost"
        chmod 644 /data/caddy/certs/cert.pem
        chmod 600 /data/caddy/certs/key.pem
        chown www-data:www-data /data/caddy/certs/cert.pem /data/caddy/certs/key.pem
    fi
    # Write the TLS directive to a file that Caddy will import
    echo "tls /data/caddy/certs/cert.pem /data/caddy/certs/key.pem" > /data/caddy/tls_config
    echo "Using generated certificates."
else
    # Use the provided configuration (e.g. email or 'internal' if user explicitly wants Caddy internal CA)
    echo "tls $CADDY_SERVER_TLS" > /data/caddy/tls_config
    echo "Using provided TLS config: $CADDY_SERVER_TLS"
fi
chown www-data:www-data /data/caddy/tls_config

# Create custom PHP configuration to secure headers
mkdir -p /data/caddy/php
echo "expose_php = Off" > /data/caddy/php/custom.ini
chown -R www-data:www-data /data/caddy/php

# Generate WordPress authentication keys and salts if they are empty
if [ -z "$WORDPRESS_AUTH_KEY" ]; then
    export WORDPRESS_AUTH_KEY="$(generate_random_string)"
fi

if [ -z "$WORDPRESS_SECURE_AUTH_KEY" ]; then
    export WORDPRESS_SECURE_AUTH_KEY="$(generate_random_string)"
fi

if [ -z "$WORDPRESS_LOGGED_IN_KEY" ]; then
    export WORDPRESS_LOGGED_IN_KEY="$(generate_random_string)"
fi

if [ -z "$WORDPRESS_NONCE_KEY" ]; then
    export WORDPRESS_NONCE_KEY="$(generate_random_string)"
fi

if [ -z "$WORDPRESS_AUTH_SALT" ]; then
    export WORDPRESS_AUTH_SALT="$(generate_random_string)"
fi

if [ -z "$WORDPRESS_SECURE_AUTH_SALT" ]; then
    export WORDPRESS_SECURE_AUTH_SALT="$(generate_random_string)"
fi

if [ -z "$WORDPRESS_LOGGED_IN_SALT" ]; then
    export WORDPRESS_LOGGED_IN_SALT="$(generate_random_string)"
fi

if [ -z "$WORDPRESS_NONCE_SALT" ]; then
    export WORDPRESS_NONCE_SALT="$(generate_random_string)"
fi

# Wait for the database to be ready
echo "Waiting for database connection..."
# We use a loop to check if we can connect to the database
# Using wp db check would leverage wp-cli, but we need to make sure credentials are loaded.
# Note: frankenphp/caddy image might not have netcat (nc). We can use a simple php check or retry wp-cli.

max_retries=30
count=0
while ! wp db check > /dev/null 2>&1; do
    echo "Waiting for database connection... ($count/$max_retries)"
    sleep 2
    count=$((count+1))
    if [ $count -ge $max_retries ]; then
        echo "Error: timed out waiting for database connection."
        # We don't exit here to allow debugging, or we could exit 1
        break
    fi
done

# Check if WordPress is installed
if ! wp core is-installed; then
    echo "WordPress not installed. Installing..."
    wp core install \
        --url="${WORDPRESS_URL}" \
        --title="${WORDPRESS_TITLE}" \
        --admin_user="${WORDPRESS_ADMIN_USER}" \
        --admin_password="${WORDPRESS_ADMIN_PASSWORD}" \
        --admin_email="${WORDPRESS_ADMIN_EMAIL}" \
        --skip-email

    # Optional: Update permalink structure to something nice
    wp rewrite structure '/%postname%/'
    
    echo "WordPress installed successfully."
else
    echo "WordPress is already installed."
fi

# Execute the main command
exec "$@"
