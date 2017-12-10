<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;


interface RepositoryLoaderInterface
{
    public function needsToken(): bool;

    public function init($token);
}
