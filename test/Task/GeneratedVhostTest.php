<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Task;

use PHPUnit\Framework\TestCase;
use Yannickl88\Heimdall\Config\ConfigInterface;

/**
 * @covers \Yannickl88\Heimdall\Task\GeneratedVhost
 */
class GeneratedVhostTest extends TestCase
{
    /**
     * @var GeneratedVhost
     */
    private $generated_vhost;

    protected function setUp()
    {
        $this->generated_vhost = new GeneratedVhost();
    }

    protected function tearDown()
    {
        if (file_exists(__DIR__ . '/foo.bar.conf')) {
            unlink(__DIR__ . '/foo.bar.conf');
        }
    }

    public function testIdentifier()
    {
        self::assertSame('generate:vhost', GeneratedVhost::identifier());
    }

    public function testRun()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.apache.vhost_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');
        $config->getFact('host.schema', 'http')->willReturn('http');
        $config->getFact('host.indexed', 'no')->willReturn('no');
        $config->hasFact('host.port')->willReturn(false);
        $config->hasFact('host.alias')->willReturn(false);

        $this->generated_vhost->run($config->reveal());

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunWithAlias()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.apache.vhost_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');
        $config->getFact('host.alias')->willReturn('www.foo.bar');
        $config->getFact('host.schema', 'http')->willReturn('http');
        $config->getFact('host.indexed', 'no')->willReturn('no');
        $config->hasFact('host.port')->willReturn(false);
        $config->hasFact('host.alias')->willReturn(true);

        $this->generated_vhost->run($config->reveal());

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-alias.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunIndexable()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.apache.vhost_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');
        $config->getFact('host.schema', 'http')->willReturn('http');
        $config->getFact('host.indexed', 'no')->willReturn('yes');
        $config->hasFact('host.port')->willReturn(false);
        $config->hasFact('host.alias')->willReturn(false);

        $this->generated_vhost->run($config->reveal());

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-indexable.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunPort()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.apache.vhost_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');
        $config->getFact('host.schema', 'http')->willReturn('http');
        $config->getFact('host.port')->willReturn('1234');
        $config->getFact('host.indexed', 'no')->willReturn('no');
        $config->hasFact('host.port')->willReturn(true);
        $config->hasFact('host.alias')->willReturn(false);

        $this->generated_vhost->run($config->reveal());

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-port.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunHttps()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.apache.vhost_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');
        $config->getFact('host.schema', 'http')->willReturn('https');
        $config->getFact('host.indexed', 'no')->willReturn('no');
        $config->getFact('cert.base_path')->willReturn('/foo/bar');
        $config->getFact('cert.cert_name')->willReturn('cert.pem');
        $config->getFact('cert.privkey_name')->willReturn('privkey.pem');
        $config->getFact('cert.chain_name')->willReturn('chain.pem');
        $config->hasFact('host.port')->willReturn(false);
        $config->hasFact('host.alias')->willReturn(false);

        $this->generated_vhost->run($config->reveal());

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-https.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunHttpsWithAlias()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.apache.vhost_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');
        $config->getFact('host.alias')->willReturn('www.foo.bar');
        $config->getFact('host.schema', 'http')->willReturn('https');
        $config->getFact('host.indexed', 'no')->willReturn('no');
        $config->getFact('cert.base_path')->willReturn('/foo/bar');
        $config->getFact('cert.cert_name')->willReturn('cert.pem');
        $config->getFact('cert.privkey_name')->willReturn('privkey.pem');
        $config->getFact('cert.chain_name')->willReturn('chain.pem');
        $config->hasFact('host.port')->willReturn(false);
        $config->hasFact('host.alias')->willReturn(true);

        $this->generated_vhost->run($config->reveal());

        self::assertFileEquals(
            __DIR__ . '/fixtures/generate-vhost-https-alias.expected.conf',
            __DIR__ . '/foo.bar.conf'
        );
    }

    public function testRunHttpsIndexable()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.apache.vhost_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');
        $config->getFact('host.schema', 'http')->willReturn('https');
        $config->getFact('host.indexed', 'no')->willReturn('yes');
        $config->getFact('cert.base_path')->willReturn('/foo/bar');
        $config->getFact('cert.cert_name')->willReturn('cert.pem');
        $config->getFact('cert.privkey_name')->willReturn('privkey.pem');
        $config->getFact('cert.chain_name')->willReturn('chain.pem');
        $config->hasFact('host.port')->willReturn(false);
        $config->hasFact('host.alias')->willReturn(false);

        $this->generated_vhost->run($config->reveal());

        self::assertFileEquals(
            __DIR__ . '/fixtures/generate-vhost-https-indexable.expected.conf',
            __DIR__ . '/foo.bar.conf'
        );
    }

    public function testRunHttpsPort()
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->getEnvironmentVariableKeys()->willReturn(['foo', 'bar']);
        $config->getEnvironmentVariable('foo')->willReturn('foobar');
        $config->getEnvironmentVariable('bar')->willReturn('barbaz');
        $config->getFact('etc.apache.vhost_location')->willReturn(__DIR__ . '/');
        $config->getFact('host.name')->willReturn('foo.bar');
        $config->getFact('host.schema', 'http')->willReturn('https');
        $config->getFact('host.port')->willReturn('1234');
        $config->getFact('host.indexed', 'no')->willReturn('no');
        $config->getFact('cert.base_path')->willReturn('/foo/bar');
        $config->getFact('cert.cert_name')->willReturn('cert.pem');
        $config->getFact('cert.privkey_name')->willReturn('privkey.pem');
        $config->getFact('cert.chain_name')->willReturn('chain.pem');
        $config->hasFact('host.port')->willReturn(true);
        $config->hasFact('host.alias')->willReturn(false);

        $this->generated_vhost->run($config->reveal());

        // If there is a port, we fallback to http.
        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-port.expected.conf', __DIR__ . '/foo.bar.conf');
    }
}
