<?php
declare(strict_types=1);

namespace Yannickl88\Heimdall\Task;

use PHPUnit\Framework\TestCase;
use Yannickl88\Heimdall\Config\ConfigInterface;

/**
 * @covers \Yannickl88\Heimdall\Task\Tasks
 */
class TasksTest extends TestCase
{
    /**
     * Test if all tasks are present in the enum and are correctly sorted.
     */
    public function testTasksConstant()
    {
        $enum_file = (new \ReflectionClass(Tasks::class))->getFileName();

        $file = basename($enum_file);
        $dir = dirname($enum_file);

        $expected_tasks = array_values(array_map(function (string $file) {
            return __NAMESPACE__  . '\\' . substr($file, 0, -4);
        }, array_filter(scandir($dir, SCANDIR_SORT_NONE), function (string $task_file) use ($dir, $file) {
            return is_file($dir . '/' . $task_file) && !in_array($task_file, ['.', '..', $file], true);
        })));

        sort($expected_tasks);

        self::assertSame($expected_tasks, Tasks::TASKS);
    }
}
