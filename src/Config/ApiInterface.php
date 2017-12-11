<?php

namespace Yannickl88\Server\Config;

interface ApiInterface
{
    public function fetchConfig(string $repo, string $token, string $identifier): array;

    public function fetchIdentifiers(string $repo, string $token): array;

    public function publishConfig(string $repo, string $token, string $identifier, string $parent_revision, array $data): string;
}
