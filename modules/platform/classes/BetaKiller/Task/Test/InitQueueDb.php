<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Enqueue\Dbal\DbalContext;
use Interop\Queue\Context;

class InitQueueDb extends AbstractTask
{
    /**
     * @var \Interop\Queue\Context
     */
    private $context;

    public function __construct(Context $context)
    {
        parent::__construct();

        $this->context = $context;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [
            'targetUser' => null,
        ];
    }

    public function run(): void
    {
        if (!$this->context instanceof DbalContext) {
            throw new TaskException('Wrong queue context');
        }

        $this->context->createDataBaseTable();
    }
}
