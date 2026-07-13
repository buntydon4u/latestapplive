# Web Server Configuration Guide

## Apache Configuration

### Enable Required Modules

```bash
# Enable mod_rewrite for SPA routing
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod proxy
sudo a2enmod proxy_http

# Restart Apache
sudo systemctl restart apache2
```

### Apache VirtualHost Configuration

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    
    # Document root for React build
    DocumentRoot /var/www/xch555/dist
    
    # React SPA - Route all requests to index.html
    <Directory /var/www/xch555/dist>
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.html$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.html [L]
        
        AllowOverride All
        Require all granted
    </Directory>
    
    # API Proxy - Route /api requests to PHP backend
    <Directory /var/www/xch555/api>
        RewriteEngine On
        RewriteBase /
        AllowOverride All
        Require all granted
    </Directory>
    
    # Forward /api requests to PHP
    ProxyPass /api http://localhost:8001/api
    ProxyPassReverse /api http://localhost:8001/api
    
    # CORS Headers
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
    
    # Compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript
        AddOutputFilterByType DEFLATE application/json application/javascript application/xml+rss application/rss+xml
    </IfModule>
    
    # Caching
    <FilesMatch ".(jpg|jpeg|png|gif|ico|css|js)$">
        Header set Cache-Control "max-age=31536000, public"
    </FilesMatch>
    
    <FilesMatch "^$|.(html)$">
        Header set Cache-Control "max-age=0, public, must-revalidate"
    </FilesMatch>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/xch555-error.log
    CustomLog ${APACHE_LOG_DIR}/xch555-access.log combined
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

# HTTPS Configuration
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    
    DocumentRoot /var/www/xch555/dist
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
    
    # [Include same configuration as above]
</VirtualHost>
```

### .htaccess for React SPA

If VirtualHost rewrite is not available, use `.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Don't rewrite if request is for a file
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]
    
    # Don't rewrite if request is for a directory
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]
    
    # Rewrite all other requests to index.html
    RewriteRule ^ index.html [QSA,L]
</IfModule>

<IfModule mod_headers.c>
    # CORS headers
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
</IfModule>

# Cache management
<IfModule mod_expires.c>
    ExpiresActive On
    
    # Images and fonts - long cache
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    
    # CSS and JS - medium cache
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    
    # HTML - no cache
    ExpiresByType text/html "access plus 0 seconds"
</IfModule>
```

## Nginx Configuration

```nginx
# HTTP to HTTPS redirect
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS Server
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    # Root directory for React build
    root /var/www/xch555/dist;
    
    # React SPA routing
    location / {
        try_files $uri $uri/ /index.html;
        add_header Cache-Control "public, max-age=0";
    }
    
    # Static assets with long cache
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # API Proxy to PHP backend
    location /api/ {
        proxy_pass http://localhost:8001/api/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        
        # CORS headers
        add_header 'Access-Control-Allow-Origin' '*';
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization';
        
        if ($request_method = 'OPTIONS') {
            return 204;
        }
    }
    
    # Compression
    gzip on;
    gzip_types text/plain text/css text/xml text/javascript 
               application/x-javascript application/xml+rss application/json;
    gzip_min_length 1000;
    
    # Logs
    access_log /var/log/nginx/xch555-access.log;
    error_log /var/log/nginx/xch555-error.log;
}
```

## PHP-FPM Configuration

For API backend, ensure PHP-FPM is properly configured:

```ini
; /etc/php/8.1/fpm/pool.d/xch555.conf

[xch555]
listen = 127.0.0.1:8001
listen.backlog = 65535
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.process_idle_timeout = 10s
pm.max_requests = 500
user = www-data
group = www-data
```

## SSL/TLS Setup

### Using Let's Encrypt with Certbot

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-apache

# Generate certificate
sudo certbot certonly --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

## Environment Configuration

Create `.env` file in API root:

```
DB_HOST=localhost
DB_USER=555prouser
DB_PASSWORD=e2OFVjrRK77ljyfs4z@R
DB_NAME=555prodb
JWT_SECRET=your-very-secret-key-min-32-chars-change-in-production
JWT_EXPIRY=86400
CORS_ORIGIN=https://yourdomain.com
API_ENV=production
DEBUG=false
```

## Performance Tuning

### Database Connection Pooling

Use PHP Redis for session and caching:

```php
// sessions.php
session_save_handler('redis');
session_save_path('tcp://localhost:6379?prefix=xch555');
session_start();
```

### API Rate Limiting

Implement rate limiting in middleware:

```php
// rate-limit-middleware.php
class RateLimiter {
    public static function check($ip, $limit = 100, $window = 3600) {
        $redis = new Redis();
        $redis->connect('localhost');
        
        $key = "rate_limit:$ip";
        $count = $redis->incr($key);
        
        if ($count === 1) {
            $redis->expire($key, $window);
        }
        
        if ($count > $limit) {
            http_response_code(429);
            die(json_encode(['error' => 'Rate limit exceeded']));
        }
    }
}
```

## Monitoring & Logging

### Application Monitoring

```php
// Set up error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/xch555-error.log');

// Log API requests
function logRequest() {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'],
        'path' => $_SERVER['REQUEST_URI'],
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ];
    
    file_put_contents('/var/log/xch555/api.log', json_encode($log) . PHP_EOL, FILE_APPEND);
}
```

### Uptime Monitoring

Use tools like Uptime Kuma or StatusCake for monitoring.

## Security Checklist

- [ ] Enable HTTPS with valid SSL certificate
- [ ] Set strong JWT secret (32+ characters)
- [ ] Enable CORS only for trusted domains
- [ ] Implement rate limiting
- [ ] Keep PHP and dependencies updated
- [ ] Use prepared statements (prevent SQL injection)
- [ ] Implement CSRF protection if needed
- [ ] Set secure headers (CSP, X-Frame-Options, etc.)
- [ ] Regular backups of database
- [ ] Monitor error logs regularly
- [ ] Disable directory listing
- [ ] Restrict file uploads
- [ ] Validate all inputs

## Testing Configuration

```bash
# Test React build
cd /var/www/xch555
npm run build

# Test API endpoints
curl -X POST http://localhost:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test"}'

# Test CORS
curl -X OPTIONS http://localhost/api/auth/login \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: POST" \
  -v
```
