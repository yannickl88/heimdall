<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Yannickl88\Server\Config\Config
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
        $config = new Config(__DIR__ . '/config.json');
        $config->save();

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

        self::assertFileExists(__DIR__ . '/config.json.lock');

        // load config again
        $config_new = new Config(__DIR__ . '/config.json');

        // Make sure the generated values are the same after a reload
        self::assertSame($config->getFact('gen'), $config_new->getFact('gen'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Unknown file ".*\/thisdoesnotexists.json"./
     */
    public function testConfigBadFile()
    {
        new Config(__DIR__ . '/thisdoesnotexists.json');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Unknown file ".*\/thisincludedoesnotexists.json"./
     */
    public function testConfigBadInclude()
    {
        new Config(__DIR__ . '/bad-include.json');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Bad include format for "../thisincludedoesnotexists".
     */
    public function testConfigBadIncludeFormat()
    {
        new Config(__DIR__ . '/bad-include-format.json');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown environment variable "THISDOESNOTEXISTS".
     */
    public function testConfigMissingEnvVar()
    {
        $config = new Config(__DIR__ . '/config.json');
        $config->getEnvironmentVariable('THISDOESNOTEXISTS');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown fact "THISDOESNOTEXISTS", did you create a directive for it?
     */
    public function testConfigMissingFact()
    {
        $config = new Config(__DIR__ . '/config.json');
        $config->getFact('THISDOESNOTEXISTS');
    }

    public function testConfigMissingFactWithDefault()
    {
        $config = new Config(__DIR__ . '/config.json');
        self::assertSame('foobar', $config->getFact('THISDOESNOTEXISTS', 'foobar'));
    }
}
