<?php

namespace Yannickl88\Server\Config;

interface ApiInterface
{
    public function fetchConfig(string $repo, string $token, string $identifier): array;

    public function fetchIdentifiers(string $repo, string $token): array;
}
