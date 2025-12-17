#!/bin/sh
set -e

# Dump the database to a file in the volume that will be backed up
# We use --single-transaction for a consistent dump without locking InnoDB tables
# We use --quick to retrieve rows row-by-row
echo "Dumping database..."
# Ensure the directory exists (offen image should have it, but safety first)
mkdir -p /backup/source

mysqldump \
  --host="$WORDPRESS_DB_HOST_NAME" \
  --user="$WORDPRESS_DB_USER" \
  --password="$WORDPRESS_DB_PASSWORD" \
  --single-transaction \
  --quick \
  "$WORDPRESS_DB_NAME" > /backup/source/database.sql

echo "Database dump complete."
