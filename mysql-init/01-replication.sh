#!/bin/bash
set -e

echo "Creating replication user..."

mariadb -u root -p"${MYSQL_ROOT_PASSWORD}" <<-EOSQL
    CREATE USER IF NOT EXISTS '${MYSQL_REPLICATION_USER:-replicator}'@'%' IDENTIFIED BY '${MYSQL_REPLICATION_PASSWORD:-replica_secret}';
    GRANT REPLICATION SLAVE ON *.* TO '${MYSQL_REPLICATION_USER:-replicator}'@'%';
    FLUSH PRIVILEGES;
EOSQL
