# Payroll System Deployment Guide

## Overview
This guide provides step-by-step instructions for deploying the Payroll Management System to production environment.

## Prerequisites

### System Requirements
- **Server**: Ubuntu 20.04 LTS or higher
- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0 or MariaDB 10.5
- **Web Server**: Nginx or Apache
- **SSL Certificate**: Let's Encrypt or commercial certificate
- **Domain**: Registered domain name
- **Memory**: Minimum 2GB RAM
- **Storage**: Minimum 20GB SSD

### Software Dependencies
- Git
- Composer
- Node.js & NPM (for asset compilation)
- Supervisor (for queue management)
- Redis (optional, for caching)

## Step 1: Server Setup

### 1.1 Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Install Required Packages
```bash
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-redis git composer supervisor
```

### 1.3 Configure PHP
```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Update the following settings:
```ini
upload_max_filesize = 64M
post_max_size = 64M
memory_limit = 512M
max_execution_time = 300
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

## Step 2: Database Setup

### 2.1 Secure MySQL Installation
```bash
sudo mysql_secure_installation
```

### 2.2 Create Database and User
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE payroll_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'payroll_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON payroll_system.* TO 'payroll_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 3: Application Deployment

### 3.1 Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/your-repo/payroll-system.git
sudo chown -R www-data:www-data payroll-system
sudo chmod -R 755 payroll-system
```

### 3.2 Install Dependencies
```bash
cd payroll-system
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 3.3 Environment Configuration
```bash
cp .env.example .env
nano .env
```

Update the following settings:
```env
APP_NAME="Payroll Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payroll_system
DB_USERNAME=payroll_user
DB_PASSWORD=strong_password_here

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3.4 Generate Application Key
```bash
php artisan key:generate
```

### 3.5 Run Migrations and Seeders
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 3.6 Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

## Step 4: Web Server Configuration

### 4.1 Nginx Configuration
Create Nginx configuration file:
```bash
sudo nano /etc/nginx/sites-available/payroll-system
```

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/payroll-system/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/payroll-system /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Step 5: SSL Certificate Setup

### 5.1 Install Certbot
```bash
sudo apt install certbot python3-certbot-nginx
```

### 5.2 Obtain SSL Certificate
```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### 5.3 Auto-renewal Setup
```bash
sudo crontab -e
```

Add the following line:
```
0 12 * * * /usr/bin/certbot renew --quiet
```

## Step 6: Queue Management

### 6.1 Configure Supervisor
Create supervisor configuration:
```bash
sudo nano /etc/supervisor/conf.d/payroll-worker.conf
```

```ini
[program:payroll-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/payroll-system/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/payroll-system/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start payroll-worker:*
```

## Step 7: Monitoring and Logging

### 7.1 Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/payroll-system
```

```conf
/var/www/payroll-system/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    notifempty
    create 644 www-data www-data
}
```

### 7.2 Setup Monitoring (Optional)
Install monitoring tools:
```bash
sudo apt install htop iotop nethogs
```

## Step 8: Backup Configuration

### 8.1 Database Backup Script
Create backup script:
```bash
sudo nano /var/www/payroll-system/backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/payroll-system"
DB_NAME="payroll_system"
DB_USER="payroll_user"
DB_PASS="strong_password_here"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Application files backup
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz /var/www/payroll-system

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

Make it executable:
```bash
chmod +x /var/www/payroll-system/backup.sh
```

### 8.2 Setup Automated Backups
```bash
sudo crontab -e
```

Add the following line:
```
0 2 * * * /var/www/payroll-system/backup.sh
```

## Step 9: Security Hardening

### 9.1 Configure Firewall
```bash
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 9.2 File Permissions
```bash
sudo chown -R www-data:www-data /var/www/payroll-system
sudo chmod -R 755 /var/www/payroll-system
sudo chmod -R 775 /var/www/payroll-system/storage
sudo chmod -R 775 /var/www/payroll-system/bootstrap/cache
```

### 9.3 Disable Directory Listing
Add to Nginx configuration:
```nginx
location ~ /\. {
    deny all;
}
```

## Step 10: Performance Optimization

### 10.1 Install Redis
```bash
sudo apt install redis-server
sudo systemctl enable redis-server
```

### 10.2 Configure OPcache
```bash
sudo nano /etc/php/8.2/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 10.3 Configure Nginx for Performance
Add to Nginx configuration:
```nginx
# Gzip compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

# Browser caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## Step 11: Post-Deployment Checklist

### 11.1 Verify Installation
- [ ] Application loads correctly
- [ ] Database connection works
- [ ] File uploads work
- [ ] Email sending works
- [ ] Queue processing works
- [ ] SSL certificate is valid
- [ ] Backup script runs successfully

### 11.2 Performance Testing
- [ ] Page load times are acceptable
- [ ] Database queries are optimized
- [ ] File upload limits are appropriate
- [ ] Memory usage is within limits

### 11.3 Security Testing
- [ ] HTTPS redirect works
- [ ] Directory listing is disabled
- [ ] File permissions are correct
- [ ] Firewall is configured
- [ ] Regular security updates are enabled

## Troubleshooting

### Common Issues

#### 1. 502 Bad Gateway
- Check PHP-FPM status: `sudo systemctl status php8.2-fpm`
- Check Nginx error logs: `sudo tail -f /var/log/nginx/error.log`

#### 2. Database Connection Issues
- Verify database credentials in `.env`
- Check MySQL service: `sudo systemctl status mysql`

#### 3. File Permission Issues
- Ensure proper ownership: `sudo chown -R www-data:www-data /var/www/payroll-system`
- Check storage directory permissions

#### 4. Queue Not Processing
- Check supervisor status: `sudo supervisorctl status`
- Check queue logs: `tail -f /var/www/payroll-system/storage/logs/worker.log`

## Maintenance

### Regular Tasks
- Monitor disk space usage
- Check application logs for errors
- Update system packages monthly
- Test backup restoration quarterly
- Monitor SSL certificate expiration
- Review and update security settings

### Updates
- Keep Laravel and dependencies updated
- Monitor for security patches
- Test updates in staging environment first
- Maintain backup before major updates

## Support

For technical support or questions about deployment:
- Check application logs in `/var/www/payroll-system/storage/logs/`
- Review Laravel documentation
- Contact system administrator

---

**Note**: This deployment guide assumes a standard Ubuntu server setup. Adjust configurations based on your specific server environment and requirements. 