<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;


use Yannickl88\Heimdall\Config\Exception\BadRepositoryException;

interface RepositoryLoaderInterface
{
    public function needsToken(): bool;

    /**
     * @param string $token
     * @throws BadRepositoryException
     */
    public function init(string $token): void;
}
