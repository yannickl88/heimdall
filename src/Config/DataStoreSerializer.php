<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

final class DataStoreSerializer implements SerializerInterface
{
    public function dump(string $file, array $data): void
    {
        file_put_contents($file, \json_encode($data, JSON_PRETTY_PRINT));
    }

    public function load(string $file): array
    {
        return json_decode(file_get_contents($file), true);
    }
}
