#!/bin/bash
set -e
set -o pipefail

echo "Starting Replica Initialization..."

# Define credentials (fallbacks for safety)
USER=${MYSQL_REPLICATION_USER:-replicator}
PASS=${MYSQL_REPLICATION_PASSWORD:-replica_secret}
ROOT_PASS=${MYSQL_ROOT_PASSWORD}

# Wait for primary to be ready
echo "Waiting for Primary database connection..."
until mariadb-admin ping -h mysql -u root -p"${ROOT_PASS}" --silent; do
    echo "Waiting for primary..."
    sleep 5
done

echo "Setting Master Connection Details..."
mariadb -u root -p"${ROOT_PASS}" <<-EOSQL
    STOP SLAVE;
    CHANGE MASTER TO
    MASTER_HOST='mysql',
    MASTER_USER='${USER}',
    MASTER_PASSWORD='${PASS}',
    MASTER_CONNECT_RETRY=10;
EOSQL

echo "Cloning Data from Primary..."
# --master-data=1 writes 'CHANGE MASTER TO MASTER_LOG_FILE=..., MASTER_LOG_POS=...' into the dump
# This automatically positions the slave correctly.
mariadb-dump \
  -h mysql \
  -u "${USER}" \
  -p"${PASS}" \
  --master-data=1 \
  --single-transaction \
  --all-databases \
  | mariadb -u root -p"${ROOT_PASS}"

echo "Starting Slave..."
mariadb -u root -p"${ROOT_PASS}" -e "START SLAVE;"

echo "Replica Initialization Complete."
