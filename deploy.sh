#!/bin/bash

# Payroll Management System - Deployment Script
# Production Deployment Automation

set -e

# Configuration
APP_NAME="payroll-system"
APP_DIR="/var/www/payroll-system"
BACKUP_DIR="/var/backups/payroll-system"
LOG_DIR="/var/log/payroll-system"
DEPLOY_USER="www-data"
DEPLOY_GROUP="www-data"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root"
    fi
}

# Create necessary directories
create_directories() {
    log "Creating necessary directories..."
    
    mkdir -p $APP_DIR
    mkdir -p $BACKUP_DIR
    mkdir -p $LOG_DIR
    mkdir -p /var/log/nginx
    mkdir -p /var/log/supervisor
    mkdir -p /var/log/cron
    
    chown -R $DEPLOY_USER:$DEPLOY_GROUP $APP_DIR
    chown -R $DEPLOY_USER:$DEPLOY_GROUP $BACKUP_DIR
    chown -R $DEPLOY_USER:$DEPLOY_GROUP $LOG_DIR
}

# Backup current installation
backup_current() {
    if [ -d "$APP_DIR" ]; then
        log "Creating backup of current installation..."
        
        BACKUP_FILE="$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz"
        tar -czf $BACKUP_FILE -C /var/www payroll-system
        
        log "Backup created: $BACKUP_FILE"
    fi
}

# Install system dependencies
install_dependencies() {
    log "Installing system dependencies..."
    
    # Update package list
    apt-get update
    
    # Install required packages
    apt-get install -y \
        nginx \
        php8.2-fpm \
        php8.2-mysql \
        php8.2-redis \
        php8.2-curl \
        php8.2-gd \
        php8.2-mbstring \
        php8.2-xml \
        php8.2-zip \
        php8.2-bcmath \
        php8.2-intl \
        php8.2-opcache \
        mysql-server \
        redis-server \
        supervisor \
        git \
        curl \
        unzip \
        certbot \
        python3-certbot-nginx \
        ufw \
        fail2ban \
        htop \
        nload \
        iotop \
        logrotate
}

# Configure PHP-FPM
configure_php_fpm() {
    log "Configuring PHP-FPM..."
    
    # Copy PHP-FPM configuration
    cp php-fpm.conf /etc/php/8.2/fpm/pool.d/payroll-system.conf
    
    # Restart PHP-FPM
    systemctl restart php8.2-fpm
    systemctl enable php8.2-fpm
}

# Configure Nginx
configure_nginx() {
    log "Configuring Nginx..."
    
    # Copy Nginx configuration
    cp nginx.conf /etc/nginx/sites-available/payroll-system
    
    # Enable site
    ln -sf /etc/nginx/sites-available/payroll-system /etc/nginx/sites-enabled/
    
    # Remove default site
    rm -f /etc/nginx/sites-enabled/default
    
    # Test configuration
    nginx -t
    
    # Restart Nginx
    systemctl restart nginx
    systemctl enable nginx
}

# Configure Supervisor
configure_supervisor() {
    log "Configuring Supervisor..."
    
    # Copy Supervisor configuration
    cp supervisor.conf /etc/supervisor/conf.d/payroll-system.conf
    
    # Create log directory
    mkdir -p /var/log/supervisor
    
    # Reload Supervisor
    supervisorctl reread
    supervisorctl update
    supervisorctl start payroll-system:*
}

# Configure Cron
configure_cron() {
    log "Configuring Cron jobs..."
    
    # Copy crontab configuration
    crontab crontab.txt
    
    # Create log directory
    mkdir -p /var/log/cron
}

# Setup SSL Certificate
setup_ssl() {
    log "Setting up SSL certificate..."
    
    # Stop Nginx temporarily
    systemctl stop nginx
    
    # Obtain SSL certificate
    certbot certonly --standalone -d your-domain.com -d www.your-domain.com
    
    # Start Nginx
    systemctl start nginx
    
    # Setup auto-renewal
    echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
}

# Configure Firewall
configure_firewall() {
    log "Configuring firewall..."
    
    # Enable UFW
    ufw --force enable
    
    # Allow SSH
    ufw allow ssh
    
    # Allow HTTP and HTTPS
    ufw allow 80
    ufw allow 443
    
    # Allow specific ports for monitoring
    ufw allow 22
    ufw allow 3306/tcp
    ufw allow 6379/tcp
    
    # Enable logging
    ufw logging on
}

