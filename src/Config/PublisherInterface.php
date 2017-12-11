<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;

use Yannickl88\Server\Config\Exception\AlreadyUpToDateException;
use Yannickl88\Server\Config\Exception\FileChangedException;
use Yannickl88\Server\Config\Exception\UnknownFileException;

interface PublisherInterface
{
    /**
     * @throws FileChangedException
     * @throws AlreadyUpToDateException
     */
    public function update(): void;
    public function dump(): void;

    /**
     * @throws UnknownFileException
     */
    public function publish(): void;
}
