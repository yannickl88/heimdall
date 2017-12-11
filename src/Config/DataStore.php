<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;

use Yannickl88\Server\Config\Exception\AlreadyRegisteredException;
use Yannickl88\Server\Config\Exception\AlreadyUpToDateException;
use Yannickl88\Server\Config\Exception\BadRepositoryException;
use Yannickl88\Server\Config\Exception\ConfigAlreadyAddedException;
use Yannickl88\Server\Config\Exception\ConfigNotFoundException;
use Yannickl88\Server\Config\Exception\FileChangedException;
use Yannickl88\Server\Config\Exception\InvalidUrlException;
use Yannickl88\Server\Config\Exception\UnknownFileException;

class DataStore
{
    private $lock_file;
    private $api;
    private $serializer;
    private $repositories;
    private $stored_data;
    private $config_data;
    private $checkouts;

    public function __construct(string $lock_file, ApiInterface $api, SerializerInterface $serializer)
    {
        $this->lock_file = $lock_file;
        $this->api = $api;
        $this->serializer = $serializer;

        if (!file_exists($this->lock_file)) {
            $this->repositories = [];
            $this->stored_data = [];
            $this->config_data = [];
            $this->checkouts = [];
        } else {
            [$this->repositories, $this->stored_data, $this->config_data, $this->checkouts] = $this->serializer->load($this->lock_file);
        }
    }

    /**
     * Save the datastore to disk.
     */
    public function save(): void
    {
        $this->serializer->dump($this->lock_file, [$this->repositories, $this->stored_data, $this->config_data, $this->checkouts]);
    }

    /**
     * Return a list of configs which have been added.
     *
     * @return ConfigRepositoryInterface
     */
    public function configs(): ConfigRepositoryInterface
    {
        $binding_all = function () {
            $configs = [];

            foreach ($this->config_data as $identifier => $config) {
                $configs[] = new Config($identifier, $config['config']['data'], $this->bind($identifier));
            }

            return $configs;
        };

        $binding_update = function () {
            foreach (array_keys($this->config_data) as $identifier) {
                try {
                    $this->publisher($identifier)->update();
                } catch (AlreadyUpToDateException $e) {
                    continue;
                }
            }
        };

        return new class($binding_all, $binding_update) implements ConfigRepositoryInterface
        {
            private $binding_all;
            private $binding_update;

            public function __construct(callable $binding_all, callable $binding_update)
            {
                $this->binding_all = $binding_all;
                $this->binding_update = $binding_update;
            }

            public function update(): void
            {
                \call_user_func($this->binding_update);
            }

            public function all(): array
            {
                return \call_user_func($this->binding_all);
            }
        };
    }

    /**
     * Scope the datastore on an identifier.
     *
     * @param string $identifier
     * @return ScopedDataStoreInterface
     */
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

    /**
     * @param string $identifier
     * @return PublisherInterface
     * @throws ConfigNotFoundException
     */
    public function publisher(string $identifier): PublisherInterface
    {
        if (!isset($this->config_data[$identifier])) {
            throw new ConfigNotFoundException('Config does not exists.');
        }

        $file = getcwd() . DIRECTORY_SEPARATOR . $identifier . '.json';

        $binding_update = function () use ($file, $identifier) {
            if (file_exists($file)) {
                // does the file contain changes?
                $data = json_decode(file_get_contents($file), true);

                if ($data !== $this->config_data[$identifier]['config']['data']) {
                    throw new FileChangedException('File changed, cannot update.');
                }
            }

            $repo = $this->config_data[$identifier]['repository'];
            $token = $this->repositories[$repo]['token'];

            $data = $this->api->fetchConfig($repo, $token, $identifier);

            if ($data['revision'] === $this->config_data[$identifier]['config']['revision']) {
                throw new AlreadyUpToDateException('Already up to date.');
            }

            $this->config_data[$identifier]['config'] = $data;

            if (file_exists($file)) {
                $this->serializer->dump($file, $this->config_data[$identifier]['config']['data']);
            }
        };

        $binding_dump = function () use ($file, $identifier) {
            $this->checkouts[$file] = [
                'revision' => $this->config_data[$identifier]['config']['revision']
            ];

            $this->serializer->dump($file, $this->config_data[$identifier]['config']['data']);
        };

        $binding_publish = function () use ($file, $identifier) {
            if (!file_exists($file)) {
                throw new UnknownFileException('No file to publish.');
            }

            $repo = $this->config_data[$identifier]['repository'];
            $token = $this->repositories[$repo]['token'];
            $parent_revision = $this->checkouts[$file]['revision'];
            $data = json_decode(file_get_contents($file), true);

            $new_revision = $this->api->publishConfig($repo, $token, $identifier, $parent_revision, $data);

            // update revision of the file
            $this->checkouts[$file]['revision'] = $new_revision;

            // update the cached config revision
            $this->config_data[$identifier]['config']['data'] = $data;
            $this->config_data[$identifier]['config']['parent_revision'] = $parent_revision;
            $this->config_data[$identifier]['config']['revision'] = $new_revision;
        };

        return new class($binding_update, $binding_dump, $binding_publish) implements PublisherInterface
        {
            private $binding_update;
            private $binding_dump;
            private $binding_publish;

            public function __construct(callable $update, callable $dump, callable $publish)
            {
                $this->binding_update = $update;
                $this->binding_dump = $dump;
                $this->binding_publish = $publish;
            }

            public function update(): void
            {
                \call_user_func($this->binding_update);
            }

            public function dump(): void
            {
                \call_user_func($this->binding_dump);
            }

            public function publish(): void
            {
                \call_user_func($this->binding_publish);
            }
        };
    }

    /**
     * Register a repository to this client. These repositories will then be
     * available to fetch configs from.
     *
     * @param string $url
     * @return RepositoryLoaderInterface
     * @throws InvalidUrlException
     * @throws AlreadyRegisteredException
     */
    public function register(string $url): RepositoryLoaderInterface
    {
        $url = rtrim($url, '/');

        if (1 !== preg_match('~^https?://[a-z0-9_\-\.]+$~', $url)) {
            throw new InvalidUrlException('Invalid repository URL.');
        }

        if (isset($this->repositories[$url])) {
            throw new AlreadyRegisteredException('Repository already registered.');
        }

        $needs_token = function () use ($url) {
            return true;
        };

        $init = function (string $token) use ($url) {
            try {
                $this->api->fetchIdentifiers($url, $token);
            } catch (ApiException $e) {
                throw new BadRepositoryException('Cannot access repository.', 0, $e);
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

            public function init(string $token): void
            {
                \call_user_func($this->binding_init, $token);
            }
        };
    }

    /**
     * Add a config from a repository to this client. This config, once added,
     * will then be available in the ::configs() result.
     *
     * @param $identifier
     * @return ConfigLoaderInterface
     * @throws \Yannickl88\Server\Config\Exception\ConfigAlreadyAddedException
     */
    public function add($identifier): ConfigLoaderInterface
    {
        if (isset($this->config_data[$identifier])) {
            throw new ConfigAlreadyAddedException('Config already added.');
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
}