# Configure Fail2ban
configure_fail2ban() {
    log "Configuring Fail2ban..."
    
    # Create custom jail for Nginx
    cat > /etc/fail2ban/jail.local << EOF
[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log

[nginx-botsearch]
enabled = true
filter = nginx-botsearch
port = http,https
logpath = /var/log/nginx/access.log

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
port = http,https
logpath = /var/log/nginx/error.log
EOF
    
    # Restart Fail2ban
    systemctl restart fail2ban
    systemctl enable fail2ban
}

# Setup Log Rotation
setup_log_rotation() {
    log "Setting up log rotation..."
    
    cat > /etc/logrotate.d/payroll-system << EOF
/var/log/payroll-system/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload nginx
        systemctl reload php8.2-fpm
    endscript
}

/var/log/nginx/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload nginx
    endscript
}

/var/log/supervisor/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 root root
    postrotate
        supervisorctl reread
        supervisorctl update
    endscript
}
EOF
}

# Setup Monitoring
setup_monitoring() {
    log "Setting up monitoring..."
    
    # Create monitoring script
    cat > /usr/local/bin/monitor-payroll-system.sh << 'EOF'
#!/bin/bash

# Payroll System Monitoring Script

LOG_FILE="/var/log/payroll-system/monitoring.log"
ALERT_EMAIL="admin@your-domain.com"

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$(date): Disk usage is ${DISK_USAGE}%" >> $LOG_FILE
    echo "Disk usage alert: ${DISK_USAGE}%" | mail -s "Payroll System Alert" $ALERT_EMAIL
fi

# Check memory usage
MEMORY_USAGE=$(free | awk 'NR==2{printf "%.2f", $3*100/$2}')
if (( $(echo "$MEMORY_USAGE > 85" | bc -l) )); then
    echo "$(date): Memory usage is ${MEMORY_USAGE}%" >> $LOG_FILE
    echo "Memory usage alert: ${MEMORY_USAGE}%" | mail -s "Payroll System Alert" $ALERT_EMAIL
fi

# Check PHP-FPM status
if ! systemctl is-active --quiet php8.2-fpm; then
    echo "$(date): PHP-FPM is down" >> $LOG_FILE
    echo "PHP-FPM is down" | mail -s "Payroll System Alert" $ALERT_EMAIL
    systemctl restart php8.2-fpm
fi

# Check Nginx status
if ! systemctl is-active --quiet nginx; then
    echo "$(date): Nginx is down" >> $LOG_FILE
    echo "Nginx is down" | mail -s "Payroll System Alert" $ALERT_EMAIL
    systemctl restart nginx
fi

# Check MySQL status
if ! systemctl is-active --quiet mysql; then
    echo "$(date): MySQL is down" >> $LOG_FILE
    echo "MySQL is down" | mail -s "Payroll System Alert" $ALERT_EMAIL
    systemctl restart mysql
fi

# Check Redis status
if ! systemctl is-active --quiet redis-server; then
    echo "$(date): Redis is down" >> $LOG_FILE
    echo "Redis is down" | mail -s "Payroll System Alert" $ALERT_EMAIL
    systemctl restart redis-server
fi

# Check Supervisor status
if ! systemctl is-active --quiet supervisor; then
    echo "$(date): Supervisor is down" >> $LOG_FILE
    echo "Supervisor is down" | mail -s "Payroll System Alert" $ALERT_EMAIL
    systemctl restart supervisor
fi
EOF
    
    chmod +x /usr/local/bin/monitor-payroll-system.sh
    
    # Add to crontab
    echo "*/5 * * * * /usr/local/bin/monitor-payroll-system.sh" | crontab -
}

