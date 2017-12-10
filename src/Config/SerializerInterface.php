<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;


interface SerializerInterface
{
    public function dump(string $file, array $data): void;

    public function load(string $file): array;
}
