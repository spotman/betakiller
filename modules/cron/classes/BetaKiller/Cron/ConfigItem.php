<?php
declare(strict_types=1);

namespace BetaKiller\Cron;

use BetaKiller\Task\TaskException;
use Cron\CronExpression;
use function DI\factory;

final class ConfigItem
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var CronExpression
     */
    private $expr;

    /**
     * @var array
     */
    private $stages;

    /**
     * @var array
     */
    private $params;

    public static function fromArray(array $config): self
    {
        $name       = (string)($config['name'] ?? null);
        $expr       = (string)($config['at'] ?? null);
        $params     = $config['params'] ?? null;
        $taskStages = $config['stages'] ?? [];

        if (!$name) {
            throw new TaskException('Missing "name" key value in task :data', [
                ':data' => \json_encode($config),
            ]);
        }

        if (!$expr) {
            throw new TaskException('Missing "at" key value in [:name] task', [
                ':name' => $name,
            ]);
        }

        if (!\is_array($taskStages)) {
            throw new TaskException('Task stages must be an array');
        }

        return new self($name, $expr, $taskStages, $params);
    }

    /**
     * ConfigItem constructor.
     *
     * @param string     $name
     * @param string     $expr
     * @param array      $stages
     * @param array|null $params
     */
    public function __construct(string $name, string $expr, array $stages, array $params = null)
    {
        $this->name   = $name;
        $this->expr   = CronExpression::factory($expr);
        $this->stages = $stages;
        $this->params = $params ?? [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Cron\CronExpression
     */
    public function getExpression(): CronExpression
    {
        return $this->expr;
    }

    /**
     * @return array
     */
    public function getStages(): array
    {
        return $this->stages;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