# Setup Backup Script
setup_backup() {
    log "Setting up backup script..."
    
    cat > /usr/local/bin/backup-payroll-system.sh << 'EOF'
#!/bin/bash

# Payroll System Backup Script

BACKUP_DIR="/var/backups/payroll-system"
APP_DIR="/var/www/payroll-system"
DB_NAME="payroll_system"
DB_USER="payroll_user"
DB_PASS="strong_password_here"
DATE=$(date +%Y%m%d-%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db-backup-$DATE.sql
gzip $BACKUP_DIR/db-backup-$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/app-backup-$DATE.tar.gz -C /var/www payroll-system

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
EOF
    
    chmod +x /usr/local/bin/backup-payroll-system.sh
    
    # Add to crontab
    echo "0 2 * * * /usr/local/bin/backup-payroll-system.sh" | crontab -
}

# Setup Health Check
setup_health_check() {
    log "Setting up health check..."
    
    cat > /usr/local/bin/health-check.sh << 'EOF'
#!/bin/bash

# Payroll System Health Check

HEALTH_FILE="/var/www/payroll-system/public/health"
LOG_FILE="/var/log/payroll-system/health.log"

# Check if application is responding
if curl -f -s http://localhost/health > /dev/null; then
    echo "$(date): Health check passed" >> $LOG_FILE
    echo "healthy" > $HEALTH_FILE
else
    echo "$(date): Health check failed" >> $LOG_FILE
    echo "unhealthy" > $HEALTH_FILE
fi
EOF
    
    chmod +x /usr/local/bin/health-check.sh
    
    # Add to crontab
    echo "*/10 * * * * /usr/local/bin/health-check.sh" | crontab -
}

# Setup Performance Optimization
setup_performance() {
    log "Setting up performance optimization..."
    
    # Optimize MySQL
    cat > /etc/mysql/mysql.conf.d/optimization.cnf << EOF
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_size = 128M
query_cache_type = 1
max_connections = 200
thread_cache_size = 50
table_open_cache = 2000
EOF
    
    # Optimize Redis
    cat >> /etc/redis/redis.conf << EOF
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
EOF
    
    # Restart services
    systemctl restart mysql
    systemctl restart redis-server
}

# Setup Security
setup_security() {
    log "Setting up security..."
    
    # Secure MySQL
    mysql_secure_installation
    
    # Set file permissions
    find $APP_DIR -type f -exec chmod 644 {} \;
    find $APP_DIR -type d -exec chmod 755 {} \;
    chmod -R 775 $APP_DIR/storage
    chmod -R 775 $APP_DIR/bootstrap/cache
    
    # Set ownership
    chown -R $DEPLOY_USER:$DEPLOY_GROUP $APP_DIR
    
    # Disable root SSH login
    sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
    systemctl restart ssh
}

# Main deployment function
deploy() {
    log "Starting Payroll System deployment..."
    
    check_root
    create_directories
    backup_current
    install_dependencies
    configure_php_fpm
    configure_nginx
    configure_supervisor
    configure_cron
    setup_ssl
    configure_firewall
    configure_fail2ban
    setup_log_rotation
    setup_monitoring
    setup_backup
    setup_health_check
    setup_performance
    setup_security
    
    log "Deployment completed successfully!"
    log "Please update your domain name in the configuration files."
    log "Access your application at: https://your-domain.com"
}

# Rollback function
rollback() {
    log "Rolling back to previous version..."
    
    # Find latest backup
    LATEST_BACKUP=$(ls -t $BACKUP_DIR/*.tar.gz | head -1)
    
    if [ -z "$LATEST_BACKUP" ]; then
        error "No backup found for rollback"
    fi
    
    # Stop services
    systemctl stop nginx
    systemctl stop php8.2-fpm
    systemctl stop supervisor
    
    # Restore from backup
    rm -rf $APP_DIR
    tar -xzf $LATEST_BACKUP -C /var/www
    
    # Restart services
    systemctl start php8.2-fpm
    systemctl start nginx
    supervisorctl start payroll-system:*
    
    log "Rollback completed successfully!"
}

# Show usage
usage() {
    echo "Usage: $0 {deploy|rollback|status}"
    echo ""
    echo "Commands:"
    echo "  deploy   - Deploy the Payroll System"
    echo "  rollback - Rollback to previous version"
    echo "  status   - Show system status"
    exit 1
}

# Show status
status() {
    log "System Status:"
    echo ""
    
    echo "Services:"
    systemctl status nginx --no-pager -l
    echo ""
    systemctl status php8.2-fpm --no-pager -l
    echo ""
    systemctl status mysql --no-pager -l
    echo ""
    systemctl status redis-server --no-pager -l
    echo ""
    systemctl status supervisor --no-pager -l
    echo ""
    
    echo "Supervisor Processes:"
    supervisorctl status
    echo ""
    
    echo "Disk Usage:"
    df -h
    echo ""
    
    echo "Memory Usage:"
    free -h
    echo ""
    
    echo "Recent Logs:"
    tail -20 /var/log/payroll-system/monitoring.log
}

# Main script logic
case "$1" in
    deploy)
        deploy
        ;;
    rollback)
        rollback
        ;;
    status)
        status
        ;;
    *)
        usage
        ;;
esac

exit 0 