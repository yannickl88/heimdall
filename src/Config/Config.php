<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;

final class Config implements ConfigInterface
{
    private const DEFAULT_KEYSPACE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_*-+!$%=';
    public static function generate(int $length = 10, string $hint = ''): string
    {
        $keyspace = !empty($hint) ? $hint : self::DEFAULT_KEYSPACE;
        $str      = '';
        $max      = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    private $data_store;
    private $identifier;
    private $directives = [];
    private $environment_variables = [];
    private $tasks = [];

    public function __construct(string $identifier, array $config_data, ScopedDataStoreInterface $data_store)
    {
        $this->data_store = $data_store;
        $this->identifier = $identifier;

        $this->load($config_data);
    }

    private function load(array $data)
    {
        $includes = $data['includes'] ?? [];
        $directives = [];
        $environment_variables = [];
        $tasks = [];

        foreach ($includes as $include) {
            if (1 !== preg_match('/^[a-z0-9\-]+$/', $include)) {
                throw new \RuntimeException(sprintf('Bad include format for "%s".', $include));
            }

            $file = dirname(__DIR__, 2) . '/etc/' . $include . '.json';

            if (!file_exists($file)) {
                throw new \RuntimeException(sprintf('Unknown file "%s".', $file));
            }

            $config = new self(
                $include,
                json_decode(file_get_contents($file), true),
                $this->data_store
            );

            $directives[] = $config->directives;
            $environment_variables[] = $config->environment_variables;
            $tasks[] = $config->tasks;
        }

        // directives
        $directives[] = $data['directives'] ?? [];
        $environment_variables[] = $data['env-variables'] ?? [];
        $tasks[] = $data['tasks'] ?? [];

        $this->directives = array_merge(...$directives);
        $this->environment_variables = array_merge(...$environment_variables);
        $this->tasks = array_merge(...$tasks);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getEnvironmentVariableKeys(): array
    {
        return array_keys($this->environment_variables);
    }

    public function getEnvironmentVariable(string $key): string
    {
        if (!isset($this->environment_variables[$key])) {
            throw new \InvalidArgumentException(sprintf('Unknown environment variable "%s".', $key));
        }

        return preg_replace_callback('/%([^%]+)%/', function (array $match) {
            return $this->hasFact($match[1]) ? $this->getFact($match[1]) : $match[0];
        }, $this->environment_variables[$key]);
    }

    public function hasFact(string $key): bool
    {
        return isset($this->directives[$key]);
    }

    public function getFact(string $key, ?string $default = null): string
    {
        if (null !== $default && !$this->hasFact($key)) {
            return $default;
        }

        if (!isset($this->directives[$key])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown fact "%s", did you create a directive for it?',
                $key
            ));
        }

        $directive = $this->directives[$key];

        // was is stored before and still up-to-date?
        if ($this->data_store->has($key, $directive)) {
            return $this->data_store->get($key);
        }

        $fact = $this->evaluateFact($directive);

        $this->data_store->put($key, $directive, $fact);

        return $fact;
    }

    private function evaluateFact(string $raw_value): string
    {
        $value = $raw_value;

        if (1 === preg_match('/^@GEN(\(([0-9]+)(;(.*))?\))?$/', $value, $matches)) {
            switch (count($matches)) {
                case 3:
                    $value = self::generate((int) $matches[2]);
                    break;
                case 5:
                    $value = self::generate((int) $matches[2], $matches[4]);
                    break;
                default:
                    $value = self::generate();
            }
        }

        return $value;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }
}
