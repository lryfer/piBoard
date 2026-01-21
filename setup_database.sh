#!/bin/bash

###############################################################################
# piBoard - Simple Database Setup
# Use this if you already know your MySQL/MariaDB root password
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${YELLOW}ℹ $1${NC}"; }

echo "╔═══════════════════════════════════════════╗"
echo "║  piBoard Database Setup                   ║"
echo "╚═══════════════════════════════════════════╝"
echo

# Get MySQL credentials
read -p "MySQL root username [root]: " DB_ROOT_USER
DB_ROOT_USER=${DB_ROOT_USER:-root}

read -sp "MySQL root password: " DB_ROOT_PASS
echo
echo

# Test connection
print_info "Testing MySQL connection..."
if ! mysql -u "$DB_ROOT_USER" -p"$DB_ROOT_PASS" -e "SELECT 1" > /dev/null 2>&1; then
    print_error "Cannot connect to MySQL. Wrong password?"
    echo
    print_info "If you forgot your password, run: sudo ./reset_mariadb.sh"
    exit 1
fi
print_success "MySQL connection successful"

# Drop old database if exists
print_info "Removing old databases..."
mysql -u "$DB_ROOT_USER" -p"$DB_ROOT_PASS" <<EOF
DROP DATABASE IF EXISTS NotForCianFa;
DROP DATABASE IF EXISTS piBoard;
EOF
print_success "Old databases removed"

# Create new database
print_info "Creating piBoard database..."
mysql -u "$DB_ROOT_USER" -p"$DB_ROOT_PASS" <<EOF
CREATE DATABASE piBoard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF
print_success "piBoard database created"

# Import schema
if [ -f "./sqldatabase/database.sql" ]; then
    print_info "Importing database schema..."
    mysql -u "$DB_ROOT_USER" -p"$DB_ROOT_PASS" piBoard < ./sqldatabase/database.sql
    print_success "Schema imported successfully"
else
    print_error "database.sql not found!"
    exit 1
fi

# Update connection.php
print_info "Updating connection.php..."
cat > ./src/connection.php <<PHPEOF
<?php
\$hostname = "localhost";
\$username = "$DB_ROOT_USER";
\$password = "$DB_ROOT_PASS";
\$databasename = "piBoard";

try {
    \$conn = new PDO("mysql:host=\$hostname;dbname=\$databasename;charset=utf8mb4", \$username, \$password);
    \$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException \$e) {
    die("Connection failed: " . \$e->getMessage());
}
PHPEOF
print_success "connection.php updated"

# Verify setup
print_info "Verifying setup..."
TABLES=$(mysql -u "$DB_ROOT_USER" -p"$DB_ROOT_PASS" piBoard -e "SHOW TABLES;" | wc -l)
print_success "Found $((TABLES - 1)) tables"

ADMIN_EXISTS=$(mysql -u "$DB_ROOT_USER" -p"$DB_ROOT_PASS" piBoard -e "SELECT COUNT(*) FROM Users WHERE Nickname='admin';" -s -N)
if [ "$ADMIN_EXISTS" -eq 1 ]; then
    print_success "Admin account created"
else
    print_error "Admin account not found!"
fi

echo
echo "╔═══════════════════════════════════════════╗"
echo "║          Setup Complete!                  ║"
echo "╚═══════════════════════════════════════════╝"
echo
print_success "Database 'piBoard' is ready"
print_success "Default admin: admin / piBoard2024!"
echo
print_info "To start the development server:"
echo "  cd src && php -S localhost:8000"
echo
print_info "Then visit: http://localhost:8000"
echo
