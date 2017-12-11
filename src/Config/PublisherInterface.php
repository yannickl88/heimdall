<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

use Yannickl88\Heimdall\Config\Exception\AlreadyUpToDateException;
use Yannickl88\Heimdall\Config\Exception\ConfigNotFoundException;
use Yannickl88\Heimdall\Config\Exception\FileChangedException;
use Yannickl88\Heimdall\Config\Exception\UnknownFileException;

interface PublisherInterface
{
    /**
     * @throws FileChangedException
     * @throws AlreadyUpToDateException
     * @throws ConfigNotFoundException
     */
    public function update(): void;

    /**
     * @throws ConfigNotFoundException
     */
    public function dump(): void;

    /**
     * @throws ConfigNotFoundException
     */
    public function exists(): bool;

    public function getRepositories(): array;

    /**
     * @throws ConfigNotFoundException
     * @throws UnknownFileException
     * @param string|null $repo
     */
    public function publish(string $repo = null): void;
}
