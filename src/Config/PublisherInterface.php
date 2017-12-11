<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

use Yannickl88\Heimdall\Config\Exception\AlreadyUpToDateException;
use Yannickl88\Heimdall\Config\Exception\FileChangedException;
use Yannickl88\Heimdall\Config\Exception\UnknownFileException;

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
