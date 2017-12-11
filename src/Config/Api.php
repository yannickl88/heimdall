<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

use GuzzleHttp\Client;

final class Api implements ApiInterface
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout'  => 5,
            'http_errors' => false,
        ]);
    }

    public function fetchConfig(string $repo, string $token, string $identifier): array
    {
        // http://heimdall.dev/api/v1/config/voeding.coach
        $url = $repo . '/api/v1/config/' . rawurlencode($identifier) . '?token=' . rawurlencode($token);

        return $this->send($url)['config'];
    }

    public function fetchIdentifiers(string $repo, string $token): array
    {
        // http://heimdall.dev/api/v1/config/identifiers
        $url = $repo . '/api/v1/config/identifiers?token='. rawurlencode($token);

        return $this->send($url)['identifiers'];
    }

    public function publishConfig(string $repo, string $token, string $identifier, string $parent_revision, array $data): string
    {
        // http://heimdall.dev/api/v1/config/identifiers
        $url = $repo . '/api/v1/config/' . rawurlencode($identifier) . '?token=' . rawurlencode($token);

        $response = $this->send($url, 'PUT', ['parent_revision' => $parent_revision, 'data' => $data]);

        return $response['revision'];
    }

    public function initConfig(string $repo, string $token, string $identifier): string
    {
        // http://heimdall.dev/api/v1/config/identifiers
        $url = $repo . '/api/v1/config/' . rawurlencode($identifier) . '?token=' . rawurlencode($token);

        $response = $this->send($url, 'POST');

        return $response['revision'];
    }

    private function send(string $url, string $method = 'GET', array $payload = null): array
    {
        $options = [];

        if (null !== $payload) {
            $options['json'] = $payload;
        }

        $res = $this->client->request($method, $url, $options);

        $http_code = $res->getStatusCode();
        $data = $res->getBody()->__toString();

        if ($http_code === 404) {
            throw ApiException::notFoundError($data);
        }
        if ($http_code >= 400 && $http_code < 500) {
            throw ApiException::authenticationError($data);
        }
        if ($http_code >= 500) {
            throw ApiException::unexpectedError($data);
        }

        if ('' === $data || $data[0] !== '{') {
            throw ApiException::malformedData();
        }

        return json_decode($data, true);
    }
}
