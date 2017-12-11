<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Config;

/**
 * Implementations of this config allow for accessing facts, directives and desired environment variables.

 * Directives together is the knowledgeable from which facts are derived and form the bases for configure items.
 *
 * Directives must always contain a string, but special values can be used. Such as:
 *  - @GEN[(length[;charspace])]
 *
 * @GEN allows you to generate a value. This can be useful for creating passwords or other secrets. @GEN accepts two
 * optional parameters, first is the length of the string you want to generate and the second is the  characters to
 * pick when generating a string.
 *   default length: 10
 *   default charspace: 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_*-+!$%=
 * Examples:
 *   - @GEN
 *   - @GEN(10)
 *   - @GEN(10;0123456789abcdef)
 */
interface ConfigInterface
{
    public function getIdentifier(): string;

    /**
     * Return the task identifiers to run.
     *
     * @return string[]
     */
    public function getTasks(): array;

    /**
     * Return the keys for all the environment variables.
     *
     * @return string[]
     */
    public function getEnvironmentVariableKeys(): array;

    /**
     * Return an environment variable. Environment variables should be configured or used when running tasks.
     *
     * @param string $key
     * @return string
     */
    public function getEnvironmentVariable(string $key): string;

    /**
     * Check if a fact is present.
     *
     * @param string $key
     * @return bool
     */
    public function hasFact(string $key): bool;

    /**
     * Return a fact. Facts are based on static values and directives.
     *
     * @param string      $key
     * @param null|string $default
     * @return string
     */
    public function getFact(string $key, ?string $default = null): string;
}
