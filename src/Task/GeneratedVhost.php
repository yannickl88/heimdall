<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Task;

use Yannickl88\Heimdall\Config\ConfigInterface;
use Yannickl88\Heimdall\TaskInterface;

/**
 * Generates a vhost file for a host.
 */
class GeneratedVhost implements TaskInterface
{
    public static function identifier(): string
    {
        return 'generate:vhost';
    }

    public function run(ConfigInterface $config): void
    {
        $schema = $config->getFact('host.schema', 'http');
        $lines = [
            '<VirtualHost *:80>',
            [],
            '</VirtualHost>'
        ];

        if ($config->hasFact('host.port')) {
            $lines[0] = '<VirtualHost *:' . $config->getFact('host.port') . '>';
            $schema = 'http';
        }

        if ($schema === 'https') {
            // HTTP redirect
            array_splice($lines[1], 1, 0, [
                'RewriteEngine On',
                'RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]',
            ]);
            array_splice($lines[1], 0, 0, $this->getServerName($config));

            // HTTPS info
            $https_lines = [
                '<VirtualHost *:443>',
                [],
                '</VirtualHost>'
            ];
            array_splice($https_lines[1], 0, 0, $this->getDirectory($config));
            array_splice($https_lines[1], 0, 0, $this->getKeepAlive($config));
            array_splice($https_lines[1], 0, 0, $this->getSslConfig($config));
            array_splice($https_lines[1], 0, 0, $this->getEnvVars($config));
            array_splice($https_lines[1], 0, 0, $this->getCacheSettings($config));
            array_splice($https_lines[1], 0, 0, $this->getApacheConfig($config));
            array_splice($https_lines[1], 0, 0, $this->getServerName($config));

            $lines[] = '';
            $lines[] = '<IfModule mod_ssl.c>';
            $lines[] = $https_lines;
            $lines[] = '</IfModule>';
        } else {
            // HTTP info
            array_splice($lines[1], 0, 0, $this->getDirectory($config));
            array_splice($lines[1], 0, 0, $this->getKeepAlive($config));
            array_splice($lines[1], 0, 0, $this->getEnvVars($config));
            array_splice($lines[1], 0, 0, $this->getCacheSettings($config));
            array_splice($lines[1], 0, 0, $this->getApacheConfig($config));
            array_splice($lines[1], 0, 0, $this->getServerName($config));
        }

        $file = $config->getFact('etc.apache.vhost_location', '/etc/apache/sites-available') . '/' . $config->getFact('host.name') . '.conf';

        file_put_contents($file, $this->implode($lines));
    }

    private function getApacheConfig(ConfigInterface $config): array
    {
        return [
            'ServerAdmin webmaster@localhost',
            'DocumentRoot /var/www/' . $config->getFact('host.name') . '/current/' . $config->getFact('etc.apache.document_root', 'web'),
            '',
            'ErrorLog ${APACHE_LOG_DIR}/error.log',
            'CustomLog ${APACHE_LOG_DIR}/access.log combined',
            '',
        ];
    }

    private function getDirectory(ConfigInterface $config): array
    {
        $index = $config->getFact('host.indexed', 'no') === 'yes';
        $htaccess = $config->getFact('host.htaccess', 'yes') === 'yes';

        return [
            '<Directory /var/www/' . $config->getFact('host.name') . '/current/' . $config->getFact('etc.apache.document_root', 'web') . '>',
            [
                $index ? 'Options Indexes FollowSymLinks' : 'Options FollowSymLinks',
                'AllowOverride ' . ( $htaccess ? 'All' : 'None'),
                'Require all granted',
                'Allow from all',
            ],
            '</Directory>'
        ];
    }

    private function getSslConfig(ConfigInterface $config): array
    {
        $ssl_paths = $config->getFact('cert.base_path');
        $domain = $config->hasFact('cert.host_name')
            ? $config->getFact('cert.host_name')
            : $config->getFact('host.name');

        return [
            'Include ' . dirname($ssl_paths) . '/options-ssl-apache.conf',
            'SSLCertificateFile ' . $ssl_paths . '/' . $domain . '/' . $config->getFact('cert.cert_name', 'cert.pem'),
            'SSLCertificateKeyFile ' . $ssl_paths . '/' . $domain . '/' . $config->getFact('cert.privkey_name', 'privkey.pem'),
            'SSLCertificateChainFile ' . $ssl_paths . '/' . $domain . '/' . $config->getFact('cert.chain_name', 'chain.pem'),
            '',
        ];
    }

    private function getServerName(ConfigInterface $config): array
    {
        $lines = ['ServerName ' . $config->getFact('host.name')];

        if ($config->hasFact('host.alias')) {
            foreach (explode(';', $config->getFact('host.alias')) as $alias) {
                $lines[] = 'ServerAlias ' . $alias;
            }

        }

        $lines[] = '';

        return $lines;
    }

    private function getEnvVars(ConfigInterface $config): array
    {
        $lines = [];

        foreach ($config->getEnvironmentVariableKeys() as $key) {
            $lines[] = 'SetEnv ' . $key . ' "' . $config->getEnvironmentVariable($key) . '"';
        }

        $lines[] = '';

        return $lines;
    }

    private function getCacheSettings(ConfigInterface $config): array
    {
        if (empty($cache_control = $config->getFact('host.cache-control', ''))) {
            return [];
        }

        return [
            'Header set Cache-Control "max-age=' . $cache_control . ', public"',
            ''
        ];
    }

    private function getKeepAlive(ConfigInterface $config): array
    {
        if ($config->getFact('host.keep-alive', 'no') !== 'yes') {
            return [];
        }

        return [
            'KeepAlive On',
            'MaxKeepAliveRequests ' . $config->getFact('host.keep-alive-max-requests', '100'),
            'KeepAliveTimeout ' . $config->getFact('host.keep-alive-timeout', '100'),
            ''
        ];
    }

    private function implode(array $data, string $intent = ''): string
    {
        $str = '';

        foreach ($data as $line) {
            if (\is_array($line)) {
                $str .= $this->implode($line, $intent . "\t");
            } else {
                $str .= (empty($line) ? '' : $intent . $line) . "\n";
            }
        }

        return $str;
    }
}
