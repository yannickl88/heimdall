<?php
declare(strict_types=1);

namespace Yannickl88\Server;

use PHPUnit\Framework\TestCase;
use Yannickl88\Server\Task\GeneratedEnvScript;

/**
 * @covers \Yannickl88\Server\TaskLoader
 */
class TaskLoaderTest extends TestCase
{
    /**
     * @var TaskLoader
     */
    private $task_loader;

    protected function setUp()
    {
        $this->task_loader = new TaskLoader();
    }

    public function testGetTasks()
    {
        $tasks = $this->task_loader->getTasks(['generate:env-script']);

        self::assertCount(1, $tasks);
        self::assertInstanceOf(GeneratedEnvScript::class, $tasks[0]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown task "task:does-not-exists".
     */
    public function testGetTasksBadTask()
    {
        $this->task_loader->getTasks(['task:does-not-exists']);
    }
}
