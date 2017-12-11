<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Task;

final class Tasks
{
    /**
     * All possible tasks to execute.
     *
     * Please add any in alphabetical order.
     */
    public const TASKS = [
        GeneratedEnvScript::class,
        GeneratedVhost::class,
    ];

    private function __construct() {}
}
