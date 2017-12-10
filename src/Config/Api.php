<?php
declare(strict_types=1);

namespace Yannickl88\Server\Config;

final class Api implements ApiInterface
{
    public function fetchConfig(string $repo, string $token, string $identifier): array
    {
        // http://heimdall.dev/api/v1/config/voeding.coach
        $url = $repo . '/api/v1/config/' . rawurlencode($identifier) . '?token=' . rawurlencode($token);

        return $this->doFetch($url)['config'];
    }

    public function fetchIdentifiers(string $repo, string $token): array
    {
        // http://heimdall.dev/api/v1/config/identifiers
        $url = $repo . '/api/v1/config/identifiers?token='. rawurlencode($token);

        return $this->doFetch($url)['identifiers'];
    }

    private function doFetch(string $url): array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

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
            throw ApiException::authenticationError($data);
        }

        if ('' === $data || $data[0] !== '{') {
            throw ApiException::malformedData();
        }

        return json_decode($data, true);
    }
}
