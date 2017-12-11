<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

interface ConfigRepositoryInterface
{
    public function update():void;

    /**
     * @return ConfigInterface[]
     */
    public function all(): array;
}
