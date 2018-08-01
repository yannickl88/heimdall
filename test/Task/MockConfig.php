<?php
namespace Yannickl88\Heimdall\Task;

use Yannickl88\Heimdall\Config\ConfigInterface;

class MockConfig implements ConfigInterface
{
    private $identifier;
    private $facts;
    private $envs;
    private $tasks;

    public function __construct(string $identifier, array $facts = [], array $envs = [], array $tasks = [])
    {

        $this->identifier = $identifier;
        $this->facts = $facts;
        $this->envs = $envs;
        $this->tasks = $tasks;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function getEnvironmentVariableKeys(): array
    {
        return array_keys($this->envs);
    }

    public function getEnvironmentVariable(string $key): string
    {
        return $this->envs[$key];
    }

    public function hasFact(string $key): bool
    {
        return isset($this->facts[$key]);
    }

    public function getFact(string $key, ?string $default = null): string
    {
        return $this->facts[$key] ?? $default;
    }
}
