<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Task;

use PHPUnit\Framework\TestCase;
use Yannickl88\Heimdall\Config\ConfigInterface;

/**
 * @covers \Yannickl88\Heimdall\Task\GeneratedEnvScript
 */
class GeneratedEnvScriptTest extends TestCase
{
    /**
     * @var GeneratedEnvScript
     */
    private $generated_env_script;

    protected function setUp()
    {
        $this->generated_env_script = new GeneratedEnvScript();
    }

    protected function tearDown()
    {
        if (file_exists(__DIR__ . '/foo.bar.sh')) {
            unlink(__DIR__ . '/foo.bar.sh');
        }
    }

    public function testIdentifier()
    {
        self::assertSame('generate:env-script', GeneratedEnvScript::identifier());
    }

    public function testRun()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.env.vars_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');

        $this->generated_env_script->run($config->reveal());

        self::assertFileEquals(__DIR__ . '/fixtures/generate-env-script.expected.sh', __DIR__ . '/foo.bar.sh');
    }
}
