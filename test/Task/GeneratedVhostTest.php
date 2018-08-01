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
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunWithAlias()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.alias' => 'www.foo.bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-alias.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunWithMultipleAliases()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.alias' => 'www1.foo.bar;www2.foo.bar;www3.foo.bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-multi-alias.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunIndexable()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.indexed' => 'yes',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-indexable.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunHtaccess()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.indexed' => 'yes',
            'host.htaccess' => 'no',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-htaccess.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunPort()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.port' => '1234',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-port.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunCacheControl()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.cache-control' => '2628000',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-cache-control.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunHttps()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.schema' => 'https',
            'cert.base_path' => '/foo/bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-https.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunHttpsDifferentCertHost()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.schema' => 'https',
            'cert.base_path' => '/foo/bar',
            'cert.host_name' => 'foobar.com',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-https-cert-loc.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunHttpsWithAlias()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.alias' => 'www.foo.bar',
            'host.schema' => 'https',
            'cert.base_path' => '/foo/bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(
            __DIR__ . '/fixtures/generate-vhost-https-alias.expected.conf',
            __DIR__ . '/foo.bar.conf'
        );
    }

    public function testRunHttpsWithMultipleAliases()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.alias' => 'www1.foo.bar;www2.foo.bar;www3.foo.bar',
            'host.schema' => 'https',
            'cert.base_path' => '/foo/bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(
            __DIR__ . '/fixtures/generate-vhost-https-multi-alias.expected.conf',
            __DIR__ . '/foo.bar.conf'
        );
    }

    public function testRunHttpsIndexable()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.schema' => 'https',
            'host.indexed' => 'yes',
            'cert.base_path' => '/foo/bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(
            __DIR__ . '/fixtures/generate-vhost-https-indexable.expected.conf',
            __DIR__ . '/foo.bar.conf'
        );
    }

    public function testRunHttpsHtaccess()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.schema' => 'https',
            'host.htaccess' => 'no',
            'cert.base_path' => '/foo/bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(
            __DIR__ . '/fixtures/generate-vhost-https-htaccess.expected.conf',
            __DIR__ . '/foo.bar.conf'
        );
    }

    public function testRunHttpsPort()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.schema' => 'https',
            'host.port' => '1234',
            'cert.base_path' => '/foo/bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        // If there is a port, we fallback to http.
        self::assertFileEquals(__DIR__ . '/fixtures/generate-vhost-port.expected.conf', __DIR__ . '/foo.bar.conf');
    }

    public function testRunHttpsCacheControl()
    {
        $config = new MockConfig('foobar', [
            'host.name' => 'foo.bar',
            'host.schema' => 'https',
            'host.cache-control' => '2628000',
            'cert.base_path' => '/foo/bar',
            'etc.apache.vhost_location' => __DIR__,
        ], ['foo' => 'foobar', 'bar' => 'barbaz']);

        $this->generated_vhost->run($config);

        self::assertFileEquals(
            __DIR__ . '/fixtures/generate-vhost-https-cache-control.expected.conf',
            __DIR__ . '/foo.bar.conf'
        );
    }
}
