<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Task\AbstractTask;

class UrlDetector extends AbstractTask
{
    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * UrlDetector constructor.
     *
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     */
    public function __construct(UserUrlDetectorInterface $urlDetector)
    {
        parent::__construct();

        $this->urlDetector = $urlDetector;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        echo $this->urlDetector->detect($this->getUser()).PHP_EOL;
    }
}
