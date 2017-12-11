<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

class ApiException extends \RuntimeException
{
    private static function decode(string $data): array
    {
        if ('' === $data || $data[0] !== '{') {
            return ['Unknown Error', 0];
        }

        $json_data = json_decode($data, true);

        if (!is_array($json_data)) {
            return ['Unknown Error', 0];
        }

        return [
            $json_data['error_message'] ?? 'Unknown Error',
            $json_data['error_code'] ?? 0,
        ];
    }

    public static function notFoundError(string $data): self
    {
        [$message, $code] = self::decode($data);

        return new self('Not Found error: ' . $message, $code);
    }

    public static function authenticationError(string $data): self
    {
        [$message, $code] = self::decode($data);

        return new self('Authentication error: ' . $message, $code);
    }

    public static function unexpectedError(string $data): self
    {
        [$message, $code] = self::decode($data);

        return new self('Unexpected error: ' . $message, $code);
    }

    public static function malformedData(): self
    {
        return new self('Malformed or missing data.');
    }
}
