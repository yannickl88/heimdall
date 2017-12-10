<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;


interface ConfigLoaderInterface
{
    public function getRepositories(): array;

    public function initFrom(string $repository): void;
}
