<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;

class DataStore
{
    private $lock_file;
    private $api;
    private $serializer;
    private $repositories = [];
    private $stored_data = [];
    private $config_data = [];

    public function __construct(string $lock_file, ApiInterface $api, SerializerInterface $serializer)
    {
        $this->lock_file = $lock_file;
        $this->api = $api;
        $this->serializer = $serializer;

        if (!file_exists($this->lock_file)) {
            $this->repositories = [];
            $this->stored_data = [];
            $this->config_data = [];
        } else {
            [$this->repositories, $this->stored_data, $this->config_data] = $this->serializer->load($this->lock_file);
        }
    }

    /**
     * @return ConfigInterface[]
     */
    public function configs(): array
    {
        $configs = [];

        foreach ($this->config_data as $identifier => $config) {
            $configs[] = new Config($identifier, $config['config']['data'], $this->bind($identifier));
        }

        return $configs;
    }

    public function save(): void
    {
        $this->serializer->dump($this->lock_file, [$this->repositories, $this->stored_data, $this->config_data]);
    }

    public function register(string $url): RepositoryLoaderInterface
    {
        $url = rtrim($url, '/');

        if (1 !== preg_match('~^https?://[a-z0-9_\-\.]+$~', $url)) {
            throw new \RuntimeException('Invalid repository URL.');
        }

        if (isset($this->repositories[$url])) {
            throw new \RuntimeException('Repository already registered.');
        }

        $needs_token = function () use ($url) {
            return true;
        };

        $init = function (string $token) use ($url) {
            try {
                $this->api->fetchIdentifiers($url, $token);
            } catch (ApiException $e) {
                throw new \RuntimeException('Cannot access repository.', 0, $e);
            }

            $this->repositories[$url] = [
                'token' => $token
            ];
        };

        return new class($needs_token, $init) implements RepositoryLoaderInterface
        {
            private $binding_needs_token;
            private $binding_init;

            public function __construct(callable $needs_token, callable $init)
            {
                $this->binding_needs_token = $needs_token;
                $this->binding_init = $init;
            }

            public function needsToken(): bool
            {
                return \call_user_func($this->binding_needs_token);
            }

            public function init($token)
            {
                return \call_user_func($this->binding_init, $token);
            }
        };
    }

    public function add($identifier): ConfigLoaderInterface
    {
        if (isset($this->config_data[$identifier])) {
            throw new \RuntimeException('Config already added.');
        }

        $binding_get_repositories = function () use ($identifier) {
            $possible_repositories = [];

            foreach ($this->repositories as $url => $repository_data) {
                if (\in_array($identifier, $this->api->fetchIdentifiers($url, $repository_data['token']), true)) {
                    $possible_repositories[] = $url;
                }
            }

            return $possible_repositories;
        };
        $binding_init_from = function (string $url) use ($identifier) {
            $this->config_data[$identifier] = [
                'repository' => $url,
                'config' => $this->api->fetchConfig($url, $this->repositories[$url]['token'], $identifier)
            ];
        };

        return new class($binding_get_repositories, $binding_init_from) implements ConfigLoaderInterface
        {
            private $binding_get_repositories;
            private $binding_init_from;

            public function __construct(callable $get_repositories, callable $init_from)
            {
                $this->binding_get_repositories = $get_repositories;
                $this->binding_init_from = $init_from;
            }

            public function getRepositories(): array
            {
                return \call_user_func($this->binding_get_repositories);
            }

            public function initFrom(string $repository): void
            {
                \call_user_func($this->binding_init_from, $repository);
            }
        };
    }

    private function bind(string $identifier): ScopedDataStoreInterface
    {
        $binding_get = function (string $key) use ($identifier): string
        {
            return $this->stored_data[$identifier][$key]['value'];
        };

        $binding_has = function (string $key, string $directive) use ($identifier): bool
        {
            return isset($this->stored_data[$identifier][$key]['value'])
                && $this->stored_data[$identifier][$key]['hash'] === md5($directive);
        };

        $binding_put = function (string $key, string $directive, string $value) use ($identifier): void
        {
            $this->stored_data[$identifier][$key] = [
                'hash' => md5($directive),
                'value' => $value
            ];
        };

        return new class($binding_get, $binding_has, $binding_put) implements ScopedDataStoreInterface
        {
            private $binding_get;
            private $binding_has;
            private $binding_put;

            public function __construct(callable $get, callable $has, callable $put)
            {
                $this->binding_get = $get;
                $this->binding_has = $has;
                $this->binding_put = $put;
            }

            public function get(string $key): string
            {
                return \call_user_func($this->binding_get, $key);
            }

            public function has(string $key, string $directive): bool
            {
                return \call_user_func($this->binding_has, $key, $directive);
            }

            public function put(string $key, string $directive, string $value): void
            {
                \call_user_func($this->binding_put, $key, $directive, $value);
            }
        };
    }
}
