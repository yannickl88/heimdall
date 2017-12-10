<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers \Yannickl88\Server\Config\DataStore
 */
class DataStoreTest extends TestCase
{
    private $lock_file;
    private $api;
    private $serializer;

    /**
     * @var DataStore
     */
    private $data_store;

    protected function setUp()
    {
        $this->lock_file = __DIR__ . '/lock';
        $this->api = $this->prophesize(ApiInterface::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);

        $this->data_store = new DataStore($this->lock_file, $this->api->reveal(), $this->serializer->reveal());
    }

    public function testLoadFromFile(): void
    {
        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->load(Argument::any())->willReturn([['http://foo.bar' => ['token' => 'foobar']], [], []]);
        $serializer->dump(Argument::any(), [['http://foo.bar' => ['token' => 'foobar']], [], []])->shouldBeCalled();

        $data_store = new DataStore(__FILE__, $this->api->reveal(), $serializer->reveal());
        $data_store->save();
    }

    public function testConfigs(): void
    {
        $this->api->fetchIdentifiers('http://foo.bar', 'foobar')->willReturn(['test']);
        $this->api
            ->fetchConfig('http://foo.bar', 'foobar', 'test')
            ->willReturn(['data' => ['directives' => ['foo' => 'bar']]]);

        $this->data_store->register('http://foo.bar')->init('foobar');
        $this->data_store->add('test')->initFrom('http://foo.bar');

        $configs = $this->data_store->configs();

        self::assertCount(1, $configs);
        self::assertSame('test', $configs[0]->getIdentifier());
        self::assertSame('bar', $configs[0]->getFact('foo'));
        self::assertSame('bar', $configs[0]->getFact('foo'));
    }

    public function testRegister(): void
    {
        $this->api->fetchIdentifiers('http://foo.bar', 'foobar')->willReturn(['test']);
        $register = $this->data_store->register('http://foo.bar');

        self::assertTrue($register->needsToken());

        $register->init('foobar');

        $this->serializer->dump(Argument::any(), [['http://foo.bar' => ['token' => 'foobar']], [], []])->shouldBeCalled();

        $this->data_store->save();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid repository URL.
     * @dataProvider invalidUrlProvider
     */
    public function testRegisterInvalidUrl(string $url): void
    {
        $this->data_store->register($url);
    }

    public function invalidUrlProvider()
    {
        return [
            ['foobar'],
            ['ssh://some.url'],
            ['http://'],
            ['localhost'],
        ];
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Repository already registered.
     */
    public function testRegisterDuplicate(): void
    {
        $this->api->fetchIdentifiers('http://foo.bar', 'foobar')->willReturn(['test']);
        $this->data_store->register('http://foo.bar')->init('foobar');

        $this->data_store->register('http://foo.bar')->init('foobar');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot access repository.
     */
    public function testRegisterInvalidApi(): void
    {
        $this->api->fetchIdentifiers('http://foo.bar', 'foobar')->willThrow(new ApiException());
        $this->data_store->register('http://foo.bar')->init('foobar');
    }

    public function testAdd(): void
    {
        $this->api->fetchIdentifiers('http://foo.bar', 'foobar')->willReturn(['test']);
        $this->api
            ->fetchConfig('http://foo.bar', 'foobar', 'test')
            ->willReturn(['data' => []]);

        $this->data_store->register('http://foo.bar')->init('foobar');
        $adder = $this->data_store->add('test');

        self::assertSame(['http://foo.bar'], $adder->getRepositories());

        $adder->initFrom('http://foo.bar');

        $this->serializer->dump(Argument::any(), [
            ['http://foo.bar' => ['token' => 'foobar']],
            [],
            ['test' => ['repository' => 'http://foo.bar', 'config' => ['data' => []]]]
        ])->shouldBeCalled();

        $this->data_store->save();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Config already added.
     */
    public function testAddDuplicate(): void
    {
        $this->api->fetchIdentifiers('http://foo.bar', 'foobar')->willReturn(['test']);
        $this->api
            ->fetchConfig('http://foo.bar', 'foobar', 'test')
            ->willReturn(['data' => []]);

        $this->data_store->register('http://foo.bar')->init('foobar');
        $this->data_store->add('test')->initFrom('http://foo.bar');

        $this->data_store->add('test')->initFrom('http://foo.bar');
    }
}
