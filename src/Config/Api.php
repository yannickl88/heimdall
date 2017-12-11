<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

final class Api implements ApiInterface
{
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

        $response = $this->send($url, 'PUT', json_encode(['parent_revision' => $parent_revision, 'data' => $data]));

        return $response['revision'];
    }

    private function send(string $url, string $method = 'GET', string $payload = ''): array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($payload)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        $data = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

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
