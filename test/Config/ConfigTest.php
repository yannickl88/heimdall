<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Yannickl88\Heimdall\Config\Config
 */
class ConfigTest extends TestCase
{
    protected function tearDown()
    {
        if (file_exists(__DIR__ . '/config.json.lock')) {
            unlink(__DIR__ . '/config.json.lock');
        }
    }

    public function testGenerate()
    {
        self::assertSame('aaaaa', Config::generate(5, 'a'));
        self::assertRegExp('/^[abc]{1000}$/', Config::generate(1000, 'abc'));
    }

    public function testConfig()
    {
        $api = $this->prophesize(ApiInterface::class);
        $serializer = $this->prophesize(SerializerInterface::class);

        $api->fetchIdentifiers('http://foo.bar', 'foobar')->willReturn(['test']);
        $api
            ->fetchConfig('http://foo.bar', 'foobar', 'test')
            ->willReturn(json_decode(file_get_contents(__DIR__ . '/config.json'), true));

        $data_store = new DataStore('phpunit', $api->reveal(), $serializer->reveal());
        $data_store->register('http://foo.bar')->init('foobar');
        $data_store->add('test')->initFrom('http://foo.bar');

        $config = $data_store->configs()->all()[0];

        self::assertSame('test', $config->getIdentifier());
        self::assertSame('bar', $config->getFact('foo'));
        self::assertSame('baz', $config->getFact('bar'));
        self::assertSame($config->getFact('gen'), $config->getFact('gen'));
        self::assertSame(10, mb_strlen($config->getFact('gen')));
        self::assertSame(20, mb_strlen($config->getFact('gen-20')));
        self::assertSame(20, mb_strlen($config->getFact('gen-20-numbers')));
        self::assertSame(['FOO', 'STATIC'], $config->getEnvironmentVariableKeys());
        self::assertSame('phpunit', $config->getEnvironmentVariable('STATIC'));
        self::assertSame('bar-baz', $config->getEnvironmentVariable('FOO'));
        self::assertSame(['some:task'], $config->getTasks());

        // load config again
        $config_new = $data_store->configs()->all()[0];

        // Make sure the generated values are the same after a reload
        self::assertSame($config->getFact('gen'), $config_new->getFact('gen'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Unknown file ".*\/thisincludedoesnotexists.json"./
     */
    public function testConfigBadInclude()
    {
        $data_store = $this->prophesize(ScopedDataStoreInterface::class);

        new Config('phpunit', ['includes' => ['thisincludedoesnotexists']], $data_store->reveal());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Bad include format for "../thisincludedoesnotexists".
     */
    public function testConfigBadIncludeFormat()
    {
        $data_store = $this->prophesize(ScopedDataStoreInterface::class);

        new Config('phpunit', ['includes' => ['../thisincludedoesnotexists']], $data_store->reveal());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown environment variable "THISDOESNOTEXISTS".
     */
    public function testConfigMissingEnvVar()
    {
        $data_store = $this->prophesize(ScopedDataStoreInterface::class);

        $config = new Config('phpunit', [], $data_store->reveal());
        $config->getEnvironmentVariable('THISDOESNOTEXISTS');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown fact "THISDOESNOTEXISTS", did you create a directive for it?
     */
    public function testConfigMissingFact()
    {
        $data_store = $this->prophesize(ScopedDataStoreInterface::class);
        $config = new Config('phpunit', [], $data_store->reveal());
        $config->getFact('THISDOESNOTEXISTS');
    }

    public function testConfigMissingFactWithDefault()
    {
        $data_store = $this->prophesize(ScopedDataStoreInterface::class);
        $config = new Config('phpunit', [], $data_store->reveal());
        self::assertSame('foobar', $config->getFact('THISDOESNOTEXISTS', 'foobar'));
    }
}
