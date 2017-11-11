# vhost-helper
Small helper script to create vhosts

## Usage
See the help for usage information.

```
$ ./gen-host --help
Usage: gen-host [OPTION]... DOMAIN
Generate a vhost file content to use with Apache.
Example: genhost --http google.com

Options to use
  --alias=NAME Create a vhost which has a server alias.
  --help       Print this help.
  --http       Create a vhost only for http usage, no redirect on port 80.
  --no-index   Create a vhost which has Indexing disabled.
  --port       Create a vhost which has an alternative port, this will create a HTTP only vhost.
```

## Example

**Basic HTTPS vhost**
```
$ ./gen-host google.com
<VirtualHost *:80>
        ServerName google.com
        # No ServerAlias

        RewriteEngine On
        RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

<IfModule mod_ssl.c>
        <VirtualHost *:443>
                ServerName google.com
                # No ServerAlias

                ServerAdmin webmaster@localhost
                DocumentRoot /var/www/google.com/current/web

                ErrorLog ${APACHE_LOG_DIR}/error.log
                CustomLog ${APACHE_LOG_DIR}/access.log combined

                Include /etc/letsencrypt/options-ssl-apache.conf
                SSLCertificateFile /etc/letsencrypt/live/google.com/cert.pem
                SSLCertificateKeyFile /etc/letsencrypt/live/google.com/privkey.pem
                SSLCertificateChainFile /etc/letsencrypt/live/google.com/chain.pem

                <Directory /var/www/google.com/current/web>
                        Options Indexes FollowSymLinks
                        AllowOverride All
                        Require all granted
                        Allow from all
                </Directory>
        </VirtualHost>
</IfModule>
```
**Basic HTTP vhost**
```
$ ./gen-host --http google.com
<VirtualHost *:80>
        ServerName google.com
        # No ServerAlias

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/google.com/current/web

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        <Directory /var/www/google.com/current/web>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
                Allow from all
        </Directory>
</VirtualHost>
```
**Https with server alias**
```
$ ./gen-host --alias www google.com
<VirtualHost *:80>
        ServerName google.com
        ServerAlias www.google.com

        RewriteEngine On
        RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

<IfModule mod_ssl.c>
        <VirtualHost *:443>
                ServerName google.com
                ServerAlias www.google.com

                ServerAdmin webmaster@localhost
                DocumentRoot /var/www/google.com/current/web

                ErrorLog ${APACHE_LOG_DIR}/error.log
                CustomLog ${APACHE_LOG_DIR}/access.log combined

                Include /etc/letsencrypt/options-ssl-apache.conf
                SSLCertificateFile /etc/letsencrypt/live/google.com/cert.pem
                SSLCertificateKeyFile /etc/letsencrypt/live/google.com/privkey.pem
                SSLCertificateChainFile /etc/letsencrypt/live/google.com/chain.pem

                <Directory /var/www/google.com/current/web>
                        Options Indexes FollowSymLinks
                        AllowOverride All
                        Require all granted
                        Allow from all
                </Directory>
        </VirtualHost>
</IfModule>
```

**HTTP on port 8080 and no index**
```
$ ./gen-host --no-index --port 8080 google.com
<VirtualHost *:8080>
        ServerName google.com
        # No ServerAlias

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/google.com/current/web

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        <Directory /var/www/google.com/current/web>
                Options FollowSymLinks
                AllowOverride All
                Require all granted
                Allow from all
        </Directory>
</VirtualHost>
```
