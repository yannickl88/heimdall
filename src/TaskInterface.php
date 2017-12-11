<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall;

use Yannickl88\Heimdall\Config\ConfigInterface;

interface TaskInterface
{
    /**
     * Return the identifier of the task. This will be used to configure which tasks to run per server.
     *
     * @return string
     */
    public static function identifier(): string;

    /**
     * Run a task based on the config.
     *
     * @param ConfigInterface $config
     */
    public function run(ConfigInterface $config): void;
}
