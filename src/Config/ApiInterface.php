<?php

namespace Yannickl88\Heimdall\Config;

interface ApiInterface
{
    public function fetchConfig(string $repo, string $token, string $identifier): array;

    public function fetchIdentifiers(string $repo, string $token): array;

    public function publishConfig(string $repo, string $token, string $identifier, string $parent_revision, array $data): string;

    public function initConfig(string $repo, string $token, string $identifier): string;
}
