#!/bin/bash

set -e

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}⚠️  WARNING: This will change root password and recreate piBoard database!${NC}"
echo

read -p "Enter current MariaDB root username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -p "Enter current MariaDB root password (press ENTER if empty): " -s CURRENT_PASSWORD
echo
echo

# Generate new password
NEW_PASSWORD="piboard_root_$(date +%s)"

echo -e "${YELLOW}Testing connection...${NC}"
if [ -z "$CURRENT_PASSWORD" ]; then
    # Try without password
    if ! mysql -u "$DB_USER" -e "SELECT 1;" &>/dev/null; then
        echo -e "${RED}✗ Failed to connect. Wrong username?${NC}"
        exit 1
    fi
else
    # Try with password
    if ! mysql -u "$DB_USER" -p"$CURRENT_PASSWORD" -e "SELECT 1;" &>/dev/null; then
        echo -e "${RED}✗ Failed to connect. Wrong password?${NC}"
        exit 1
    fi
fi
echo -e "${GREEN}✓ Connected${NC}"

echo -e "${YELLOW}Changing root password...${NC}"
if [ -z "$CURRENT_PASSWORD" ]; then
    mysql -u "$DB_USER" <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '$NEW_PASSWORD';
FLUSH PRIVILEGES;
EOF
else
    mysql -u "$DB_USER" -p"$CURRENT_PASSWORD" <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '$NEW_PASSWORD';
FLUSH PRIVILEGES;
EOF
fi
echo -e "${GREEN}✓ Password changed${NC}"

echo -e "${YELLOW}Dropping and recreating piBoard database...${NC}"
mysql -u root -p"$NEW_PASSWORD" <<EOF
DROP DATABASE IF EXISTS piBoard;
CREATE DATABASE piBoard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
FLUSH PRIVILEGES;
EOF
echo -e "${GREEN}✓ Database recreated${NC}"

if [ -f "./sqldatabase/database.sql" ]; then
    echo -e "${YELLOW}Importing schema...${NC}"
    mysql -u root -p"$NEW_PASSWORD" piBoard < ./sqldatabase/database.sql
    echo -e "${GREEN}✓ Schema imported${NC}"
else
    echo -e "${RED}✗ database.sql not found${NC}"
    exit 1
fi

echo -e "${YELLOW}Updating connection.php...${NC}"
cat > ./src/connection.php <<PHPEOF
<?php
\$hostname = "localhost";
\$username = "root";
\$password = "$NEW_PASSWORD";
\$databasename = "piBoard";

try {
    \$conn = new PDO("mysql:host=\$hostname;dbname=\$databasename;charset=utf8mb4", \$username, \$password);
    \$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException \$e) {
    die("Connection failed: " . \$e->getMessage());
}
PHPEOF
echo -e "${GREEN}✓ connection.php updated${NC}"

echo
echo -e "${GREEN}═════════════════════════════════════${NC}"
echo -e "${GREEN}       Reset Complete!${NC}"
echo -e "${GREEN}═════════════════════════════════════${NC}"
echo
echo -e "${GREEN}New root password: ${NC}$NEW_PASSWORD"
echo -e "${YELLOW}Create founder: ${NC}php scripts/create_founder.php username password"
echo
