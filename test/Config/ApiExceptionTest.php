<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Yannickl88\Server\Config\ApiException
 */
class ApiExceptionTest extends TestCase
{
    public function testNotFoundError(): void
    {
        $e = ApiException::notFoundError(json_encode(['error_message' => 'foo', 'error_code' => 1]));

        self::assertSame('Not Found error: foo', $e->getMessage());
        self::assertSame(1, $e->getCode());
    }

    public function testMessageNoData(): void
    {
        $e = ApiException::notFoundError('');

        self::assertSame('Not Found error: Unknown Error', $e->getMessage());
        self::assertSame(0, $e->getCode());
    }

    public function testMessageBadData(): void
    {
        $e = ApiException::notFoundError('phpunit');

        self::assertSame('Not Found error: Unknown Error', $e->getMessage());
        self::assertSame(0, $e->getCode());
    }

    public function testMessageBadJson(): void
    {
        $e = ApiException::notFoundError('{');

        self::assertSame('Not Found error: Unknown Error', $e->getMessage());
        self::assertSame(0, $e->getCode());
    }

    public function testAuthenticationError(): void
    {
        $e = ApiException::authenticationError(json_encode(['error_message' => 'foo', 'error_code' => 2]));

        self::assertSame('Authentication error: foo', $e->getMessage());
        self::assertSame(2, $e->getCode());
    }

    public function testUnexpectedError(): void
    {
        $e = ApiException::unexpectedError(json_encode(['error_message' => 'foo', 'error_code' => 3]));

        self::assertSame('Unexpected error: foo', $e->getMessage());
        self::assertSame(3, $e->getCode());
    }

    public function testMalformedData(): void
    {
        $e = ApiException::malformedData();

        self::assertSame('Malformed or missing data.', $e->getMessage());
    }
}
