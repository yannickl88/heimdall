<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Yannickl88\Heimdall\Config\DataStoreSerializer
 */
class DataStoreSerializerTest extends TestCase
{
    protected function tearDown()
    {
        @unlink(__DIR__ . '/dump');
    }

    public function testGeneric(): void
    {
        $file = __DIR__ . '/dump';
        $data = ['some_data' => bin2hex(random_bytes(21))];

        $serializer = new DataStoreSerializer();
        $serializer->dump($file, $data);

        self::assertSame($data, $serializer->load($file));
    }
}
