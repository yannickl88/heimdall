<?php
declare(strict_types=1);

namespace Yannickl88\Server\Task;

use Yannickl88\Server\Config\ConfigInterface;
use Yannickl88\Server\TaskInterface;

/**
 * Generates a script which exports all environment variables.
 */
class GeneratedEnvScript implements TaskInterface
{
    public static function identifier(): string
    {
        return 'generate:env-script';
    }

    public function run(ConfigInterface $config): void
    {
        $lines = [];

        foreach ($config->getEnvironmentVariableKeys() as $key) {
            $lines[] = 'export ' . $key . '=' . $this->escape($config->getEnvironmentVariable($key));
        }

        $lines[] = '';

        $file = $config->getFact('etc.env.vars_location') . '/' . $config->getFact('host.name') . '.sh';

        file_put_contents($file, implode("\n", $lines));
    }

    private function escape(string $value): string
    {
        return preg_replace('/([\\$\\\\])/', '\\\\$1', $value);
    }
}
