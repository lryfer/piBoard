#!/bin/bash

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SRC_DIR="$SCRIPT_DIR/src"

print_success() {
    echo -e "${GREEN} $1${NC}"
}

print_error() {
  echo -e "${RED} $1${NC}"
}

print_warning() {
    echo -e "${YELLOW} $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

check_prerequisites() {
    print_info "Checking prerequisites..."

    if ! command_exists php; then
        print_error "PHP is not installed"
        exit 1
    fi

    print_success "PHP found: $(php -v | head -n 1)"
}

generate_config() {
    print_info "Generating .env configuration..."

    if [ -f "$SCRIPT_DIR/.env" ]; then
        print_warning ".env already exists"
        read -p "Overwrite? (y/n): " overwrite
        if [ "$overwrite" != "y" ]; then
            return
        fi
    fi

    read -p "DB host [localhost]: " DB_HOST
    DB_HOST=${DB_HOST:-localhost}

    read -p "DB name [piBoard]: " DB_NAME
    DB_NAME=${DB_NAME:-piBoard}

    read -p "DB user [root]: " DB_USER
    DB_USER=${DB_USER:-root}

    read -sp "DB password: " DB_PASSWORD
    echo

    cat > "$SCRIPT_DIR/.env" << EOF
DB_HOST=$DB_HOST
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASSWORD

APP_ENV=development
APP_DEBUG=true
EOF

    print_success ".env created at $SCRIPT_DIR/.env"
}

start_dev_server() {
    check_prerequisites

    if [ ! -f "$SCRIPT_DIR/.env" ]; then
        print_error ".env not found. Run option 0 first"
        exit 1
    fi

    read -p "Port [8000]: " PORT
    PORT=${PORT:-8000}

    cd "$SRC_DIR"
    print_success "Starting dev server on http://localhost:$PORT"
    print_info "Press Ctrl+C to stop"
    echo

    php -S localhost:$PORT
}

start_network_server() {
    check_prerequisites

    if [ ! -f "$SCRIPT_DIR/.env" ]; then
        print_error ".env not found. Run option 0 first"
        exit 1
    fi

    read -p "Port [8000]: " PORT
    PORT=${PORT:-8000}

    LOCAL_IP=$(hostname | awk '{print $1}')

    cd "$SRC_DIR"
    print_success "Starting server on http://$LOCAL_IP:$PORT"
    print_info "Press Ctrl+C to stop"
    echo

    php -S 0.0.0.0:$PORT
}

show_menu() {
    echo
    echo "0) Generate .env configuration"
    echo "1) Start network server"
    echo "2) Start development server"
    echo "3) Exit"
    echo
    read -p "Select [0-3]: " choice

    case $choice in
        0)
            generate_config
            ;;
        1)
            start_network_server
            ;;
        2)
            start_dev_server
            ;;
        3)
            print_info "Bye"
            exit 0
            ;;
        *)
            print_error "Invalid option"
            show_menu
            ;;
    esac
}

main() {
    clear
    show_menu
}

main
