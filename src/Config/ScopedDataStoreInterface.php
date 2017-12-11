<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

/**
 * Instances of this interface give a datastore for a specific config identifier.
 */
interface ScopedDataStoreInterface
{
    /**
     * Return a key.
     *
     * @param string $key
     * @return string
     */
    public function get(string $key): string;

    /**
     * Return if the key is present and up-to-date in the store.
     *
     * @param string $key
     * @param string $directive
     * @return bool
     */
    public function has(string $key, string $directive): bool;

    /**
     * Save the key into the datastore.
     *
     * @param string $key
     * @param string $directive
     * @param string $value
     */
    public function put(string $key, string $directive, string $value): void;
}
