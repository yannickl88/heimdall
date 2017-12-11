<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall;

use Yannickl88\Heimdall\Task\Tasks;

final class TaskLoader
{
    /**
     * @param string[] $identifiers
     * @return TaskInterface[]
     */
    public function getTasks(array $identifiers): array
    {
        return array_map([$this, 'getTask'], $identifiers);
    }

    private function getTask(string $identifier): TaskInterface
    {
        foreach (Tasks::TASKS as $task_class) {
            if ($identifier === call_user_func($task_class . '::identifier')) {
                return (new \ReflectionClass($task_class))->newInstance();
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown task "%s".', $identifier));
    }
}
